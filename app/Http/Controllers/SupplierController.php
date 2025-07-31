<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'isActive']);
    }

    public function index()
    {
        $suppliers = Supplier::whereNull('deleted_at')->paginate(15);
        return view('backoffice.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('backoffice.suppliers.create');
    }

    public function store(Request $request)
    {
        $suppliers = new Supplier();
        $suppliers->nome = $request->input('nome');
        $suppliers->contacto = $request->input('contacto');
        $suppliers->email = $request->input('email');
        $suppliers->nif = $request->input('nif');
        $suppliers->morada = $request->input('morada');
        $suppliers->save();

        flash('Supplier criado com sucesso!')->success();
        return redirect()->route('backoffice.suppliers.index');
    }

    public function edit($id)
    {
        $suppliers = Supplier::findOrFail($id);
        return view('backoffice.suppliers.edit', compact('suppliers'));
    }

    public function update(Request $request, $id)
    {
        $suppliers = Supplier::findOrFail($id);
        $suppliers->nome = $request->input('nome');
        $suppliers->contacto = $request->input('contacto');
        $suppliers->email = $request->input('email');
        $suppliers->nif = $request->input('nif');
        $suppliers->morada = $request->input('morada');
        $suppliers->updated_at = now();
        $suppliers->save();

        flash('Supplier atualizado com sucesso!')->success();
        return redirect()->route('backoffice.suppliers.index');
    }

    public function delete($id)
    {
        DB::table('suppliers')->where('id', $id)->update(['deleted_at' => now()]);
        flash('Supplier apagado com sucesso!')->success();
        return redirect()->route('backoffice.suppliers.index');
    }
    
}
