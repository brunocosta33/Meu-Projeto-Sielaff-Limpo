<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VanStock;
use App\Models\Part;
use Illuminate\Support\Facades\DB;
use App\Models\Technician;
use Illuminate\Support\Facades\Log;
use Exception;



class VanStockController extends Controller
{
    public function index(Request $request)
    {
        $query = VanStock::with(['technician', 'part']);

        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        if ($request->filled('type')) {
            $query->whereHas('part', function ($q) use ($request) {
                $q->where('type', $request->type); // ← ERRADO: coluna 'tipo' não existe
            });
        }


        $stocks = $query->orderByDesc('created_at')->paginate(20);

        $technicians = Technician::orderBy('nome')->get();

        return view('backoffice.van-stocks.index', compact('stocks', 'technicians'));
    }

 
    public function create(Request $request)
    {
        $type = $request->get('type', 'peca'); // padrão = peca
        $parts = Part::where('type', $type)->orderBy('nome')->get();
        $technicians = Technician::orderBy('nome')->get();

        return view('backoffice.van-stocks.create', compact('parts', 'technicians', 'type'));
    }

    public function store(Request $request)
    {
        try {
            $type = $request->get('type');

            $request->validate([
                'part_id' => 'required|exists:parts,id',
                'technician_id' => 'required|exists:technicians,id',
                'quantity' => 'required|integer|min:1',
                'type' => 'required|in:peca,ferramenta',
            ]);

            $part = Part::findOrFail($request->part_id);

            if ($request->quantity > $part->quantidade) {
                return back()->withErrors(['quantity' => 'Quantidade em stock insuficiente.'])->withInput();
            }

            DB::transaction(function () use ($request, $part, $type) {
                $part->quantidade -= $request->quantity;
                $part->save();

                VanStock::create([
                    'part_id' => $request->part_id,
                    'technician_id' => $request->technician_id,
                    'quantity' => $request->quantity,
                    'type' => $type,
                ]);
            });

            flash('Peça atribuída ao técnico com sucesso!')->success();
            return redirect()->route('backoffice.van-stocks.index');

        } catch (\Exception $e) {
            return back()->withErrors(['erro' => 'Ocorreu um erro ao gravar o stock.'])->withInput();
        }
    }


    public function edit($id, Request $request)
    {
        $stock = VanStock::findOrFail($id);
        
        $type = $request->get('type', $stock->type); // manter o tipo atual se não vier no GET

        $parts = Part::where('type', $type)->orderBy('nome')->get();
        $technicians = Technician::orderBy('nome')->get();

        return view('backoffice.van-stocks.edit', compact('stock', 'parts', 'technicians', 'type'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'part_id' => 'required|exists:parts,id',
            'technician_id' => 'required|exists:technicians,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:peca,ferramenta',
        ]);

        try {
            $stock = VanStock::findOrFail($id);
            $part = Part::findOrFail($request->part_id);

            DB::transaction(function () use ($request, $stock, $part) {
                // Repor o stock global anterior antes de alterar
                $part->quantidade += $stock->quantity;

                // Verifica se há stock suficiente
                if ($request->quantity > $part->quantidade) {
                    throw new \Exception('Quantidade em stock insuficiente.');
                }

                // Atualiza o stock global
                $part->quantidade -= $request->quantity;
                $part->save();

                // Atualiza o stock da carrinha
                $stock->update([
                    'part_id' => $request->part_id,
                    'technician_id' => $request->technician_id,
                    'quantity' => $request->quantity,
                    'type' => $request->type,
                ]);
            });

            flash('Stock da carrinha atualizado com sucesso!')->success();
            return redirect()->route('backoffice.van-stocks.index');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['quantity' => $e->getMessage()])
                ->withInput();
        }
    }


    public function destroy($id)
    {
        $stock = VanStock::findOrFail($id);

        DB::transaction(function () use ($stock) {
            // Devolver ao stock global
            $stock->part->increment('quantidade', $stock->quantity);
            $stock->delete();
        });

        flash('Registo removido e stock reposto com sucesso.')->success();
        return redirect()->route('backoffice.van-stocks.index');
    }
}
