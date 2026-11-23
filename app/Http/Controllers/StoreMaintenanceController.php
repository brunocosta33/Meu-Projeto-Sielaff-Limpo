<?php

namespace App\Http\Controllers;

use App\Models\HrStaff;
use App\Models\Store;
use App\Models\StoreMaintenance;
use App\Models\StoreMaintenanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StoreMaintenanceController extends Controller
{
    // Folhas de obra ficam no disco privado (só abrem com sessão iniciada)
    private const DISK = 'local';

    public function index(Request $request)
    {
        $year = (int) ($request->input('ano') ?: now()->year);
        $states = config('maintenance.states');

        // A lista parte das lojas, não das manutenções: uma loja sem data contratada
        // tem de aparecer na mesma, como "Por definir", senão fica invisível
        $maintenances = StoreMaintenance::with(['records' => fn ($q) => $q->where('ano', $year)->with('technician')])
            ->where('ativo', true)
            ->get()
            ->keyBy('store_id');

        $rows = Store::orderBy('nome_loja')->get()->map(function (Store $store) use ($maintenances, $year) {
            $m = $maintenances->get($store->id);

            // A loja já está carregada: evita ir buscá-la outra vez por cada linha
            $m?->setRelation('store', $store);

            if (!$m || !$m->appliesTo($year)) {
                return (object) [
                    'maintenance' => $m,
                    'store' => $store,
                    'record' => null,
                    'due' => null,
                    'start' => null,
                    'end' => null,
                    'status' => 'por_definir',
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

        $counts = $rows->countBy('status');
        // Quantas lojas por cliente, para os botões de filtro
        $clientes = $rows->countBy(fn ($r) => $r->store->clienteKey() ?? 'outros');

        if ($request->filled('cliente')) {
            $rows = $rows->filter(fn ($r) => ($r->store->clienteKey() ?? 'outros') === $request->cliente);
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

        // Atrasadas primeiro, depois as que estão no prazo, próximas, por fazer e feitas; dentro de cada estado por data
        $order = array_flip(array_keys($states));
        $rows = $rows->sortBy(fn ($r) => sprintf('%02d-%s-%s', $order[$r->status] ?? 99, $r->due?->format('md') ?? '0000', $r->store->nome_loja))->values();

        $technicianUserIds = HrStaff::where('funcao', 'tecnico')->where('ativo', true)->pluck('user_id');

        return view('backoffice.maintenances.index', [
            'year' => $year,
            'rows' => $rows,
            'counts' => $counts,
            'clientes' => $clientes,
            'clientesConfig' => config('maintenance.clients'),
            'total' => $counts->sum(),
            'states' => $states,
            'users' => User::whereNull('deleted_at')->orderBy('name')->get(['id', 'name'])
                ->sortByDesc(fn ($u) => $technicianUserIds->contains($u->id))->values(),
            'windowDays' => (int) config('maintenance.window_working_days'),
        ]);
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
            'documento' => self::documentRule(),
        ], [
            'data_realizada.before_or_equal' => __('A data em que foi feita não pode ser no futuro.'),
        ] + self::documentMessages());
        unset($data['documento']);

        $record = StoreMaintenanceRecord::updateOrCreate(
            ['store_maintenance_id' => $maintenance->id, 'ano' => $data['ano']],
            $data + ['created_by' => auth()->id()]
        );
        $this->storeDocument($request, $record);

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
        $record = StoreMaintenanceRecord::with('maintenance.store')->findOrFail($recordId);
        if ($record->documento_path) {
            Storage::disk(self::DISK)->delete($record->documento_path);
        }
        $record->delete();
        flash(__('Registo removido: a manutenção :ano da loja :loja volta a estar por fazer.', ['ano' => $record->ano, 'loja' => $record->maintenance->store->codigo_loja]))->success();

        return back();
    }

    /** Anexar (ou substituir) a folha de obra de uma manutenção já feita. */
    public function attachDocument(Request $request, $recordId)
    {
        $record = StoreMaintenanceRecord::with('maintenance.store')->findOrFail($recordId);
        $request->validate(['documento' => array_merge(['required'], array_slice(self::documentRule(), 1))], self::documentMessages() + [
            'documento.required' => __('Escolha o ficheiro da folha de obra.'),
        ]);

        $this->storeDocument($request, $record);
        flash(__('Folha de obra anexada à manutenção :ano da loja :loja.', ['ano' => $record->ano, 'loja' => $record->maintenance->store->codigo_loja]))->success();

        return back();
    }

    /** Abrir a folha de obra no browser (no mesmo separador). */
    public function document($recordId)
    {
        $record = StoreMaintenanceRecord::findOrFail($recordId);
        abort_unless($record->documento_path && Storage::disk(self::DISK)->exists($record->documento_path), 404);

        return Storage::disk(self::DISK)->response($record->documento_path, $record->documento_nome);
    }

    public function deleteDocument($recordId)
    {
        $record = StoreMaintenanceRecord::findOrFail($recordId);
        if ($record->documento_path) {
            Storage::disk(self::DISK)->delete($record->documento_path);
            $record->update(['documento_path' => null, 'documento_nome' => null]);
        }
        flash(__('Folha de obra removida.'))->success();

        return back();
    }

    private static function documentRule(): array
    {
        return ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,heic,doc,docx', 'max:32768'];
    }

    private static function documentMessages(): array
    {
        return [
            'documento.mimes' => __('A folha de obra tem de ser PDF, imagem ou Word.'),
            'documento.max' => __('A folha de obra não pode ter mais de 32 MB.'),
        ];
    }

    private function storeDocument(Request $request, StoreMaintenanceRecord $record): void
    {
        if (!$request->hasFile('documento')) {
            return;
        }
        if ($record->documento_path) {
            Storage::disk(self::DISK)->delete($record->documento_path);
        }
        $file = $request->file('documento');
        $record->update([
            'documento_path' => $file->store('maintenance_reports', self::DISK),
            'documento_nome' => $file->getClientOriginalName(),
        ]);
    }
}
