<?php

namespace App\Http\Controllers;

use App\Models\HrHoliday;
use App\Models\HrVacation;
use App\Services\WorkingDays;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HrHolidayController extends Controller
{
    public function __construct(private WorkingDays $workingDays)
    {
    }

    public function index(Request $request)
    {
        $year = (int) ($request->input('ano') ?: now()->year);

        $national = $this->workingDays->nationalHolidays($year);
        $custom = HrHoliday::with('creator')
            ->where(fn ($q) => $q->where('recorrente', true)->orWhereYear('data', $year))
            ->orderByRaw("DATE_FORMAT(data, '%m-%d')")
            ->get();

        return view('backoffice.hr.holidays.index', compact('year', 'national', 'custom'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'data' => ['required', 'date'],
            'nome' => ['required', 'string', 'max:255'],
        ], [
            'nome.required' => 'Indique o nome do feriado.',
        ]);

        $date = Carbon::parse($data['data']);
        $recorrente = $request->boolean('recorrente');

        if (isset($this->workingDays->nationalHolidays($date->year)[$date->toDateString()])) {
            throw ValidationException::withMessages(['data' => 'Esse dia já é feriado nacional.']);
        }

        $exists = HrHoliday::where(fn ($q) => $q
                ->whereDate('data', $date)
                ->orWhere(fn ($r) => $r->where('recorrente', true)->whereMonth('data', $date->month)->whereDay('data', $date->day)))
            ->exists();
        if ($exists) {
            throw ValidationException::withMessages(['data' => 'Já existe um feriado registado nesse dia.']);
        }

        HrHoliday::create($data + ['recorrente' => $recorrente, 'created_by' => auth()->id()]);
        $updated = $this->recalculateVacations();

        flash('Feriado "' . $data['nome'] . '" adicionado' . ($updated ? '. Foram recalculados os dias de ' . $updated . ' marcação(ões) de férias.' : '.'))->success();

        return redirect()->route('backoffice.hr.holidays.index', ['ano' => $date->year]);
    }

    public function destroy($id)
    {
        $holiday = HrHoliday::findOrFail($id);
        $holiday->delete();
        $updated = $this->recalculateVacations();

        flash('Feriado removido' . ($updated ? '. Foram recalculados os dias de ' . $updated . ' marcação(ões) de férias.' : '.'))->success();

        return back();
    }

    /** Feriados para os calendários (dashboard e escala): fundo sombreado + nome. */
    public function events(Request $request)
    {
        $from = Carbon::parse($request->input('start', now()->startOfMonth()))->startOfDay();
        $to = Carbon::parse($request->input('end', now()->endOfMonth()))->startOfDay();

        $events = [];
        foreach (range($from->year, $to->year) as $year) {
            $national = $this->workingDays->nationalHolidays($year);
            foreach ($this->workingDays->holidays($year) as $date => $name) {
                if ($date < $from->toDateString() || $date >= $to->toDateString()) {
                    continue;
                }
                $events[] = ['start' => $date, 'display' => 'background', 'color' => '#ffcdd2'];
                $events[] = [
                    'title' => $name,
                    'start' => $date,
                    'allDay' => true,
                    'color' => isset($national[$date]) ? '#c62828' : '#ad1457',
                    'feriado' => isset($national[$date]) ? 'Feriado nacional' : 'Feriado da empresa',
                    'description' => isset($national[$date]) ? 'Feriado nacional' : 'Feriado da empresa',
                ];
            }
        }

        return response()->json($events);
    }

    /** Volta a contar os dias úteis das férias marcadas (depois de mudar feriados). */
    private function recalculateVacations(): int
    {
        $this->workingDays->forgetCache();
        $updated = 0;

        foreach (HrVacation::all() as $vacation) {
            $days = $this->workingDays->count($vacation->data_inicio, $vacation->data_fim);
            if ($days !== $vacation->dias_uteis) {
                $vacation->update(['dias_uteis' => $days]);
                $updated++;
            }
        }

        return $updated;
    }
}
