<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Store;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index(Request $request)
    {
        $query = Machine::with('store')
            ->join('stores', 'machines.store_id', '=', 'stores.id')
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
            'serial_number' => 'required|unique:machines,serial_number',
            'ip_address'    => 'nullable|ip|unique:machines,ip_address', // 🔹 agora o IP também tem de ser único
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
            'serial_number' => 'required|unique:machines,serial_number,' . $machine->id,
            'ip_address'    => 'nullable|ip|unique:machines,ip_address,' . $machine->id, // 🔹 ignora a própria máquina
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
        $machines = Machine::where('store_id', $storeId)->get();
        return response()->json($machines);
    }
}
