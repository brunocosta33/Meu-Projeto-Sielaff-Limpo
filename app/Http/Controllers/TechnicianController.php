<?php

namespace App\Http\Controllers;
use App\Models\Technician;
use Illuminate\Http\Request;
class TechnicianController extends Controller
{
    public function index()
    {
        $technicians = Technician::orderBy('nome')->paginate(20);
        return view('backoffice.technicians.index', compact('technicians'));
    }

    public function create()
    {
        return view('backoffice.technicians.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        Technician::create($request->all());

        flash('Técnico criado com sucesso!')->success();
        return redirect()->route('backoffice.technicians.index');
    }

    public function edit($id)
    {
        $technician = Technician::findOrFail($id);
        return view('backoffice.technicians.edit', compact('technician'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $technician = Technician::findOrFail($id);
        $technician->update($request->all());

        flash('Dados do técnico atualizados com sucesso!')->success();
        return redirect()->route('backoffice.technicians.index');
    }

    public function destroy($id)
    {
        $technician = Technician::findOrFail($id);
        $technician->delete();

        flash('Técnico removido com sucesso.')->success();
        return redirect()->route('backoffice.technicians.index');
    }
}
