<?php

namespace App\Http\Controllers;

use App\Models\HrAbsence;
use App\Models\HrStaff;
use App\Models\HrVacation;
use App\Models\HrVacationBalance;
use App\Services\HrAccess;
use App\Services\VacationBalance;
use App\Services\WorkingDays;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HrVacationController extends Controller
{
    public const COLORS = ['#1976d2', '#e53935', '#43a047', '#8e24aa', '#fb8c00', '#00897b', '#6d4c41', '#d81b60', '#3949ab', '#7cb342'];

    public function __construct(private WorkingDays $workingDays, private VacationBalance $balances)
    {
    }

    public function index(Request $request)
    {
        $year = (int) ($request->input('ano') ?: now()->year);
        $filterStaff = $request->input('colaborador');
        $canEdit = HrAccess::isManager(auth()->user());

        // Quem não é gestão de RH só vê a sua parte
        if (!$canEdit) {
            $filterStaff = HrAccess::ownStaffIdFilter(auth()->user());
        }

        $staff = HrStaff::with('user')->where('ativo', true)->get()->sortBy(fn ($s) => $s->user->name ?? '')->values();
        $colors = $staff->mapWithKeys(fn ($s, $i) => [$s->id => self::COLORS[$i % count(self::COLORS)]]);

        $vacations = HrVacation::with(['staff.user', 'creator', 'decider'])
            ->overlapping("$year-01-01", "$year-12-31")
            ->when($filterStaff, fn ($q) => $q->where('hr_staff_id', $filterStaff))
            ->orderBy('data_inicio')
            ->get();

        // Dia => férias (pendentes e aprovadas) para pintar o calendário
        $days = [];
        foreach ($vacations->whereIn('estado', ['pendente', 'aprovado']) as $vacation) {
            foreach (CarbonPeriod::create($vacation->data_inicio, $vacation->data_fim) as $date) {
                if ($date->year === $year) {
                    $days[$date->toDateString()][] = $vacation;
                }
            }
        }

        $absences = HrAbsence::with(['staff.user', 'creator', 'decider'])
            ->overlapping("$year-01-01", "$year-12-31")
            ->when($filterStaff, fn ($q) => $q->where('hr_staff_id', $filterStaff))
            ->orderBy('data_inicio')
            ->get();

        $absenceDays = [];
        foreach ($absences as $absence) {
            foreach (CarbonPeriod::create($absence->data_inicio, $absence->data_fim) as $date) {
                if ($date->year === $year) {
                    $absenceDays[$date->toDateString()][] = $absence;
                }
            }
        }

        $summary = $staff
            ->when($filterStaff, fn ($c) => $c->where('id', (int) $filterStaff))
            ->map(fn ($s) => ['staff' => $s, 'color' => $colors[$s->id], 'faltas' => $this->absenceTotals($s, $year)]
                + $this->balances->forYear($s, $year));

        return view('backoffice.hr.vacations.index', [
            'year' => $year,
            'staff' => $staff,
            'filterStaff' => $filterStaff,
            'colors' => $colors,
            'vacations' => $vacations,
            'days' => $days,
            'absences' => $absences,
            'absenceDays' => $absenceDays,
            'summary' => $summary,
            'canApprove' => HrStaff::canApproveVacations(auth()->user()),
            'canEdit' => $canEdit,
            'approverNames' => $this->approverNames(),
            'holidays' => $this->workingDays->holidays($year),
            'holidaysJs' => $this->workingDays->holidays($year - 1) + $this->workingDays->holidays($year) + $this->workingDays->holidays($year + 1),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'hr_staff_id' => ['required', 'exists:hr_staff,id'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ], [
            'data_fim.after_or_equal' => 'A data de fim tem de ser igual ou posterior à data de início.',
        ]);

        $staff = HrStaff::with('user')->findOrFail($data['hr_staff_id']);
        $from = Carbon::parse($data['data_inicio']);
        $to = Carbon::parse($data['data_fim']);
        $days = $this->workingDays->count($from, $to);

        if ($days === 0) {
            throw ValidationException::withMessages(['data_inicio' => 'O período escolhido não tem dias úteis (só fins de semana ou feriados).']);
        }

        $overlap = HrVacation::active()->where('hr_staff_id', $staff->id)->overlapping($from, $to)->first();
        if ($overlap) {
            throw ValidationException::withMessages([
                'data_inicio' => $staff->user->name . ' já tem férias marcadas de ' . $overlap->data_inicio->format('d/m') . ' a ' . $overlap->data_fim->format('d/m') . ' que se sobrepõem a este período.',
            ]);
        }

        $absence = HrAbsence::where('hr_staff_id', $staff->id)->overlapping($from, $to)->first();
        if ($absence) {
            throw ValidationException::withMessages([
                'data_inicio' => $staff->user->name . ' tem uma falta registada a ' . $absence->data_inicio->format('d/m') . ' nesse período.',
            ]);
        }

        $approveNow = $request->boolean('aprovar');
        if ($approveNow && ($error = $this->approvalError())) {
            flash($error . ' As férias ficaram pendentes.')->warning();
            $approveNow = false;
        }
        HrVacation::create($data + [
            'dias_uteis' => $days,
            'estado' => $approveNow ? 'aprovado' : 'pendente',
            'created_by' => auth()->id(),
            'decided_by' => $approveNow ? auth()->id() : null,
            'decided_at' => $approveNow ? now() : null,
        ]);

        foreach (range($from->year, $to->year) as $year) {
            $balance = $this->balances->forYear($staff, $year);
            if ($balance['disponiveis'] < 0) {
                flash('Atenção: ' . $staff->user->name . ' fica com ' . $balance['disponiveis'] . ' dias de férias em ' . $year . ' (excede o total de ' . $balance['total'] . ').')->warning();
            }
        }

        flash('Férias de ' . $staff->user->name . ' marcadas: ' . $days . ' dias úteis' . ($approveNow ? ' (aprovadas).' : ' (a aguardar aprovação).'))->success();

        return redirect()->route('backoffice.hr.vacations.index', ['ano' => $from->year]);
    }

    /** Só coordenadores e country managers decidem férias (incluindo as próprias). */
    private function approvalError(): ?string
    {
        if (!HrStaff::canApproveVacations(auth()->user())) {
            return 'Só quem tem a função ' . $this->approverNames() . ' pode aprovar ou rejeitar férias.';
        }

        return null;
    }

    private function approverNames(): string
    {
        return implode(' ou ', array_map(fn ($f) => config('hr.functions.' . $f), config('hr.vacation_approver_functions', [])));
    }

    /** Faltas do ano por situação: dias úteis e horas (faltas de parte do dia). */
    private function absenceTotals(HrStaff $staff, int $year): array
    {
        $totals = array_fill_keys(array_keys(HrAbsence::STATES), ['dias' => 0, 'horas' => 0.0]);

        HrAbsence::where('hr_staff_id', $staff->id)
            ->overlapping("$year-01-01", "$year-12-31")
            ->get()
            ->each(function (HrAbsence $a) use (&$totals, $year) {
                if ($a->isPartial()) {
                    $totals[$a->situacao]['horas'] += $a->horas;
                } else {
                    $totals[$a->situacao]['dias'] += $this->workingDays->countInYear($a->data_inicio, $a->data_fim, $year);
                }
            });

        return $totals;
    }

    public function approve($id)
    {
        $vacation = HrVacation::findOrFail($id);
        if ($error = $this->approvalError()) {
            flash($error)->error();
            return back();
        }

        $vacation->update(['estado' => 'aprovado', 'motivo_rejeicao' => null, 'decided_by' => auth()->id(), 'decided_at' => now()]);

        flash('Férias aprovadas.')->success();
        return back();
    }

    public function reject(Request $request, $id)
    {
        $vacation = HrVacation::findOrFail($id);
        if ($error = $this->approvalError()) {
            flash($error)->error();
            return back();
        }

        $vacation->update([
            'estado' => 'rejeitado',
            'motivo_rejeicao' => $request->input('motivo_rejeicao'),
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);

        flash('Férias rejeitadas.')->success();
        return back();
    }

    public function destroy($id)
    {
        HrVacation::findOrFail($id)->delete();

        flash('Marcação de férias apagada.')->success();
        return back();
    }

    public function updateBalance(Request $request, $staffId)
    {
        $data = $request->validate([
            'ano' => ['required', 'integer', 'min:2000', 'max:2100'],
            'dias_direito' => ['required', 'integer', 'min:0', 'max:60'],
            'dias_transitados' => ['nullable', 'integer', 'min:0', 'max:60'],
        ]);

        $staff = HrStaff::with('user')->findOrFail($staffId);
        HrVacationBalance::updateOrCreate(
            ['hr_staff_id' => $staff->id, 'ano' => $data['ano']],
            ['dias_direito' => $data['dias_direito'], 'dias_transitados' => $data['dias_transitados']]
        );

        flash('Dias de férias de ' . $staff->user->name . ' em ' . $data['ano'] . ' atualizados.')->success();
        return back();
    }
}
