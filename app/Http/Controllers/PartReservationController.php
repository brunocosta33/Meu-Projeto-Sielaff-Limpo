<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PartReservation;
use App\Models\Part;
use App\Models\Store;

class PartReservationController extends Controller
{
    // Listar todas as reservas
   public function index(Request $request)
    {
        $query = PartReservation::with(['part', 'store']);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        $reservations = $query->orderBy('reserved_at', 'desc')->paginate(20);
        $stores = Store::orderBy('nome_loja')->get();

        return view('backoffice.part_reservations.index', compact('reservations', 'stores'));
    }

    // Mostrar formulário de criação
    public function create()
    {
        $parts = Part::all();
        $stores = Store::all();
        return view('backoffice.part_reservations.create', compact('parts', 'stores'));
    }

    // Gravar nova reserva
    public function store(Request $request)
    {
        $request->validate([
            'part_id' => 'required|exists:parts,id',
            'store_id' => 'required|exists:stores,id',
            'quantity' => 'required|integer|min:1',
            'reserved_at' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string',
        ], [
            'reserved_at.required' => 'A data da reserva é obrigatória.',
            'reserved_at.date' => 'A data da reserva deve ser uma data válida.',
            'reserved_at.after_or_equal' => 'A data da reserva não pode ser anterior ao dia de hoje.',
        ]);

        PartReservation::create([
            'part_id' => $request->part_id,
            'store_id' => $request->store_id,
            'quantity' => $request->quantity,
            'reserved_at' => $request->reserved_at,
            'notes' => $request->notes,
        ]);

        flash('Reserva criada com sucesso!')->success();
        return redirect()->route('backoffice.part_reservations.index');
    }


    // Editar reserva
    public function edit($id)
    {
        $reservation = PartReservation::findOrFail($id);
        $parts = Part::all();
        $stores = Store::all();
        return view('backoffice.part_reservations.edit', compact('reservation', 'parts', 'stores'));
    }

    // Atualizar reserva
    public function update(Request $request, $id)
    {
        $request->validate([
            'part_id' => 'required|exists:parts,id',
            'store_id' => 'required|exists:stores,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $reservation = PartReservation::findOrFail($id);

        $reservation->update([
            'part_id' => $request->part_id,
            'store_id' => $request->store_id,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
            // mantém a data original (caso não queiras permitir alteração)
        ]);

        return redirect()->route('backoffice.part_reservations.index')->with('success', 'Reserva atualizada com sucesso!');
    }

    // Eliminar reserva
    public function destroy($id)
    {
        $reservation = PartReservation::findOrFail($id);
        $reservation->delete();

        flash('Reserva eliminada com sucesso!')->success();
        return redirect()->route('backoffice.part_reservations.index');
    }
}
