<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::query()
            ->where(function ($q) {
                $q->whereNull('store_id');   // ignora as que são ligadas a stores
            })
            ->orderBy('tipo')
            ->orderBy('nome')
            ->paginate(20);

        return view('backoffice.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('backoffice.locations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:locations,nome',
            'tipo' => 'required|in:armazem,carrinha,cliente,fornecedor',
            'codigo' => 'nullable|string|max:100',
        ]);

        Location::create($request->only(['nome', 'tipo', 'codigo']));

        return redirect()->route('backoffice.locations.index')
            ->with('success', 'Localização criada com sucesso.');
    }

    public function edit($id)
    {
        $location = Location::findOrFail($id);
        return view('backoffice.locations.edit', compact('location'));
    }

    public function update(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:255|unique:locations,nome,' . $location->id,
            'tipo' => 'required|in:armazem,carrinha,cliente,fornecedor',
            'codigo' => 'nullable|string|max:100',
        ]);

        $location->update($request->only(['nome', 'tipo', 'codigo']));

        return redirect()->route('backoffice.locations.index')
            ->with('success', 'Localização atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return redirect()->route('backoffice.locations.index')
            ->with('success', 'Localização removida com sucesso.');
    }
}
