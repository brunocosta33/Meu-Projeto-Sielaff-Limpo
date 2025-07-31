<?php

namespace App\Http\Controllers;

use App\Models\TechnicalSchedule;
use App\Models\TechnicalRequest;
use Illuminate\Http\Request;

class TechnicalScheduleController extends Controller
{
    public function index() {
        $schedules = TechnicalSchedule::with('request')->paginate(15);
        return view('backoffice.technical_schedules.index', compact('schedules'));
    }

    public function create() {
        $requests = TechnicalRequest::where('estado', 'pendente')->get();
        return view('backoffice.technical_schedules.create', compact('requests'));
    }

    public function store(Request $request) {
        TechnicalSchedule::create($request->all());
        TechnicalRequest::where('id', $request->technical_request_id)->update(['estado' => 'agendado']);
        return redirect()->route('backoffice.technical_schedules.index')->with('success', 'Assistência agendada!');
    }

    public function show($id) {
        $schedule = TechnicalSchedule::with('request')->findOrFail($id);
        return view('backoffice.technical_schedules.show', compact('schedule'));
    }

    public function edit($id) {
        $schedule = TechnicalSchedule::findOrFail($id);
        return view('backoffice.technical_schedules.edit', compact('schedule'));
    }

    public function update(Request $request, $id) {
        $schedule = TechnicalSchedule::findOrFail($id);
        $schedule->update($request->all());
        return redirect()->route('backoffice.technical_schedules.index')->with('success', 'Agendamento atualizado.');
    }

    public function delete($id) {
        $schedule = TechnicalSchedule::findOrFail($id);
        $schedule->delete();
        return redirect()->route('backoffice.technical_schedules.index')->with('success', 'Agendamento removido.');
    }
}
