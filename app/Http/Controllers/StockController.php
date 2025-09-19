<?php

namespace App\Http\Controllers;

use App\Models\StockBalance;
use App\Models\Location;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Models\Store;

class StockController extends Controller
{
    public function indexArmazem()
    {
        $location = Location::where('tipo', 'armazem')->first();
        $stock = StockBalance::with('item')->where('location_id', $location->id)->get();
        return view('backoffice.stock.armazem', compact('stock', 'location'));
    }

    public function indexCarrinha($id)
    {
        $location = Location::findOrFail($id);
        $stock = StockBalance::with('item')->where('location_id', $location->id)->get();
        return view('backoffice.stock.carrinha', compact('stock', 'location'));
    }

    public function indexCliente($id)
    {
        $location = Location::findOrFail($id);
        $stock = StockBalance::with('item')->where('location_id', $location->id)->get();
        return view('backoffice.stock.cliente', compact('stock', 'location'));
    }

    public function getItemStock($id)
    {
        $stocks = StockBalance::with('location')
            ->where('item_id', $id)
            ->get()
            ->filter(fn($s) => $s->location->tipo !== 'fornecedor') // exclui fornecedor
            ->map(function ($s) {
                return [
                    'location'   => $s->location->nome . ' (' . $s->location->tipo . ')',
                    'quantidade' => $s->quantidade,
                ];
            })
            ->values();

        return response()->json($stocks);
    }

    public function baixaClienteForm()
    {
        $items = Item::all();
        $carrinhas = Location::where('tipo', 'carrinha')->get();
        $stores = Location::where('tipo', 'cliente')
            ->with('store')
            ->whereNotNull('store_id')
            ->get()
            ->sortBy(fn($loc) => $loc->store->codigo_loja);



        return view('backoffice.stock.baixa', compact('items', 'carrinhas', 'stores'));
    }


    // 👉 Registo da baixa
    public function baixaClienteStore(Request $request)
    {
        $request->validate([
            'item_id'     => 'required|exists:items,id',
            'quantidade'  => 'required|integer|min:1',
            'carrinha_id' => 'required|exists:locations,id',
            'store_id'    => 'required|exists:stores,id',
            'machine_id'  => 'nullable|exists:machines,id',
        ]);

        // Validar stock na carrinha
        $saldoCarrinha = StockBalance::where('item_id', $request->item_id)
            ->where('location_id', $request->carrinha_id)
            ->first();

        if (!$saldoCarrinha || $saldoCarrinha->quantidade < $request->quantidade) {
            return back()->withErrors("Stock insuficiente na carrinha.")->withInput();
        }

        // Buscar a localização associada à loja
        $storeLocation = \App\Models\Location::where('store_id', $request->store_id)->first();

        if (!$storeLocation) {
            return back()->withErrors("A loja selecionada não tem uma localização associada.")->withInput();
        }

        // Criar movimento de stock
        $movimento = StockMovement::create([
            'item_id'        => $request->item_id,
            'quantidade'     => $request->quantidade,
            'origem_id'      => $request->carrinha_id,
            'destino_id'     => $storeLocation->id, // agora é o location_id da loja
            'machine_id'     => $request->machine_id,
            'tipo_movimento' => 'saida',
            'user_id'        => auth()->id(),
            'observacoes'    => $request->observacoes,
        ]);

        // Atualizar saldos
        $this->atualizarSaldos($movimento);

        return redirect()->route('backoffice.stock.movements.index')
            ->with('success', 'Baixa registada com sucesso.');
    }


    // 👉 Atualização de saldos
    private function atualizarSaldos(StockMovement $movimento)
    {
        // debitar da origem
        $saldoOrigem = StockBalance::firstOrCreate([
            'item_id'     => $movimento->item_id,
            'location_id' => $movimento->origem_id,
        ]);
        $saldoOrigem->quantidade -= $movimento->quantidade;
        $saldoOrigem->save();

        // creditar no destino
        $saldoDestino = StockBalance::firstOrCreate([
            'item_id'     => $movimento->item_id,
            'location_id' => $movimento->destino_id,
        ]);
        $saldoDestino->quantidade += $movimento->quantidade;
        $saldoDestino->save();
    }
}
