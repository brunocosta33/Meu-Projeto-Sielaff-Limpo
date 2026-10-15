<?php

namespace App\Http\Controllers;

use App\Models\HrStaff;
use App\Models\Store;
use App\Models\StoreMaintenance;
use App\Models\StoreMaintenanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) ($request->input('ano') ?: now()->year);
        $states = config('maintenance.states');

        $rows = StoreMaintenance::with(['store', 'records' => fn ($q) => $q->where('ano', $year)->with('technician')])
            ->where('ativo', true)
            ->get()
            ->filter(fn ($m) => $m->store && $m->appliesTo($year))
            ->map(function ($m) use ($year) {
                $record = $m->records->first();
                [$start, $end] = $m->window($year);

                return (object) [
                    'maintenance' => $m,
                    'store' => $m->store,
                    'record' => $record,
                    'due' => $m->dueDate($year),
                    'start' => $start,
                    'end' => $end,
                    'status' => $m->status($year, $record),
                ];
            });

        $counts = $rows->countBy('status');

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
        $rows = $rows->sortBy(fn ($r) => sprintf('%02d-%s', $order[$r->status] ?? 99, $r->due->format('md')))->values();

        $technicianUserIds = HrStaff::where('funcao', 'tecnico')->where('ativo', true)->pluck('user_id');

        return view('backoffice.maintenances.index', [
            'year' => $year,
            'rows' => $rows,
            'counts' => $counts,
            'total' => $counts->sum(),
            'states' => $states,
            'storesWithout' => Store::whereNotIn('id', StoreMaintenance::pluck('store_id'))->orderBy('codigo_loja')->get(['id', 'codigo_loja', 'nome_loja', 'insignia']),
            'users' => User::whereNull('deleted_at')->orderBy('name')->get(['id', 'name'])
                ->sortByDesc(fn ($u) => $technicianUserIds->contains($u->id))->values(),
            'windowDays' => (int) config('maintenance.window_days'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'store_id' => ['required', 'exists:stores,id', Rule::unique('store_maintenances', 'store_id')],
            'data_contratada' => ['required', 'date'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ], [
            'store_id.unique' => 'Essa loja já tem manutenção anual registada.',
            'store_id.required' => 'Escolha a loja.',
        ]);

        $maintenance = StoreMaintenance::create($data);
        flash('Manutenção anual da loja ' . $maintenance->store->codigo_loja . ' registada.')->success();

        return back();
    }

    public function update(Request $request, $id)
    {
        $maintenance = StoreMaintenance::with('store')->findOrFail($id);
        $data = $request->validate([
            'data_contratada' => ['required', 'date'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ]);

        $maintenance->update($data);
        flash('Manutenção da loja ' . $maintenance->store->codigo_loja . ' atualizada.')->success();

        return back();
    }

    public function destroy($id)
    {
        $maintenance = StoreMaintenance::with('store')->findOrFail($id);
        $maintenance->delete();
        flash('Manutenção anual da loja ' . $maintenance->store->codigo_loja . ' removida (incluindo o histórico).')->success();

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
        ], [
            'data_realizada.before_or_equal' => 'A data em que foi feita não pode ser no futuro.',
        ]);

        StoreMaintenanceRecord::updateOrCreate(
            ['store_maintenance_id' => $maintenance->id, 'ano' => $data['ano']],
            $data + ['created_by' => auth()->id()]
        );

        [$start, $end] = $maintenance->window((int) $data['ano']);
        $done = Carbon::parse($data['data_realizada']);
        if (!$done->between($start, $end)) {
            flash('Atenção: a data ' . $done->format('d/m/Y') . ' está fora do prazo (' . $start->format('d/m') . ' a ' . $end->format('d/m') . ').')->warning();
        }
        flash('Manutenção ' . $data['ano'] . ' da loja ' . $maintenance->store->codigo_loja . ' marcada como feita.')->success();

        return back();
    }

    public function undo($recordId)
    {
        $record = StoreMaintenanceRecord::with('maintenance.store')->findOrFail($recordId);
        $record->delete();
        flash('Registo removido: a manutenção ' . $record->ano . ' da loja ' . $record->maintenance->store->codigo_loja . ' volta a estar por fazer.')->success();

        return back();
    }
}
