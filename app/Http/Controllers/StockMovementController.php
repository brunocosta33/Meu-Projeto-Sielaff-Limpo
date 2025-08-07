<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class StockMovementController extends Controller
{
    public function index()
    {
        $movimentos = StockMovement::with('item')->latest()->paginate(20);
        return view('backoffice.stock.movements.index', compact('movimentos'));
    }

    public function create()
    {
        $items = Item::orderBy('nome')->get();
        return view('backoffice.stock.movements.create', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'tipo' => 'required|in:entrada,saida',
            'quantidade' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255',
        ]);

        $item = Item::findOrFail($request->item_id);
        $quantidade = $request->quantidade;

        if ($request->tipo == 'saida' && $item->quantidade_atual < $quantidade) {
            return back()->withErrors(['quantidade' => 'Stock insuficiente.']);
        }

        $item->quantidade_atual += ($request->tipo == 'entrada') ? $quantidade : -$quantidade;
        $item->save();

        StockMovement::create([
            'item_id' => $item->id,
            'tipo' => $request->tipo,
            'quantidade' => $quantidade,
            'motivo' => $request->motivo,
            'origem' => 'manual',
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('backoffice.stock.movements.index')->with('success', 'Movimento registado com sucesso.');
    }
}