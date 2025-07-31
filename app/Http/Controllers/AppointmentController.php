<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\Team;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['store', 'supplier']);

        if ($request->filled('codigo_loja')) {
            $query->whereHas('store', function ($q) use ($request) {
                $q->where('codigo_loja', 'like', '%' . $request->codigo_loja . '%');
            });
        }

        if ($request->filled('nome_loja')) {
            $query->whereHas('store', function ($q) use ($request) {
                $q->where('nome_loja', 'like', '%' . $request->nome_loja . '%');
            });
        }

        if ($request->filled('transportadora')) {
            $query->whereHas('supplier', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->transportadora . '%');
            });
        }

        if ($request->filled('data')) {
            $query->whereDate('scheduled_date', $request->data);
        }

        $appointments = $query->orderBy('scheduled_date', 'desc')->paginate(20);

        return view('backoffice.appointments.index', compact('appointments'));
    }


    public function create() {
        $stores = Store::all();
        $suppliers = Supplier::all();
        $teams = Team::all();
        return view('backoffice.appointments.create', compact('stores', 'suppliers', 'teams'));
    }

    public function store(Request $request) {
        $data = $request->all();
        $data['scheduled_date'] = $request->input('scheduled_date');
        $data['scheduled_time'] = $request->input('scheduled_time');

        Appointment::create($data);

        flash('Agendamento criado com sucesso!')->success();
        return redirect()->route('backoffice.appointments.index');
    }


    public function edit($id) {
        $appointment = Appointment::findOrFail($id);
        $stores = Store::all();
        $suppliers = Supplier::all();
        $teams = Team::all();
        return view('backoffice.appointments.edit', compact('appointment', 'stores', 'suppliers', 'teams'));
    }

    public function update(Request $request, $id) {
        $appointment = Appointment::findOrFail($id);
        $appointment->update($request->all());
        flash('Agendamento atualizado com sucesso!')->success();
        return redirect()->route('backoffice.appointments.index');
    }

    public function delete($id) {
        Appointment::findOrFail($id)->delete();
        flash('Agendamento apagado com sucesso!')->success();
        return redirect()->route('backoffice.appointments.index');
    }

    public function show($id)
    {
        $appointment = Appointment::with(['store', 'supplier'])->findOrFail($id);
        return view('backoffice.appointments.show', compact('appointment'));
    }

}
