<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;


class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::paginate(15);
        return view('backoffice.stores.index', compact('stores'));
    }

    public function create()
    {
        return view('backoffice.stores.create');
    }

    public function store(Request $request)
    {
        Store::create($request->all());
        flash('Loja criada com sucesso!')->success();
        return redirect()->route('backoffice.stores.index');
    }

    public function edit($id)
    {
        $store = Store::findOrFail($id);
        return view('backoffice.stores.edit', compact('store'));
    }

    public function update(Request $request, $id)
    {
        $store = Store::findOrFail($id);
        $store->update($request->all());
        flash('Loja atualizada com sucesso!')->success();
        return redirect()->route('backoffice.stores.index');
    }

    public function delete($id)
    {
        Store::where('id', $id)->update(['deleted_at' => now()]);
        flash('Loja apagada com sucesso!')->success();
        return redirect()->route('backoffice.stores.index');
    }
}
