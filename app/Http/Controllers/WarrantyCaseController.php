<?php

namespace App\Http\Controllers;

use App\Exports\WarrantyCasesExport;
use App\Models\Store;
use App\Models\TechnicalRequest;
use App\Models\WarrantyCase;
use App\Models\WarrantyCasePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Assistência > Fora da Garantia: avarias por culpa do cliente.
 * O processo são blocos com duas metades (config/warranty.php): grava-se uma metade de cada vez,
 * por qualquer ordem. Uma metade em branco é um passo que não foi preciso (ex.: peça já em stock).
 */
class WarrantyCaseController extends Controller
{
    // Fotografias, cotações e ordens de compra ficam no disco privado (só abrem com sessão iniciada)
    private const DISK = 'local';

    private const DOC_TYPES = ['os', 'cotacao', 'oc', 'servico'];

    public function index(Request $request)
    {
        $closed = WarrantyCase::CLOSED_PHASES;
        $marks = implode(',', array_fill(0, count($closed), '?'));

        $cases = $this->filteredQuery($request)
            ->with(['store', 'machine'])
            ->withCount('photos')
            // Os abertos primeiro e, entre esses, o que está parado há mais tempo no topo
            ->orderByRaw("(fase IN ($marks)) ASC", $closed)
            ->orderByRaw("CASE WHEN fase IN ($marks) THEN 0 ELSE DATEDIFF(CURDATE(), fase_desde) END DESC", $closed)
            ->orderByDesc('fase_desde')
            ->orderByDesc('id')
            ->paginate(40)
            ->withQueryString();

        // Cada total ignora o seu próprio filtro: senão, ao escolher um, os outros ficavam a zero
        $counts = $this->filteredQuery($request, ['fase'])
            ->toBase()->selectRaw('fase, COUNT(*) as total')->groupBy('fase')->pluck('total', 'fase');

        return view('backoffice.warranty.index', [
            'cases' => $cases,
            'counts' => $counts,
            'typeCounts' => $this->filteredQuery($request, ['tipo'])
                ->toBase()->selectRaw('tipo, COUNT(*) as total')->groupBy('tipo')->pluck('total', 'tipo'),
            'openTotal' => $counts->sum() - collect(WarrantyCase::CLOSED_PHASES)->sum(fn ($p) => $counts[$p] ?? 0),
            'phases' => config('warranty.phases'),
            'types' => config('warranty.types'),
            'clients' => config('warranty.clients'),
        ]);
    }

    public function export(Request $request)
    {
        return Excel::download(new WarrantyCasesExport($this->filteredQuery($request)), 'fora-da-garantia-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function create(Request $request)
    {
        $technicalRequest = $request->filled('pedido')
            ? TechnicalRequest::with(['store', 'machine', 'files'])->find($request->pedido)
            : null;

        return view('backoffice.warranty.create', [
            'stores' => $this->storesWithMachines(),
            'clients' => config('warranty.clients'),
            'types' => config('warranty.types'),
            'prefillType' => array_key_exists((string) $request->tipo, config('warranty.types')) ? $request->tipo : null,
            'technicalRequest' => $technicalRequest,
            'requestPhotos' => $technicalRequest ? $technicalRequest->files->filter(fn ($f) => $f->isImage()) : collect(),
        ]);
    }

    /** Criar o processo. A ordem de serviço da visita pode vir já preenchida (é o caso normal). */
    public function store(Request $request)
    {
        $data = $this->validateCase($request);
        $request->validate($this->photoRules() + [
            'os_em' => ['nullable', 'date', 'before_or_equal:today'],
            'os_numero' => ['nullable', 'required_with:os_em', 'string', 'max:100'],
            'os_texto' => ['nullable', 'required_with:os_em', 'string', 'max:5000'],
            'documento' => ['nullable', ...$this->documentRule()],
        ], $this->photoMessages() + $this->documentMessages() + [
            'os_numero.required_with' => __('Indique o número da ordem de serviço.'),
            'os_texto.required_with' => __('Escreva o que o técnico encontrou na visita.'),
            'os_em.before_or_equal' => __('A data da visita não pode ser no futuro.'),
        ]);

        $case = DB::transaction(function () use ($data, $request) {
            $case = WarrantyCase::create($data + [
                'fase' => 'registado',
                'fase_desde' => $data['data_avaria'],
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            $case->events()->create(['fase' => 'registado', 'data' => $data['data_avaria'], 'user_id' => auth()->id()]);

            if ($request->filled('os_em')) {
                $case->fill($request->only(['os_em', 'os_numero', 'os_texto']) + $this->storeDocument($request, $case, 'os'));
                $case->syncPhase();
                $case->save();
                $case->events()->create(['fase' => 'os_diagnostico', 'data' => $request->os_em, 'user_id' => auth()->id()]);
            }

            return $case;
        });

        $this->storePhotos($request, $case);
        if ($request->boolean('copiar_fotos') && $case->technical_request_id) {
            $this->copyRequestPhotos($case);
        }

        flash(__('Processo criado. Falta: :passo.', ['passo' => $case->todoLabel() ?? __('nada')]))->success();

        return redirect()->route('backoffice.warranty.show', $case->id);
    }

    public function show($id)
    {
        $case = WarrantyCase::with(['store', 'machine', 'technicalRequest', 'photos', 'events.user', 'creator'])->findOrFail($id);

        return view('backoffice.warranty.show', [
            'case' => $case,
            'phases' => config('warranty.phases'),
            'clients' => config('warranty.clients'),
            'types' => config('warranty.types'),
            'stores' => $this->storesWithMachines(),
            'next' => $case->nextStep(),
            'siblings' => $case->siblings(),
            // Nº de cliente na Sielaff Alemanha: sugere o último usado para o mesmo cliente (Lidl/Sonae)
            'suggestedClientNumber' => WarrantyCase::where('cliente', $case->cliente)->whereNotNull('cliente_numero')
                ->orderByDesc('alemanha_enviado_em')->orderByDesc('id')->value('cliente_numero'),
            'germanyEmail' => config('warranty.germany_email'),
        ]);
    }

    /** Corrigir os dados da avaria (loja, máquina, cliente, tipo, texto). */
    public function update(Request $request, $id)
    {
        $case = WarrantyCase::with('events')->findOrFail($id);
        $data = $this->validateCase($request, $case);

        DB::transaction(function () use ($case, $data) {
            $case->fill($data + ['updated_by' => auth()->id()]);
            $case->syncPhase();
            $case->save();
            $case->events->firstWhere('fase', 'registado')?->update(['data' => $data['data_avaria']]);
        });
        flash(__('Dados atualizados.'))->success();

        return back();
    }

    public function destroy($id)
    {
        $case = WarrantyCase::with('photos')->findOrFail($id);
        Storage::disk(self::DISK)->deleteDirectory($this->folder($case));
        $case->delete();
        flash(__('Processo apagado.'))->success();

        return redirect()->route('backoffice.warranty.index');
    }

    /** Gravar (ou corrigir) uma metade de um bloco: a data, os seus dados e o documento. */
    public function saveStep(Request $request, $id, string $block, string $half)
    {
        $case = WarrantyCase::findOrFail($id);
        $step = $case->step($block, $half);
        abort_unless($step, 404);

        $data = $request->validate(
            ['data' => ['required', 'date', 'before_or_equal:today'], 'nota' => ['nullable', 'string', 'max:2000']] + $this->stepRules($case, $block, $half),
            ['data.before_or_equal' => __('A data não pode ser no futuro.')] + $this->stepMessages() + $this->documentMessages()
        );

        $updates = [$step['date'] => $data['data']] + $this->stepUpdates($case, $block, $half, $data, $request);

        DB::transaction(function () use ($case, $step, $data, $updates) {
            $case->fill($updates + ['updated_by' => auth()->id()]);
            $case->syncPhase();
            $case->save();
            // O fecho tem duas metades alternativas: ao gravar uma, a outra deixa de existir
            if ($step['block'] === 'fecho') {
                $case->events()->where('fase', $step['half'] === 'concluido' ? 'recusado' : 'concluido')->delete();
            }
            $case->events()->updateOrCreate(
                ['fase' => $step['fase']],
                ['data' => $data['data'], 'nota' => $data['nota'] ?? null, 'user_id' => auth()->id()]
            );
        });

        flash(__('Registado: :passo.', ['passo' => __($step['label'])]))->success();

        return back();
    }

    /** Apagar uma metade: ou foi engano, ou esse passo não foi preciso. */
    public function clearStep($id, string $block, string $half)
    {
        $case = WarrantyCase::findOrFail($id);
        $step = $case->step($block, $half);
        abort_unless($step, 404);
        abort_unless($case->stepDone($step), 422, __('Este passo ainda não foi registado.'));

        $fields = $this->stepFields($case, $block, $half);
        if (isset($step['doc']) && $case->{$step['doc'] . '_path'}) {
            Storage::disk(self::DISK)->delete($case->{$step['doc'] . '_path'});
        }

        DB::transaction(function () use ($case, $step, $fields) {
            $case->fill(array_fill_keys($fields, null) + ['updated_by' => auth()->id()]);
            $case->syncPhase();
            $case->save();
            $case->events()->where('fase', $step['fase'])->delete();
        });

        flash(__('Passo apagado: :passo.', ['passo' => __($step['label'])]))->success();

        return back();
    }

    public function addPhotos(Request $request, $id)
    {
        $case = WarrantyCase::findOrFail($id);
        $request->validate(['fotos' => ['required', 'array', 'max:20']] + $this->photoRules(), $this->photoMessages() + [
            'fotos.required' => __('Escolha pelo menos uma fotografia.'),
        ]);

        $total = $this->storePhotos($request, $case);
        flash(trans_choice(':count fotografia adicionada.|:count fotografias adicionadas.', $total, ['count' => $total]))->success();

        return back();
    }

    public function photo($photoId)
    {
        $photo = WarrantyCasePhoto::findOrFail($photoId);
        abort_unless(Storage::disk(self::DISK)->exists($photo->path), 404);

        return Storage::disk(self::DISK)->response($photo->path, $photo->nome, ['Cache-Control' => 'private, max-age=86400']);
    }

    public function deletePhoto($photoId)
    {
        $photo = WarrantyCasePhoto::findOrFail($photoId);
        Storage::disk(self::DISK)->delete($photo->path);
        $photo->delete();
        flash(__('Fotografia removida.'))->success();

        return back();
    }

    /** Abrir o PDF da ordem de serviço, da cotação, da ordem de compra ou da ordem de serviço final. */
    public function document($id, string $tipo)
    {
        abort_unless(in_array($tipo, self::DOC_TYPES, true), 404);
        $case = WarrantyCase::findOrFail($id);
        $path = $case->{$tipo . '_path'};
        abort_unless($path && Storage::disk(self::DISK)->exists($path), 404);

        return Storage::disk(self::DISK)->response($path, $case->{$tipo . '_nome'});
    }

    /** Anexar (mais tarde) ou substituir um documento. */
    public function replaceDocument(Request $request, $id, string $tipo)
    {
        abort_unless(in_array($tipo, self::DOC_TYPES, true), 404);
        $case = WarrantyCase::findOrFail($id);

        $request->validate([
            'documento' => ['required', ...$this->documentRule()],
        ], $this->documentMessages() + ['documento.required' => __('Escolha o ficheiro.')]);

        $hadFile = (bool) $case->{$tipo . '_path'};
        $case->update($this->storeDocument($request, $case, $tipo) + ['updated_by' => auth()->id()]);
        flash($hadFile ? __('Documento substituído.') : __('Documento anexado.'))->success();

        return back();
    }

    // ----------------------------------------------------------------------

    /** Dados próprios de cada metade: o que é preciso para a gravar. */
    private function stepRules(WarrantyCase $case, string $block, string $half): array
    {
        $doc = ['documento' => ['nullable', ...$this->documentRule()]];

        return match ("$block.$half") {
            'visita.feita' => ['os_numero' => ['required', 'string', 'max:100'], 'os_texto' => ['required', 'string', 'max:5000']] + $doc,
            'sielaff.recebida' => ['cotacao_numero' => ['required', 'string', 'max:100']] + $doc,
            'cliente.enviada' => ['enviada_para' => ['nullable', 'string', 'max:255']]
                // Sem peças a cotação é nossa: o número e o PDF entram aqui
                + ($case->needsParts() ? [] : ['cotacao_numero' => ['required', 'string', 'max:100']] + $doc),
            'cliente.oc' => ['oc_numero' => ['required', 'string', 'max:100']] + $doc,
            'alemanha.pedida' => ['artigo_numero' => ['required', 'string', 'max:255'], 'cliente_numero' => ['required', 'string', 'max:100']],
            'alemanha.recebida' => ['peca_origem' => ['required', Rule::in(array_keys(config('warranty.part_origins')))]],
            'fecho.concluido' => $doc,
            'fecho.recusado' => ['motivo_recusa' => ['required', 'string', 'max:2000']],
            default => [],
        };
    }

    private function stepUpdates(WarrantyCase $case, string $block, string $half, array $data, Request $request): array
    {
        $only = fn (array $keys) => array_intersect_key($data, array_flip($keys));

        return match ("$block.$half") {
            'visita.feita' => $only(['os_numero', 'os_texto']) + $this->storeDocument($request, $case, 'os'),
            'sielaff.recebida' => $only(['cotacao_numero']) + $this->storeDocument($request, $case, 'cotacao'),
            'cliente.enviada' => ['enviada_para' => $data['enviada_para'] ?? null]
                + ($case->needsParts() ? [] : $only(['cotacao_numero']) + $this->storeDocument($request, $case, 'cotacao')),
            'cliente.oc' => $only(['oc_numero']) + $this->storeDocument($request, $case, 'oc'),
            'alemanha.pedida' => $only(['artigo_numero', 'cliente_numero']),
            'alemanha.recebida' => $only(['peca_origem']),
            'fecho.concluido' => ['motivo_recusa' => null] + $this->storeDocument($request, $case, 'servico'),
            'fecho.recusado' => $only(['motivo_recusa']) + ['servico_path' => null, 'servico_nome' => null],
            default => [],
        };
    }

    /** Campos a limpar quando se apaga uma metade. */
    private function stepFields(WarrantyCase $case, string $block, string $half): array
    {
        return match ("$block.$half") {
            'visita.feita' => ['os_em', 'os_numero', 'os_texto', 'os_path', 'os_nome'],
            'sielaff.pedida' => ['cotacao_pedida_em'],
            'sielaff.recebida' => ['cotacao_recebida_em', 'cotacao_numero', 'cotacao_path', 'cotacao_nome'],
            'cliente.enviada' => array_merge(['enviada_cliente_em', 'enviada_para'],
                $case->needsParts() ? [] : ['cotacao_numero', 'cotacao_path', 'cotacao_nome']),
            'cliente.oc' => ['oc_recebida_em', 'oc_numero', 'oc_path', 'oc_nome'],
            'alemanha.pedida' => ['alemanha_enviado_em', 'artigo_numero', 'cliente_numero'],
            'alemanha.recebida' => ['peca_recebida_em', 'peca_origem'],
            'fecho.concluido' => ['fechado_em', 'servico_path', 'servico_nome'],
            'fecho.recusado' => ['fechado_em', 'motivo_recusa'],
            default => [],
        };
    }

    private function stepMessages(): array
    {
        return [
            'os_numero.required' => __('Indique o número da ordem de serviço.'),
            'os_texto.required' => __('Escreva o que o técnico encontrou na visita.'),
            'cotacao_numero.required' => __('Indique o número da cotação.'),
            'oc_numero.required' => __('Indique o número da ordem de compra.'),
            'artigo_numero.required' => __('Indique o número do artigo.'),
            'cliente_numero.required' => __('Indique o número do cliente.'),
            'peca_origem.required' => __('Escolha se recebemos a peça ou se já a tínhamos.'),
            'motivo_recusa.required' => __('Indique o motivo da recusa.'),
        ];
    }

    private function filteredQuery(Request $request, array $ignore = [])
    {
        $usa = fn (string $filtro) => !in_array($filtro, $ignore, true) && $request->filled($filtro);

        return WarrantyCase::query()
            ->when($request->filled('cliente'), fn ($q) => $q->where('cliente', $request->cliente))
            ->when($usa('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
            // Aqui dentro para que o Excel exporte o mesmo que estamos a ver
            ->when($usa('fase'), fn ($q) => $request->fase === 'abertos'
                ? $q->whereNotIn('fase', WarrantyCase::CLOSED_PHASES)
                : $q->where('fase', $request->fase))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . trim($request->q) . '%';
                $q->where(fn ($w) => $w->where('descricao', 'like', $term)
                    ->orWhere('os_numero', 'like', $term)
                    ->orWhere('cotacao_numero', 'like', $term)
                    ->orWhere('oc_numero', 'like', $term)
                    ->orWhereHas('store', fn ($s) => $s->where('codigo_loja', 'like', $term)->orWhere('nome_loja', 'like', $term)->orWhere('cidade', 'like', $term))
                    ->orWhereHas('machine', fn ($m) => $m->where('serial_number', 'like', $term)));
            });
    }

    private function validateCase(Request $request, ?WarrantyCase $case = null): array
    {
        $data = $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'machine_id' => ['nullable', Rule::exists('machines', 'id')->where('store_id', $request->input('store_id'))],
            'cliente' => ['required', Rule::in(array_keys(config('warranty.clients')))],
            'tipo' => [$case ? 'sometimes' : 'required', Rule::in(array_keys(config('warranty.types')))],
            'data_avaria' => ['required', 'date', 'before_or_equal:today'],
            'descricao' => ['required', 'string', 'max:5000'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
            'technical_request_id' => ['nullable', 'exists:technical_requests,id'],
        ], [
            'store_id.required' => __('Escolha a loja.'),
            'cliente.required' => __('Escolha o cliente.'),
            'tipo.required' => __('Escolha se o processo leva peças ou não.'),
            'descricao.required' => __('Descreva o problema.'),
            'data_avaria.required' => __('Indique a data da avaria.'),
            'data_avaria.before_or_equal' => __('A data da avaria não pode ser no futuro.'),
            'machine_id.exists' => __('Essa máquina não pertence à loja escolhida.'),
        ]);

        if ($case) {
            unset($data['technical_request_id']);
            if (!$case->canChangeType()) {
                unset($data['tipo']);
            }
        }

        return $data;
    }

    private function photoRules(): array
    {
        return [
            'fotos' => ['nullable', 'array', 'max:20'],
            'fotos.*' => ['file', 'mimes:jpg,jpeg,png,webp,heic,heif,gif', 'max:15360'],
        ];
    }

    private function photoMessages(): array
    {
        return [
            'fotos.max' => __('No máximo 20 fotografias de cada vez.'),
            'fotos.*.mimes' => __('As fotografias têm de ser imagens (JPG, PNG, HEIC…).'),
            'fotos.*.max' => __('Cada fotografia pode ter no máximo 15 MB.'),
        ];
    }

    private function documentRule(): array
    {
        return ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'];
    }

    private function documentMessages(): array
    {
        return [
            'documento.mimes' => __('O documento tem de ser PDF ou imagem.'),
            'documento.max' => __('O documento pode ter no máximo 20 MB.'),
        ];
    }

    private function folder(WarrantyCase $case): string
    {
        return 'warranty_cases/' . $case->id;
    }

    private function storePhotos(Request $request, WarrantyCase $case): int
    {
        $total = 0;
        foreach ((array) $request->file('fotos', []) as $file) {
            $case->photos()->create([
                'path' => $file->store($this->folder($case) . '/fotos', self::DISK),
                'nome' => $file->getClientOriginalName(),
                'uploaded_by' => auth()->id(),
            ]);
            $total++;
        }

        return $total;
    }

    /** Copia as imagens anexadas ao pedido técnico de origem. */
    private function copyRequestPhotos(WarrantyCase $case): void
    {
        $request = TechnicalRequest::with('files')->find($case->technical_request_id);
        foreach ($request?->files ?? [] as $file) {
            if (!$file->isImage() || !Storage::disk('public')->exists($file->file_path)) {
                continue;
            }
            $target = $this->folder($case) . '/fotos/' . basename($file->file_path);
            Storage::disk(self::DISK)->put($target, Storage::disk('public')->get($file->file_path));
            $case->photos()->create(['path' => $target, 'nome' => $file->file_name, 'uploaded_by' => auth()->id()]);
        }
    }

    private function storeDocument(Request $request, WarrantyCase $case, string $tipo): array
    {
        $file = $request->file('documento');
        if (!$file) {
            return [];
        }
        $old = $case->{$tipo . '_path'};
        $path = $file->store($this->folder($case) . '/' . $tipo, self::DISK);
        if ($old) {
            Storage::disk(self::DISK)->delete($old);
        }

        return [$tipo . '_path' => $path, $tipo . '_nome' => $file->getClientOriginalName()];
    }

    private function storesWithMachines()
    {
        return Store::with(['machines' => fn ($q) => $q->orderBy('serial_number')])
            ->orderBy('codigo_loja')
            ->get(['id', 'codigo_loja', 'nome_loja', 'insignia', 'cidade']);
    }
}
