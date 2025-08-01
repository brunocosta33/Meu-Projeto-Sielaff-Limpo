<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Technician;

class PartController extends Controller
{
    public function index(Request $request) {
        $query = Part::with('reservations.store');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $parts = $query->paginate(20)->appends($request->all());

        return view('backoffice.parts.index', compact('parts'));
    }


  public function create(Request $request) {
        $type = $request->get('type', 'peca'); // valor padrão
        $parts = Part::where('type', $type)->orderBy('nome')->get();
        $technicians = Technician::orderBy('nome')->get();

        return view('backoffice.parts.create', compact('parts', 'technicians', 'type'));
    }

   public function store(Request $request) {
        Part::create($request->only(['nome', 'referencia', 'descricao', 'quantidade', 'store_id', 'reservado', 'type']));
        return redirect()->route('backoffice.parts.index')->with('success', 'Peça criada com sucesso!');
    }

    public function edit($id) {
        $part = Part::findOrFail($id);
        $stores = Store::orderBy('nome_loja')->get();
        return view('backoffice.parts.edit', compact('part', 'stores'));
    }

    public function update(Request $request, $id) {
        $part = Part::findOrFail($id);
        $part->update($request->only(['nome', 'referencia', 'descricao', 'quantidade', 'store_id', 'reservado', 'type']));
        return redirect()->route('backoffice.parts.index')->with('success', 'Peça atualizada com sucesso!');
    }

    public function delete($id) {
        $part = Part::findOrFail($id);
        $part->delete();
        return redirect()->route('backoffice.parts.index')->with('success', 'Peça apagada com sucesso!');
    }
    public function show($id) {
        $part = Part::findOrFail($id);
        return view('backoffice.parts.show', compact('part'));
    }
}
