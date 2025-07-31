<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index() {
        
        $machines = Machine::paginate(15); 
        return view('backoffice.machines.index', compact('machines'));
    }

    public function create() {
        return view('backoffice.machines.create');
    }

    public function store(Request $request) {
        Machine::create($request->only(['modelo', 'numero_serie', 'data_recebimento', 'observacoes']));
        return redirect()->route('backoffice.machines.index')->with('success', 'Máquina registada com sucesso!');
    }

    public function edit($id) {
        $machine = Machine::findOrFail($id);
        return view('backoffice.machines.edit', compact('machine'));
    }

    public function update(Request $request, $id) {
        $machine = Machine::findOrFail($id);
        $machine->update($request->only(['modelo', 'numero_serie', 'data_recebimento', 'observacoes']));
        return redirect()->route('backoffice.machines.index')->with('success', 'Máquina atualizada com sucesso!');
    }

    public function delete($id) {
        $machine = Machine::findOrFail($id);
        $machine->delete();
        return redirect()->route('backoffice.machines.index')->with('success', 'Máquina apagada com sucesso!');
    }

    public function show($id) {
        $machine = Machine::findOrFail($id);
        return view('backoffice.machines.show', compact('machine'));
    }

}
