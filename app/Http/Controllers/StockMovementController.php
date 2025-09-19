<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\StockBalance;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with(['item','origem','destino','user'])
            ->orderBy('created_at','desc')
            ->paginate(20);

        return view('backoffice.stock.movements.index', compact('movements'));
    }

    public function create()
    {
        $items = Item::all();
        $locations = Location::all();
        return view('backoffice.stock.movements.create', compact('items','locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id'        => 'required|exists:items,id',
            'quantidade'     => 'required|integer|min:1',
            'origem_id'      => 'required|exists:locations,id',
            'destino_id'     => 'required|exists:locations,id',
            'tipo_movimento' => 'required|in:entrada,saida,transferencia,devolucao',
        ]);

        if ($request->origem_id == $request->destino_id) {
            return back()->withErrors("Origem e destino não podem ser iguais.")->withInput();
        }

        // Verificar regras de negócio
        $origem  = Location::find($request->origem_id);
        $destino = Location::find($request->destino_id);

        if (!$origem || !$destino) {
            return back()->withErrors("Localização inválida.")->withInput();
        }

        // Regras:
        // 1. Armazém → só pode enviar para carrinha ou devolver para fornecedor
        if ($origem->tipo === 'armazem') {
            if (!in_array($destino->tipo, ['carrinha','fornecedor'])) {
                return back()->withErrors("Do armazém só podes enviar para carrinha ou devolver para a Alemanha.")->withInput();
            }
        }

        // 2. Carrinha → só pode devolver para armazém ou enviar para cliente
        if ($origem->tipo === 'carrinha') {
            if (!in_array($destino->tipo, ['armazem','cliente'])) {
                return back()->withErrors("Da carrinha só podes devolver para o armazém ou enviar para cliente.")->withInput();
            }
        }

        // 3. Cliente → não pode ser origem
        if ($origem->tipo === 'cliente') {
            return back()->withErrors("De clientes não é permitido enviar peças.")->withInput();
        }

        // Verificação de stock
        if (in_array($request->tipo_movimento, ['saida','transferencia','devolucao'])) {
            $saldoOrigem = StockBalance::where('item_id', $request->item_id)
                                       ->where('location_id', $request->origem_id)
                                       ->first();
            if (!$saldoOrigem || $saldoOrigem->quantidade < $request->quantidade) {
                return back()->withErrors("Stock insuficiente na origem.")->withInput();
            }
        }

        // Criar movimento
        $movimento = StockMovement::create([
            'item_id'        => $request->item_id,
            'quantidade'     => $request->quantidade,
            'origem_id'      => $request->origem_id,
            'destino_id'     => $request->destino_id,
            'tipo_movimento' => $request->tipo_movimento,
            'observacoes'    => $request->observacoes,
            'user_id'        => auth()->id(),
        ]);

        // Atualizar saldos
        $this->atualizarSaldos($movimento);

        return redirect()->route('backoffice.stock.movements.index')
            ->with('success','Movimento registado com sucesso.');
    }

    private function atualizarSaldos(StockMovement $movimento)
    {
        // Debita da origem
        $saldoOrigem = StockBalance::firstOrCreate([
            'item_id'     => $movimento->item_id,
            'location_id' => $movimento->origem_id,
        ]);
        $saldoOrigem->quantidade -= $movimento->quantidade;
        $saldoOrigem->save();

        // Credita no destino
        $saldoDestino = StockBalance::firstOrCreate([
            'item_id'     => $movimento->item_id,
            'location_id' => $movimento->destino_id,
        ]);
        $saldoDestino->quantidade += $movimento->quantidade;
        $saldoDestino->save();
    }
}
