<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MachineController extends Controller
{
    private const SERVICE_TYPES = [
        'software' => 'Software',
        'reparacao' => 'Assistência/Reparação',
        'manutencao' => 'Manutenção',
        'pre_visita' => 'Pré-Visita',
    ];

    private const STATUSES = [
        'pendente' => 'Pendente',
        'agendado' => 'Agendado',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
        'aguarda_peca' => 'Aguarda Peça',
    ];

    public function history($id)
    {
        $machine = Machine::with('store')->findOrFail($id);

        $requests = $machine->technicalRequests()
            ->with(['assignedTechnician', 'creator'])
            ->orderByRaw('COALESCE(data_resolucao, data_pedido) DESC')
            ->get();

        $consumptions = $machine->consumptions()
            ->with(['item', 'technician', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        // Total acumulado de peças gastas nesta máquina (por peça).
        $partsSummary = $consumptions
            ->groupBy('item_id')
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'reference' => $first->item->reference ?? '—',
                    'name' => $first->item->name ?? '—',
                    'quantity' => $group->sum('quantity'),
                ];
            })
            ->sortByDesc('quantity')
            ->values();

        return view('backoffice.machines.history', [
            'machine' => $machine,
            'requests' => $requests,
            'consumptions' => $consumptions,
            'partsSummary' => $partsSummary,
            'serviceTypes' => self::SERVICE_TYPES,
            'statuses' => self::STATUSES,
            'totalPartsConsumed' => $consumptions->sum('quantity'),
        ]);
    }

    public function index(Request $request)
    {
        $query = Machine::with('store')
            ->join('stores', 'machines.store_id', '=', 'stores.id')
            ->whereNull('stores.deleted_at')
            ->orderBy('stores.codigo_loja', 'asc')
            ->select('machines.*');

        // Filtro por código da loja
        if ($request->filled('codigo_loja')) {
            $query->whereHas('store', function ($q) use ($request) {
                $q->where('codigo_loja', 'like', '%' . $request->codigo_loja . '%');
            });
        }

        // Filtro por nome da loja
        if ($request->filled('nome_loja')) {
            $query->whereHas('store', function ($q) use ($request) {
                $q->where('nome_loja', 'like', '%' . $request->nome_loja . '%');
            });
        }

        // Filtro por número de série
        if ($request->filled('serial_number')) {
            $query->where('serial_number', 'like', '%' . $request->serial_number . '%');
        }

        // 👉 Agora traz todos os resultados
        $machines = $query->get();

        return view('backoffice.machines.index', compact('machines'));
    }


    public function create()
    {
        $stores = Store::orderBy('codigo_loja', 'asc')->get();
        return view('backoffice.machines.create', compact('stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id'      => 'required|exists:stores,id',
            'serial_number' => ['required', $this->uniqueActiveStoreMachine('serial_number')],
            'ip_address'    => ['nullable', 'ip', $this->uniqueActiveStoreMachine('ip_address')],
            'descricao'     => 'nullable|string',
        ]);

        Machine::create($request->all());

        return redirect()->route('backoffice.machines.index')
            ->with('success', 'Máquina criada com sucesso.');
    }
    public function edit($id)
    {
        $machine = Machine::findOrFail($id);
        $stores = Store::orderBy('codigo_loja', 'asc')->get();
        return view('backoffice.machines.edit', compact('machine', 'stores'));
    }
    public function update(Request $request, $id)
    {
        $machine = Machine::findOrFail($id);

        $request->validate([
            'store_id'      => 'required|exists:stores,id',
            'serial_number' => ['required', $this->uniqueActiveStoreMachine('serial_number', $machine->id)],
            'ip_address'    => ['nullable', 'ip', $this->uniqueActiveStoreMachine('ip_address', $machine->id)],
            'descricao'     => 'nullable|string',
        ]);

        $machine->update($request->all());

        return redirect()->route('backoffice.machines.index')
            ->with('success', 'Máquina atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $machine = Machine::findOrFail($id);
        $machine->delete();

        return redirect()->route('backoffice.machines.index')
            ->with('success', 'Máquina eliminada com sucesso.');
    }

    public function getByStore($storeId)
    {
        $machines = Machine::where('store_id', $storeId)
            ->whereHas('store')
            ->get();
        return response()->json($machines);
    }

    private function uniqueActiveStoreMachine(string $column, ?int $ignoreId = null)
    {
        return Rule::unique('machines', $column)
            ->ignore($ignoreId)
            ->where(function ($query) {
                $query->whereIn('store_id', function ($storeQuery) {
                    $storeQuery->select('id')
                        ->from('stores')
                        ->whereNull('deleted_at');
                });
            });
    }
}
