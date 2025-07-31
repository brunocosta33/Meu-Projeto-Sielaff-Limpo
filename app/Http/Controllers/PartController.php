<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function index() {
        $parts = Part::paginate(15); 
        return view('backoffice.parts.index', compact('parts'));
    }

    public function create() {
        return view('backoffice.parts.create');
    }

    public function store(Request $request) {
        Part::create($request->only(['nome', 'referencia', 'descricao', 'quantidade']));
        return redirect()->route('backoffice.parts.index')->with('success', 'Peça criada com sucesso!');
    }

    public function edit($id) {
        $part = Part::findOrFail($id);
        return view('backoffice.parts.edit', compact('part'));
    }

    public function update(Request $request, $id) {
        $part = Part::findOrFail($id);
        $part->update($request->only(['nome', 'referencia', 'descricao', 'quantidade']));
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
