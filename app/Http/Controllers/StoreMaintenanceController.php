<?php

namespace App\Http\Controllers;

use App\Models\HrStaff;
use App\Models\Store;
use App\Models\StoreMaintenance;
use App\Exports\StoreMaintenancesExport;
use App\Models\StoreMaintenanceFile;
use App\Models\StoreMaintenanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class StoreMaintenanceController extends Controller
{
    // Folhas de obra ficam no disco privado (só abrem com sessão iniciada)
    private const DISK = 'local';

    public function index(Request $request)
    {
        $year = (int) ($request->input('ano') ?: now()->year);
        $states = config('maintenance.states');

        $rows = $this->linhasDoAno($year);

        // Quantas em cada mês deste ano, para a barra dos meses
        $meses = $rows
            ->filter(fn ($r) => $r->due && $r->status !== 'contrato_futuro')
            ->countBy(fn ($r) => (int) $r->due->month);

        // As contagens dos estados e dos clientes são as do mês que se está a ver
        $doMes = $request->filled('mes')
            ? $this->filtrar($rows, new Request(['mes' => $request->mes]))
            : $rows;

        $counts = $doMes->countBy('status');
        $clientes = $doMes->countBy(fn ($r) => $r->store->clienteKey() ?? 'outros');

        $rows = $this->filtrar($rows, $request);

        $technicianUserIds = HrStaff::where('funcao', 'tecnico')->where('ativo', true)->pluck('user_id');

        return view('backoffice.maintenances.index', [
            'year' => $year,
            'rows' => $rows,
            'counts' => $counts,
            'meses' => $meses,
            'mesEscolhido' => $request->input('mes'),
            'clientes' => $clientes,
            'clientesConfig' => config('maintenance.clients'),
            'total' => $counts->sum(),
            'states' => $states,
            'users' => User::whereNull('deleted_at')->orderBy('name')->get(['id', 'name'])
                ->sortByDesc(fn ($u) => $technicianUserIds->contains($u->id))->values(),
            'windowDays' => (int) config('maintenance.window_working_days'),
        ]);
    }

    /**
     * Uma linha por loja (não por manutenção): quem ainda não tem contrato aparece
     * na mesma, como "Por definir". Serve a página e as exportações.
     */
    private function linhasDoAno(int $year)
    {
        $maintenances = StoreMaintenance::with(['records' => fn ($q) => $q->where('ano', $year)->with(['technician', 'files'])])
            ->where('ativo', true)
            ->get()
            ->keyBy('store_id');

        return Store::with(['machines' => fn ($q) => $q->whereNull('deleted_at')->orderBy('serial_number')])
            ->orderBy('nome_loja')->get()->map(function (Store $store) use ($maintenances, $year) {
            $m = $maintenances->get($store->id);

            // A loja já está carregada: evita ir buscá-la outra vez por cada linha
            $m?->setRelation('store', $store);

            if (!$m || !$m->appliesTo($year)) {
                return (object) [
                    'maintenance' => $m,
                    'store' => $store,
                    'record' => null,
                    // O contrato existe, mas só começa num ano à frente: não é "por definir"
                    'due' => $m ? $m->dueDate($m->data_contratada->year) : null,
                    'start' => null,
                    'end' => null,
                    'status' => $m ? 'contrato_futuro' : 'por_definir',
                ];
            }

            $record = $m->records->first();
            [$start, $end] = $m->window($year);

            return (object) [
                'maintenance' => $m,
                'store' => $store,
                'record' => $record,
                'due' => $m->dueDate($year),
                'start' => $start,
                'end' => $end,
                'status' => $m->status($year, $record),
            ];
        });
    }

    /** Os filtros da página, aplicados por igual na lista e nas exportações. */
    private function filtrar($rows, Request $request)
    {
        if ($request->filled('cliente')) {
            $rows = $rows->filter(fn ($r) => ($r->store->clienteKey() ?? 'outros') === $request->cliente);
        }

        if ($request->filled('mes')) {
            $rows = $request->mes === 'sem_data'
                ? $rows->filter(fn ($r) => in_array($r->status, ['por_definir', 'contrato_futuro'], true))
                : $rows->filter(fn ($r) => $r->due && $r->status !== 'contrato_futuro' && $r->due->month === (int) $request->mes);
        }

        if ($request->filled('q')) {
            $term = mb_strtolower(trim($request->q));
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower(implode(' ', [
                $r->store->codigo_loja, $r->store->nome_loja, $r->store->insignia, $r->store->cidade, $r->store->regiao,
            ])), $term));
        }

        if ($request->filled('estado')) {
            $wanted = $request->estado === 'feita' ? ['feita', 'feita_fora'] : [$request->estado];
            $rows = $rows->whereIn('status', $wanted);
        }

        $order = array_flip(array_keys(config('maintenance.states')));

        return $rows->sortBy(fn ($r) => sprintf('%02d-%s-%s', $order[$r->status] ?? 99, $r->due?->format('md') ?? '0000', $r->store->nome_loja))->values();
    }

    public function store(Request $request)
    {
        $this->mesParaData($request);

        $data = $request->validate([
            'store_id' => ['required', 'exists:stores,id', Rule::unique('store_maintenances', 'store_id')],
            'data_contratada' => ['required', 'date'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ], [
            'store_id.unique' => __('Essa loja já tem manutenção anual registada.'),
            'store_id.required' => __('Escolha a loja.'),
        ]);

        $maintenance = StoreMaintenance::create($data);
        flash(__('Manutenção anual da loja :loja registada.', ['loja' => $maintenance->store->codigo_loja]))->success();

        return back();
    }

    public function update(Request $request, $id)
    {
        $maintenance = StoreMaintenance::with('store')->findOrFail($id);
        $this->mesParaData($request);
        $data = $request->validate([
            'data_contratada' => ['required', 'date'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ]);

        $maintenance->update($data);
        flash(__('Manutenção da loja :loja atualizada.', ['loja' => $maintenance->store->codigo_loja]))->success();

        return back();
    }

    /**
     * Nos clientes mensais o campo é um mês (2026-11): guarda-se como o dia 1 desse mês,
     * já que o que conta é o mês inteiro.
     */
    private function mesParaData(Request $request): void
    {
        $valor = $request->input('data_contratada');

        if (is_string($valor) && preg_match('/^\d{4}-\d{2}$/', $valor)) {
            $request->merge(['data_contratada' => $valor . '-01']);
        }
    }

    public function destroy($id)
    {
        $maintenance = StoreMaintenance::with('store')->findOrFail($id);
        $maintenance->delete();
        flash(__('Manutenção anual da loja :loja removida (incluindo o histórico).', ['loja' => $maintenance->store->codigo_loja]))->success();

        return back();
    }

    /** Marcar a manutenção de um ano como feita. */
    public function markDone(Request $request, $id)
    {
        $maintenance = StoreMaintenance::with('store')->findOrFail($id);
        $data = $request->validate([
            'ano' => ['required', 'integer', 'min:2000', 'max:2100'],
            'data_realizada' => ['required', 'date', 'before_or_equal:today'],
            'user_id' => ['nullable', 'exists:users,id'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
            'folhas' => ['nullable', 'array', 'max:10'],
            'folhas.*' => self::regraFicheiro(),
            'fotos' => ['nullable', 'array', 'max:20'],
            'fotos.*' => self::regraFicheiro(),
        ], [
            'data_realizada.before_or_equal' => __('A data em que foi feita não pode ser no futuro.'),
        ] + self::mensagensFicheiro());
        unset($data['folhas'], $data['fotos']);

        $record = StoreMaintenanceRecord::updateOrCreate(
            ['store_maintenance_id' => $maintenance->id, 'ano' => $data['ano']],
            $data + ['created_by' => auth()->id()]
        );
        $this->guardarFicheiros($request, $record, 'folhas', 'folha');
        $this->guardarFicheiros($request, $record, 'fotos', 'foto');

        [$start, $end] = $maintenance->window((int) $data['ano']);
        $done = Carbon::parse($data['data_realizada']);
        if (!$done->between($start, $end)) {
            flash($maintenance->isMensal()
                ? __('Atenção: a data :data está fora do mês contratado (:mes).', ['data' => $done->format('d/m/Y'), 'mes' => $start->translatedFormat('F \d\e Y')])
                : __('Atenção: a data :data está fora do prazo (:inicio a :fim).', ['data' => $done->format('d/m/Y'), 'inicio' => $start->format('d/m'), 'fim' => $end->format('d/m')]))->warning();
        }
        flash(__('Manutenção :ano da loja :loja marcada como feita.', ['ano' => $data['ano'], 'loja' => $maintenance->store->codigo_loja]))->success();

        return back();
    }

    public function undo($recordId)
    {
        $record = StoreMaintenanceRecord::with(['maintenance.store', 'files'])->findOrFail($recordId);

        foreach ($record->files as $ficheiro) {
            Storage::disk(self::DISK)->delete($ficheiro->path);
        }

        $record->delete();
        flash(__('Registo removido: a manutenção :ano da loja :loja volta a estar por fazer.', ['ano' => $record->ano, 'loja' => $record->maintenance->store->codigo_loja]))->success();

        return back();
    }

    /** A lista em Excel, com os mesmos filtros que estão à vista. */
    public function export(Request $request)
    {
        [$year, $linhas] = $this->paraExportar($request);

        return Excel::download(new StoreMaintenancesExport($linhas, $year), 'manutencoes-' . $year . '.xlsx');
    }

    /** A lista em Excel mais os anexos (folhas de serviço e fotografias), tudo num ZIP. */
    public function exportZip(Request $request)
    {
        [$year, $linhas] = $this->paraExportar($request);

        $zip = new ZipArchive();
        $caminho = storage_path('app/manutencoes-' . $year . '-' . uniqid() . '.zip');

        abort_unless($zip->open($caminho, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500);

        $zip->addFromString(
            'manutencoes-' . $year . '.xlsx',
            Excel::raw(new StoreMaintenancesExport($linhas, $year), ExcelFormat::XLSX)
        );

        foreach ($linhas as $linha) {
            foreach ($linha->record?->files ?? [] as $ficheiro) {
                $origem = Storage::disk(self::DISK)->path($ficheiro->path);

                if (!is_file($origem)) {
                    continue;
                }

                $zip->addFile($origem, sprintf(
                    'anexos/%s/%s/%d-%s',
                    self::nomeSeguro($linha->store->codigo_loja . ' ' . $linha->store->nome_loja),
                    $ficheiro->tipo === 'foto' ? 'fotografias' : 'folhas de servico',
                    $ficheiro->id,
                    self::nomeSeguro($ficheiro->nome)
                ));
            }
        }

        $zip->close();

        return response()->download($caminho, 'manutencoes-' . $year . '.zip')->deleteFileAfterSend();
    }

    /** O ano e as linhas filtradas, para as duas exportações. */
    private function paraExportar(Request $request): array
    {
        $year = (int) ($request->input('ano') ?: now()->year);

        return [$year, $this->filtrar($this->linhasDoAno($year), $request)];
    }

    /** Nomes de pasta e de ficheiro que aguentam qualquer sistema. */
    private static function nomeSeguro(string $nome): string
    {
        $limpo = preg_replace('/[^\p{L}\p{N} ._-]+/u', '', $nome);

        return trim(preg_replace('/\s+/', ' ', $limpo)) ?: 'ficheiro';
    }

    /** Anexar ficheiros a uma manutenção já feita: folhas de serviço ou fotografias. */
    public function attachFiles(Request $request, $recordId)
    {
        $record = StoreMaintenanceRecord::with('maintenance.store')->findOrFail($recordId);

        $request->validate([
            'tipo' => ['required', Rule::in(array_keys(StoreMaintenanceFile::TIPOS))],
            'ficheiros' => ['required', 'array', 'max:20'],
            'ficheiros.*' => self::regraFicheiro(),
        ], self::mensagensFicheiro() + [
            'ficheiros.required' => __('Escolha os ficheiros a anexar.'),
        ]);

        $quantos = $this->guardarFicheiros($request, $record, 'ficheiros', $request->tipo);

        flash(trans_choice(
            '{1} :n ficheiro anexado à manutenção :ano da loja :loja.|[2,*] :n ficheiros anexados à manutenção :ano da loja :loja.',
            $quantos,
            ['n' => $quantos, 'ano' => $record->ano, 'loja' => $record->maintenance->store->codigo_loja]
        ))->success();

        return back();
    }

    /** Abrir um anexo no browser. */
    public function file($fileId)
    {
        $ficheiro = StoreMaintenanceFile::findOrFail($fileId);
        abort_unless(Storage::disk(self::DISK)->exists($ficheiro->path), 404);

        return Storage::disk(self::DISK)->response($ficheiro->path, $ficheiro->nome);
    }

    public function deleteFile($fileId)
    {
        $ficheiro = StoreMaintenanceFile::findOrFail($fileId);
        Storage::disk(self::DISK)->delete($ficheiro->path);
        $ficheiro->delete();

        flash(__('Anexo removido.'))->success();

        return back();
    }

    private static function regraFicheiro(): array
    {
        return ['file', 'mimes:pdf,jpg,jpeg,png,heic,heif,webp,doc,docx', 'max:32768'];
    }

    private static function mensagensFicheiro(): array
    {
        return [
            'folhas.*.mimes' => __('Os ficheiros têm de ser PDF, imagem ou Word.'),
            'fotos.*.mimes' => __('As fotografias têm de ser imagens (JPG, PNG ou HEIC).'),
            'ficheiros.*.mimes' => __('Os ficheiros têm de ser PDF, imagem ou Word.'),
            'folhas.*.max' => __('Cada ficheiro não pode ter mais de 32 MB.'),
            'fotos.*.max' => __('Cada ficheiro não pode ter mais de 32 MB.'),
            'ficheiros.*.max' => __('Cada ficheiro não pode ter mais de 32 MB.'),
        ];
    }

    /** Guarda os ficheiros de um campo e devolve quantos ficaram. */
    private function guardarFicheiros(Request $request, StoreMaintenanceRecord $record, string $campo, string $tipo): int
    {
        $guardados = 0;

        foreach ((array) $request->file($campo, []) as $file) {
            if (!$file) {
                continue;
            }

            StoreMaintenanceFile::create([
                'record_id' => $record->id,
                'tipo' => $tipo,
                'path' => $file->store('maintenance_files', self::DISK),
                'nome' => $file->getClientOriginalName(),
                'tamanho' => $file->getSize(),
                'created_by' => auth()->id(),
            ]);

            $guardados++;
        }

        return $guardados;
    }
}
