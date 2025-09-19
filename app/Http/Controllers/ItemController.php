<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::orderBy('id','desc')->paginate(20);
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
            'referencia' => 'nullable|string|max:100|unique:items,referencia',
        ]);

        Item::create($request->only(['nome','referencia','descricao']));

        return redirect()->route('backoffice.items.index')
                         ->with('success','Peça criada com sucesso.');
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        return view('backoffice.items.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:255',
            'referencia' => 'nullable|string|max:100|unique:items,referencia,'.$item->id,
        ]);

        $item->update($request->only(['nome','referencia','descricao']));

        return redirect()->route('backoffice.items.index')
                         ->with('success','Peça atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return redirect()->route('backoffice.items.index')
                         ->with('success','Peça removida com sucesso.');
    }
}
