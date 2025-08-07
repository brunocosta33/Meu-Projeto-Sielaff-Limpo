<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::orderBy('nome')->paginate(20);
        return view('backoffice.items.index', compact('items'));
    }

    public function create()
    {
        return view('backoffice.items.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'referencia' => 'required|string|max:100|unique:items',
            'tipo' => 'nullable|string|max:50',
            'quantidade_atual' => 'nullable|integer|min:0',
            'unidade_medida' => 'nullable|string|max:20',
        ]);

        Item::create($request->all());

        return redirect()->route('backoffice.items.index')->with('success', 'Item criado com sucesso.');
    }

    public function edit(Item $item)
    {
        return view('backoffice.items.edit', compact('item'));
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'referencia' => 'required|string|max:100|unique:items,referencia,' . $item->id,
            'tipo' => 'nullable|string|max:50',
            'quantidade_atual' => 'nullable|integer|min:0',
            'unidade_medida' => 'nullable|string|max:20',
        ]);

        $item->update($request->all());

        return redirect()->route('backoffice.items.index')->with('success', 'Item atualizado com sucesso.');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('backoffice.items.index')->with('success', 'Item eliminado com sucesso.');
    }
}

