<?php

namespace App\Http\Controllers;

use App\Models\TechnicalRequest;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Item;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TechnicalRequestsExport;

class TechnicalRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = TechnicalRequest::with('store')->orderBy('data_pedido', 'desc');

        // 🔎 Filtro por código da loja
        if ($request->filled('codigo_loja')) {
            $query->whereHas('store', function ($q) use ($request) {
                $q->where('codigo_loja', 'like', '%' . $request->codigo_loja . '%');
            });
        }

        // 🔎 Filtro por múltiplos estados
        if ($request->filled('estado')) {
            $query->whereIn('estado', (array) $request->estado);
        }

        $requests = $query->get();

        return view('backoffice.technical_requests.index', compact('requests'));
    }


    public function create()
    {
        $stores = Store::orderBy('codigo_loja')->get();
        return view('backoffice.technical_requests.create', compact('stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id'       => 'required|exists:stores,id',
            'origem'         => 'required|string|max:255',
            'tipo_servico'   => 'required|in:software,reparacao,manutencao,pre_visita',
            'descricao_problema' => 'nullable|string',
            'prioridade'     => 'required|in:baixa,media,alta',
            'estado'         => 'required|in:pendente,agendado,concluido,cancelado,aguarda_peca',
            'data_pedido'    => 'required|date',
            'data_agendamento' => 'nullable|date', // novo campo
            'data_resolucao' => 'nullable|date',
            'observacoes'    => 'nullable|string',
        ]);

        TechnicalRequest::create($request->all());

        return redirect()->route('backoffice.technical_requests.index')
            ->with('success', 'Pedido criado com sucesso!');
    }

    public function show($id)
    {
        $request = TechnicalRequest::with([
            'store.machines',          // máquinas disponíveis da loja
            'machines.machine',        // máquinas associadas ao pedido
            'machines.parts.item',     // peças aplicadas
            'machines.parts.user'      // técnico que aplicou
        ])->findOrFail($id);

        $items = Item::all();

        return view('backoffice.technical_requests.show', compact('request', 'items'));
    }

    public function edit($id)
    {
        $technicalRequest = TechnicalRequest::findOrFail($id);
        $stores = Store::orderBy('codigo_loja')->get();
        return view('backoffice.technical_requests.edit', compact('technicalRequest', 'stores'));
    }

    public function update(Request $request, $id)
    {
        $req = TechnicalRequest::findOrFail($id);

        $data = $request->all();

        // 👉 Se o estado não for "agendado", limpa a data de agendamento
        if ($request->estado !== 'agendado') {
            $data['data_agendamento'] = null;
        }

        $req->update($data);

        // 🔎 Guardar filtros e página atuais para manter no redirect
        $queryParams = $request->except(['_token', '_method']);

        return redirect()
            ->route('backoffice.technical_requests.index', $queryParams)
            ->with('success', 'Pedido atualizado com sucesso!');
    }

    public function delete(Request $request, $id)
    {
        $req = TechnicalRequest::findOrFail($id);
        $req->delete();

        // 🔎 Guardar filtros e página atuais para manter no redirect
        $queryParams = $request->except(['_token', '_method']);

        return redirect()
            ->route('backoffice.technical_requests.index', $queryParams)
            ->with('success', 'Pedido apagado com sucesso!');
    }


    public function export()
    {
        return Excel::download(new TechnicalRequestsExport, 'pedidos_assistencia.xlsx');
    }
}
