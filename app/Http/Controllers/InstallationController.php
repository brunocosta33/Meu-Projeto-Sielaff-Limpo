<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Installation;

class InstallationController extends Controller
{   
    public function index()
    {
        $installations = Installation::with(['store', 'team'])
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20);

        return view('backoffice.installations.index', compact('installations'));
    }

    public function create()
    {
        $stores = Store::all();
        $teams = Team::all();
        return view('backoffice.installations.create', compact('stores', 'teams'));
    }

    public function store(Request $request)
    {
        Installation::create($request->all());
        flash('Instalação criada com sucesso!')->success();
        return redirect()->route('backoffice.installations.index');
    }
    public function update(Request $request, Installation $installation)
    {
        $installation->update($request->all());

        flash('Instalação atualizada com sucesso!')->success();

        return redirect()->route('backoffice.installations.index', ['page' => $request->get('page')]);
    }


    public function edit(Installation $installation)
    {
        $stores = Store::all();
        $teams = Team::all();
        return view('backoffice.installations.edit', compact('installation', 'stores', 'teams'));
    }


    public function show(Installation $installation)
    {
        return view('backoffice.installations.show', compact('installation'));
    }

    public function delete(Installation $installation)
    {
        $installation->delete();
        flash('Instalação apagada com sucesso!')->success();
        return redirect()->route('backoffice.installations.index');
    }


}