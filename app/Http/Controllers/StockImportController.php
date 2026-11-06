<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Importar o catálogo de artigos a partir de um CSV ou Excel.
 * Casa pelo código: código novo cria o artigo, código conhecido actualiza-o.
 * As quantidades são substituídas, nunca somadas — a folha é a fotografia do armazém.
 * Nada é aplicado sem passar primeiro pela pré-visualização.
 */
class StockImportController extends Controller
{
    private const DISK = 'local';
    private const PASTA = 'stock_imports';

    /** Nomes aceites para cada coluna, sem acentos e em minúsculas. */
    private const COLUNAS = [
        'codigo' => ['codigo', 'código', 'referencia', 'referência', 'reference', 'ref', 'code'],
        'nome' => ['nome', 'name', 'designacao', 'designação', 'artigo', 'descricao', 'descrição'],
        'quantidade' => ['quantidade', 'qtd', 'stock', 'quantity', 'existencias', 'existências', 'stock em armazem'],
        'minimo' => ['minimo', 'mínimo', 'stock minimo', 'stock mínimo', 'minimum', 'min'],
        'localizacao' => ['localizacao', 'localização', 'location', 'local'],
    ];

    public function index()
    {
        $this->guard();

        return view('backoffice.stock.import.index', ['previsao' => session('stock_import')]);
    }

    /** Lê o ficheiro, valida linha a linha e mostra o que vai acontecer. Não grava nada. */
    public function preview(Request $request)
    {
        $this->guard();

        $request->validate([
            'ficheiro' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ], [
            'ficheiro.required' => __('Escolha o ficheiro.'),
            'ficheiro.mimes' => __('O ficheiro tem de ser CSV ou Excel (xlsx, xls).'),
            'ficheiro.max' => __('O ficheiro pode ter no máximo 10 MB.'),
        ]);

        $caminho = $request->file('ficheiro')->store(self::PASTA, self::DISK);
        $linhas = $this->lerFicheiro(Storage::disk(self::DISK)->path($caminho), $request->file('ficheiro')->getClientOriginalExtension());

        if ($linhas === null) {
            Storage::disk(self::DISK)->delete($caminho);
            return back()->withErrors(['ficheiro' => __('Não encontrei as colunas do código e do nome. Descarregue o modelo e use os mesmos cabeçalhos.')]);
        }

        $previsao = $this->analisar($linhas) + [
            'ficheiro' => $caminho,
            'nome_ficheiro' => $request->file('ficheiro')->getClientOriginalName(),
        ];

        $this->limparAntigos($caminho);

        return redirect()->route('backoffice.stock.import.index')->with('stock_import', $previsao);
    }

    /** Aplica o que foi mostrado na pré-visualização. */
    public function apply(Request $request)
    {
        $this->guard();

        $previsao = session('stock_import');
        if (!$previsao || !Storage::disk(self::DISK)->exists($previsao['ficheiro'])) {
            return redirect()->route('backoffice.stock.import.index')
                ->withErrors(['ficheiro' => __('A pré-visualização expirou. Volte a escolher o ficheiro.')]);
        }

        $linhas = $this->lerFicheiro(Storage::disk(self::DISK)->path($previsao['ficheiro']), pathinfo($previsao['nome_ficheiro'], PATHINFO_EXTENSION));
        $analise = $this->analisar($linhas ?? []);

        $novos = 0;
        $atualizados = 0;
        foreach ($analise['validas'] as $linha) {
            $item = Item::where('reference', $linha['codigo'])->first();

            $campos = array_filter([
                'name' => $linha['nome'],
                'location' => $linha['localizacao'],
            ], fn ($v) => $v !== null && $v !== '');

            // Substitui, nunca soma: a folha é a fotografia do armazém
            if ($linha['quantidade'] !== null) {
                $campos['warehouse_stock'] = $linha['quantidade'];
            }
            if ($linha['minimo'] !== null) {
                $campos['minimum_stock'] = $linha['minimo'];
            }

            if ($item) {
                $item->update($campos);
                $atualizados++;
            } else {
                Item::create($campos + ['reference' => $linha['codigo'], 'is_active' => true]);
                $novos++;
            }
        }

        Storage::disk(self::DISK)->delete($previsao['ficheiro']);
        session()->forget('stock_import');

        flash(__(':novos artigos criados e :atualizados actualizados.', ['novos' => $novos, 'atualizados' => $atualizados]))->success();

        return redirect()->route('backoffice.stock.items.index');
    }

    public function cancel()
    {
        $this->guard();

        if ($previsao = session('stock_import')) {
            Storage::disk(self::DISK)->delete($previsao['ficheiro']);
            session()->forget('stock_import');
        }

        return redirect()->route('backoffice.stock.import.index');
    }

    /** Modelo com os cabeçalhos certos e uma linha de exemplo. */
    public function template()
    {
        $this->guard();

        $csv = "codigo;nome;quantidade;minimo;localizacao\n"
            . "699015800;Sensor;12;2;Estante A3\n"
            . "FERR-001;Chave dinamométrica;1;0;Carrinha\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo-artigos.csv"',
        ]);
    }

    // ----------------------------------------------------------------------

    /**
     * Devolve as linhas já com as colunas identificadas, ou null se não encontrar o essencial.
     * O CSV é lido à mão para detetar o separador: o Excel português grava com ponto e vírgula.
     */
    private function lerFicheiro(string $caminho, string $extensao): ?array
    {
        $linhas = strtolower($extensao) === 'csv' || strtolower($extensao) === 'txt'
            ? $this->lerCsv($caminho)
            : (Excel::toArray(new \stdClass(), $caminho)[0] ?? []);

        if (!$linhas) {
            return null;
        }

        $mapa = $this->mapearColunas(array_shift($linhas));
        if (!isset($mapa['codigo'], $mapa['nome'])) {
            return null;
        }

        $valor = fn ($linha, $coluna) => isset($mapa[$coluna]) ? trim((string) ($linha[$mapa[$coluna]] ?? '')) : '';

        return collect($linhas)
            ->map(fn ($linha, $i) => [
                'linha' => $i + 2, // +1 do cabeçalho, +1 porque as folhas começam em 1
                'codigo' => $valor($linha, 'codigo'),
                'nome' => $valor($linha, 'nome'),
                'quantidade' => $valor($linha, 'quantidade'),
                'minimo' => $valor($linha, 'minimo'),
                'localizacao' => $valor($linha, 'localizacao'),
            ])
            ->reject(fn ($l) => $l['codigo'] === '' && $l['nome'] === '')
            ->values()
            ->all();
    }

    private function lerCsv(string $caminho): array
    {
        $conteudo = file_get_contents($caminho);
        // Ficheiros do Excel vêm muitas vezes em Windows-1252 e com BOM
        if (!mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'Windows-1252');
        }
        $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);

        $primeira = strtok($conteudo, "\r\n") ?: '';
        $separador = substr_count($primeira, ';') > substr_count($primeira, ',') ? ';' : ',';

        $linhas = [];
        foreach (preg_split('/\r\n|\r|\n/', $conteudo) as $linha) {
            if (trim($linha) !== '') {
                $linhas[] = str_getcsv($linha, $separador);
            }
        }

        return $linhas;
    }

    /** Liga cada coluna esperada à posição onde está na folha. */
    private function mapearColunas(array $cabecalho): array
    {
        $normalizar = fn ($t) => trim(mb_strtolower(preg_replace('/\s+/', ' ', (string) $t)));

        $mapa = [];
        foreach ($cabecalho as $i => $titulo) {
            $t = $normalizar($titulo);
            foreach (self::COLUNAS as $campo => $aceites) {
                if (!isset($mapa[$campo]) && in_array($t, array_map($normalizar, $aceites), true)) {
                    $mapa[$campo] = $i;
                }
            }
        }

        return $mapa;
    }

    /** Separa o que dá para aplicar do que tem problemas, e diz o que vai mudar. */
    private function analisar(array $linhas): array
    {
        $existentes = Item::pluck('name', 'reference');
        $vistos = [];
        $validas = [];
        $problemas = [];
        $novos = [];
        $atualizados = [];
        $nomesAlterados = [];

        foreach ($linhas as $l) {
            $codigo = $l['codigo'];

            if ($codigo === '') {
                $problemas[] = ['linha' => $l['linha'], 'erro' => __('sem código')];
                continue;
            }
            if (isset($vistos[$codigo])) {
                $problemas[] = ['linha' => $l['linha'], 'erro' => __('código :codigo repetido (linha :outra)', ['codigo' => $codigo, 'outra' => $vistos[$codigo]])];
                continue;
            }
            $existe = $existentes->has($codigo);
            if (!$existe && $l['nome'] === '') {
                $problemas[] = ['linha' => $l['linha'], 'erro' => __('artigo novo :codigo sem nome', ['codigo' => $codigo])];
                continue;
            }
            foreach (['quantidade', 'minimo'] as $campo) {
                if ($l[$campo] !== '' && !preg_match('/^\d+$/', str_replace([' ', '.'], '', $l[$campo]))) {
                    $problemas[] = ['linha' => $l['linha'], 'erro' => __(':campo ":valor" não é um número', ['campo' => $campo, 'valor' => $l[$campo]])];
                    continue 2;
                }
            }

            $vistos[$codigo] = $l['linha'];
            $validas[] = [
                'codigo' => $codigo,
                'nome' => $l['nome'] ?: null,
                'quantidade' => $l['quantidade'] === '' ? null : (int) str_replace([' ', '.'], '', $l['quantidade']),
                'minimo' => $l['minimo'] === '' ? null : (int) str_replace([' ', '.'], '', $l['minimo']),
                'localizacao' => $l['localizacao'] ?: null,
            ];

            if ($existe) {
                $atualizados[] = $codigo;
                if ($l['nome'] !== '' && $l['nome'] !== $existentes[$codigo]) {
                    $nomesAlterados[] = ['codigo' => $codigo, 'de' => $existentes[$codigo], 'para' => $l['nome']];
                }
            } else {
                $novos[] = $codigo;
            }
        }

        return [
            'validas' => $validas,
            'problemas' => $problemas,
            'novos' => count($novos),
            'atualizados' => count($atualizados),
            'nomes_alterados' => $nomesAlterados,
            'total' => count($linhas),
        ];
    }

    /** Ficheiros de importações antigas não ficam a ocupar espaço. */
    private function limparAntigos(string $manter): void
    {
        foreach (Storage::disk(self::DISK)->files(self::PASTA) as $f) {
            if ($f !== $manter && Storage::disk(self::DISK)->lastModified($f) < now()->subDay()->getTimestamp()) {
                Storage::disk(self::DISK)->delete($f);
            }
        }
    }

    private function guard(): void
    {
        abort_if(auth()->user()->hasRole('user'), 403);
    }
}
