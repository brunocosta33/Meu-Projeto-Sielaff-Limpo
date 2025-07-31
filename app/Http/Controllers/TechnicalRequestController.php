<?php

namespace App\Http\Controllers;

use App\Models\TechnicalRequest;
use Illuminate\Http\Request;
use App\Models\Store;

class TechnicalRequestController extends Controller
{
    public function index() {
        $requests = TechnicalRequest::paginate(15);
        return view('backoffice.technical_requests.index', compact('requests'));
    }
    public function create() {
        $stores = Store::orderBy('codigo_loja')->get();
        return view('backoffice.technical_requests.create', compact('stores'));
    }

   public function store(Request $request)
    {
        $request->validate([
            'origem' => 'required|string|max:255',
            'descricao_problema' => 'required|string',
            'prioridade' => 'required|in:baixa,media,alta',
            'estado' => 'required|in:pendente,agendado,concluído,cancelado',
            'data_pedido' => 'required|date',
            'observacoes' => 'nullable|string',
        ]);

        TechnicalRequest::create($request->all());

        return redirect()->route('backoffice.technical_requests.index')
            ->with('success', 'Pedido criado com sucesso!');
    }


    public function show($id) {
        $request = TechnicalRequest::findOrFail($id);
        return view('backoffice.technical_requests.show', compact('request'));
    }
    public function edit($id) {
        $technicalRequest = TechnicalRequest::findOrFail($id);
        $stores = Store::orderBy('codigo_loja')->get();
        return view('backoffice.technical_requests.edit', compact('technicalRequest', 'stores'));
    }

    public function update(Request $request, $id) {
        $req = TechnicalRequest::findOrFail($id);
        $req->update($request->all());
        return redirect()->route('backoffice.technical_requests.index')->with('success', 'Pedido atualizado com sucesso!');
    }

    public function delete($id) {
        $req = TechnicalRequest::findOrFail($id);
        $req->delete();
        return redirect()->route('backoffice.technical_requests.index')->with('success', 'Pedido apagado com sucesso!');
    }
}
