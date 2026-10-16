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
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Assistência > Fora da Garantia: avarias por culpa do cliente.
 * Pedir cotação à Sielaff -> receber cotação -> enviar ao cliente (Lidl/Sonae) -> receber ordem de compra -> reparar.
 */
class WarrantyCaseController extends Controller
{
    // Fotografias, cotações e ordens de compra ficam no disco privado (só abrem com sessão iniciada)
    private const DISK = 'local';

    /** Fase seguinte de cada fase em aberto. */
    private const NEXT = [
        'registado' => 'cotacao_pedida',
        'cotacao_pedida' => 'cotacao_recebida',
        'cotacao_recebida' => 'enviada_cliente',
        'enviada_cliente' => 'oc_recebida',
        'oc_recebida' => 'concluido',
    ];

    /** Campos preenchidos ao entrar em cada fase (limpos ao voltar atrás). */
    private const PHASE_FIELDS = [
        'cotacao_pedida' => ['cotacao_pedida_em'],
        'cotacao_recebida' => ['cotacao_numero', 'cotacao_recebida_em', 'cotacao_path', 'cotacao_nome'],
        'enviada_cliente' => ['enviada_cliente_em', 'enviada_para'],
        'oc_recebida' => ['oc_numero', 'oc_recebida_em', 'oc_path', 'oc_nome'],
        'concluido' => ['fechado_em'],
        'recusado' => ['fechado_em', 'motivo_recusa'],
    ];

    public function index(Request $request)
    {
        $phases = config('warranty.phases');
        $base = $this->filteredQuery($request);

        $counts = (clone $base)->toBase()->selectRaw('fase, COUNT(*) as total')->groupBy('fase')->pluck('total', 'fase');

        $cases = (clone $base)
            ->with(['store', 'machine'])
            ->withCount('photos')
            ->when($request->filled('fase'), fn ($q) => $request->fase === 'abertos'
                ? $q->whereIn('fase', WarrantyCase::OPEN_PHASES)
                : $q->where('fase', $request->fase))
            ->orderByRaw("FIELD(fase, '" . implode("','", array_keys($phases)) . "')")
            ->orderBy('fase_desde')
            ->paginate(40)
            ->withQueryString();

        return view('backoffice.warranty.index', [
            'cases' => $cases,
            'counts' => $counts,
            'openTotal' => collect(WarrantyCase::OPEN_PHASES)->sum(fn ($p) => $counts[$p] ?? 0),
            'phases' => $phases,
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
            'technicalRequest' => $technicalRequest,
            'requestPhotos' => $technicalRequest ? $technicalRequest->files->filter(fn ($f) => $f->isImage()) : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCase($request);
        $request->validate($this->photoRules(), $this->photoMessages());

        $case = DB::transaction(function () use ($data, $request) {
            $case = WarrantyCase::create($data + [
                'fase' => 'registado',
                'fase_desde' => now()->toDateString(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            $case->events()->create(['fase' => 'registado', 'data' => now()->toDateString(), 'user_id' => auth()->id()]);

            return $case;
        });

        $this->storePhotos($request, $case);
        if ($request->boolean('copiar_fotos') && $case->technical_request_id) {
            $this->copyRequestPhotos($case);
        }

        flash(__('Processo fora da garantia criado. Próximo passo: pedir a cotação à Sielaff.'))->success();

        return redirect()->route('backoffice.warranty.show', $case->id);
    }

    public function show($id)
    {
        $case = WarrantyCase::with(['store', 'machine', 'technicalRequest', 'photos', 'events.user', 'creator'])->findOrFail($id);

        return view('backoffice.warranty.show', [
            'case' => $case,
            'phases' => config('warranty.phases'),
            'clients' => config('warranty.clients'),
            'stores' => $this->storesWithMachines(),
            'next' => self::NEXT[$case->fase] ?? null,
        ]);
    }

    /** Corrigir os dados da avaria (loja, máquina, cliente, texto). */
    public function update(Request $request, $id)
    {
        $case = WarrantyCase::findOrFail($id);
        $data = $this->validateCase($request, $case);
        $case->update($data + ['updated_by' => auth()->id()]);
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

    /** Avançar para a fase seguinte (cada fase pede os seus dados). */
    public function advance(Request $request, $id)
    {
        $case = WarrantyCase::findOrFail($id);
        $next = self::NEXT[$case->fase] ?? null;
        abort_unless($next && $request->input('fase') === $next, 422, __('O processo mudou entretanto. Atualize a página.'));

        $rules = ['data' => ['required', 'date', 'before_or_equal:today'], 'nota' => ['nullable', 'string', 'max:2000']];
        $messages = ['data.before_or_equal' => __('A data não pode ser no futuro.')];
        if ($next === 'cotacao_recebida') {
            $rules += ['cotacao_numero' => ['required', 'string', 'max:100'], 'documento' => ['required', ...$this->documentRule()]];
            $messages += ['cotacao_numero.required' => __('Indique o número da cotação.'), 'documento.required' => __('Anexe o PDF da cotação.')];
        } elseif ($next === 'enviada_cliente') {
            $rules += ['enviada_para' => ['nullable', 'string', 'max:255']];
        } elseif ($next === 'oc_recebida') {
            $rules += ['oc_numero' => ['required', 'string', 'max:100'], 'documento' => ['required', ...$this->documentRule()]];
            $messages += ['oc_numero.required' => __('Indique o número da ordem de compra.'), 'documento.required' => __('Anexe o PDF da ordem de compra.')];
        }
        $data = $request->validate($rules, $messages + $this->documentMessages());

        if ($this->dateBeforePrevious($case, $data['data'])) {
            throw ValidationException::withMessages(['data' => __('A data não pode ser anterior ao passo anterior (:data).', ['data' => $case->fase_desde->format('d/m/Y')])]);
        }

        $updates = match ($next) {
            'cotacao_pedida' => ['cotacao_pedida_em' => $data['data']],
            'cotacao_recebida' => ['cotacao_recebida_em' => $data['data'], 'cotacao_numero' => $data['cotacao_numero']]
                + $this->storeDocument($request, $case, 'cotacao'),
            'enviada_cliente' => ['enviada_cliente_em' => $data['data'], 'enviada_para' => $data['enviada_para'] ?? null],
            'oc_recebida' => ['oc_recebida_em' => $data['data'], 'oc_numero' => $data['oc_numero']]
                + $this->storeDocument($request, $case, 'oc'),
            'concluido' => ['fechado_em' => $data['data']],
        };

        $this->moveTo($case, $next, $data['data'], $data['nota'] ?? null, $updates);
        flash(__('Passo registado: :fase.', ['fase' => __(config("warranty.phases.$next.label"))]))->success();

        return back();
    }

    /** O cliente recusou a cotação (depois de a receber). */
    public function refuse(Request $request, $id)
    {
        $case = WarrantyCase::findOrFail($id);
        abort_unless(in_array($case->fase, ['cotacao_recebida', 'enviada_cliente'], true), 422);

        $data = $request->validate([
            'data' => ['required', 'date', 'before_or_equal:today'],
            'motivo_recusa' => ['required', 'string', 'max:2000'],
        ], [
            'motivo_recusa.required' => __('Indique o motivo da recusa.'),
            'data.before_or_equal' => __('A data não pode ser no futuro.'),
        ]);

        $this->moveTo($case, 'recusado', $data['data'], $data['motivo_recusa'], ['fechado_em' => $data['data'], 'motivo_recusa' => $data['motivo_recusa']]);
        flash(__('Processo fechado: recusado pelo cliente.'))->success();

        return back();
    }

    /** Desfazer o último passo (engano): volta à fase anterior e limpa os dados desse passo. */
    public function undo($id)
    {
        $case = WarrantyCase::with('events')->findOrFail($id);
        $events = $case->events;
        abort_if($events->count() < 2, 422, __('Não há passos para desfazer.'));

        $last = $events->last();
        $previous = $events->get($events->count() - 2);

        $clear = array_fill_keys(self::PHASE_FIELDS[$case->fase] ?? [], null);
        foreach (['cotacao_path', 'oc_path'] as $file) {
            if (array_key_exists($file, $clear) && $case->$file) {
                Storage::disk(self::DISK)->delete($case->$file);
            }
        }

        DB::transaction(function () use ($case, $last, $previous, $clear) {
            $case->update($clear + ['fase' => $previous->fase, 'fase_desde' => $previous->data, 'updated_by' => auth()->id()]);
            $last->delete();
        });

        flash(__('Último passo desfeito. O processo voltou a: :fase.', ['fase' => __(config("warranty.phases.{$previous->fase}.label"))]))->success();

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

    /** Abrir o PDF da cotação ou da ordem de compra. */
    public function document($id, string $tipo)
    {
        abort_unless(in_array($tipo, ['cotacao', 'oc'], true), 404);
        $case = WarrantyCase::findOrFail($id);
        $path = $case->{$tipo . '_path'};
        abort_unless($path && Storage::disk(self::DISK)->exists($path), 404);

        return Storage::disk(self::DISK)->response($path, $case->{$tipo . '_nome'});
    }

    /** Substituir o PDF da cotação ou da ordem de compra (ex.: versão corrigida). */
    public function replaceDocument(Request $request, $id, string $tipo)
    {
        abort_unless(in_array($tipo, ['cotacao', 'oc'], true), 404);
        $case = WarrantyCase::findOrFail($id);
        abort_unless($case->{$tipo . '_path'}, 404);

        $request->validate([
            'documento' => ['required', ...$this->documentRule()],
            'numero' => ['required', 'string', 'max:100'],
        ], $this->documentMessages());

        $case->update($this->storeDocument($request, $case, $tipo) + [$tipo . '_numero' => $request->numero, 'updated_by' => auth()->id()]);
        flash(__('Documento substituído.'))->success();

        return back();
    }

    // ----------------------------------------------------------------------

    private function filteredQuery(Request $request)
    {
        return WarrantyCase::query()
            ->when($request->filled('cliente'), fn ($q) => $q->where('cliente', $request->cliente))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . trim($request->q) . '%';
                $q->where(fn ($w) => $w->where('descricao', 'like', $term)
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
            'descricao' => ['required', 'string', 'max:5000'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
            'technical_request_id' => ['nullable', 'exists:technical_requests,id'],
        ], [
            'store_id.required' => __('Escolha a loja.'),
            'cliente.required' => __('Escolha o cliente.'),
            'descricao.required' => __('Descreva o problema.'),
            'machine_id.exists' => __('Essa máquina não pertence à loja escolhida.'),
        ]);

        if ($case) {
            unset($data['technical_request_id']);
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
        $old = $case->{$tipo . '_path'};
        $file = $request->file('documento');
        $path = $file->store($this->folder($case) . '/' . $tipo, self::DISK);
        if ($old) {
            Storage::disk(self::DISK)->delete($old);
        }

        return [$tipo . '_path' => $path, $tipo . '_nome' => $file->getClientOriginalName()];
    }

    private function dateBeforePrevious(WarrantyCase $case, string $date): bool
    {
        return $case->fase_desde && \Illuminate\Support\Carbon::parse($date)->lt($case->fase_desde);
    }

    private function moveTo(WarrantyCase $case, string $phase, string $date, ?string $note, array $updates): void
    {
        DB::transaction(function () use ($case, $phase, $date, $note, $updates) {
            $case->update($updates + ['fase' => $phase, 'fase_desde' => $date, 'updated_by' => auth()->id()]);
            $case->events()->create(['fase' => $phase, 'data' => $date, 'nota' => $note, 'user_id' => auth()->id()]);
        });
    }

    private function storesWithMachines()
    {
        return Store::with(['machines' => fn ($q) => $q->orderBy('serial_number')])
            ->orderBy('codigo_loja')
            ->get(['id', 'codigo_loja', 'nome_loja', 'insignia', 'cidade']);
    }
}
