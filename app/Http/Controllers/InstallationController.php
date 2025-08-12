<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Installation;

class InstallationController extends Controller
{   
    public function index()
    {
        $query = Installation::with(['store', 'team']);

        // Filtro por código da loja
        if (request('codigo_loja')) {
            $query->whereHas('store', function($q) {
                $q->where('codigo_loja', 'like', '%' . request('codigo_loja') . '%');
            });
        }

        // Filtro por nome da loja
        if (request('nome_loja')) {
            $query->whereHas('store', function($q) {
                $q->where('nome_loja', 'like', '%' . request('nome_loja') . '%');
            });
        }

        // Filtro por equipa técnica
        if (request('team')) {
            $query->whereHas('team', function($q) {
                $q->where('nome', 'like', '%' . request('team') . '%');
            });
        }

        // Filtro por data
        if (request('data')) {
            $query->whereDate('scheduled_date', request('data'));
        }

        $installations = $query->orderBy('scheduled_date', 'desc')->paginate(20)->appends(request()->query());

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
        $installation = Installation::create($request->all());

        // Salvar PDFs enviados
        if ($request->hasFile('pdfs')) {
            foreach ($request->file('pdfs') as $pdf) {
                $path = $pdf->store('installations_pdfs', 'public');
                \App\Models\InstallationPdf::create([
                    'installation_id' => $installation->id,
                    'file_path' => $path,
                    'file_name' => $pdf->getClientOriginalName(),
                ]);
            }
        }

        flash('Instalação criada com sucesso!')->success();
        return redirect()->route('backoffice.installations.index');
    }
    public function update(Request $request, $id)
    {
        $installation = Installation::findOrFail($id);
        $installation->update($request->all());

        // Salvar PDFs enviados
        if ($request->hasFile('pdfs')) {
            foreach ($request->file('pdfs') as $pdf) {
                $path = $pdf->store('installations_pdfs', 'public');
                \App\Models\InstallationPdf::create([
                    'installation_id' => $installation->id,
                    'file_path' => $path,
                    'file_name' => $pdf->getClientOriginalName(),
                ]);
            }
        }

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

        public function deletePdf($id)
    {
        $pdf = \App\Models\InstallationPdf::findOrFail($id);
        Storage::disk('public')->delete($pdf->file_path);
        $pdf->delete();
        flash('PDF apagado com sucesso!')->success();
        return back();
    }


}