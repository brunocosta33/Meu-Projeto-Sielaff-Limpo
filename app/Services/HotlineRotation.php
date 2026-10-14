<?php

namespace App\Services;

use App\Models\HrAbsence;
use App\Models\HrShiftOverride;
use App\Models\HrStaff;
use App\Models\HrVacation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Calcula a escala rotativa dos hotliners a partir da regra em config/hr.php.
 * Só as substituições são gravadas na base de dados; o resto é calculado.
 */
class HotlineRotation
{
    public const COLORS = ['#6f42c1', '#00897b', '#ef6c00', '#3949ab'];
    public const UNCOVERED_COLOR = '#c62828';

    public function weekType(Carbon $date, string $group): string
    {
        $reference = Carbon::parse(config('hr.reference_monday'))->startOfWeek();
        $weeks = intdiv((int) $reference->diffInDays($date->copy()->startOfWeek(), false), 7);
        $even = $weeks % 2 === 0;

        // Grupo A faz fim de semana nas semanas pares, grupo B nas ímpares
        return ($group === 'A') === $even ? 'fim_de_semana' : 'semana';
    }

    /** Turno previsto pela rotação, ou null se for folga. */
    public function scheduledShift(HrStaff $staff, Carbon $date): ?array
    {
        $pattern = config('hr.week_patterns.' . $this->weekType($date, $staff->rotation_group));
        $hours = $pattern['days'][$date->dayOfWeekIso] ?? null;

        return $hours ? ['inicio' => $hours[0], 'fim' => $hours[1]] : null;
    }

    /**
     * Escala para um intervalo de datas: um item por dia, com uma célula por hotliner.
     */
    public function schedule(Carbon $from, Carbon $to, ?Collection $hotliners = null): array
    {
        $hotliners ??= HrStaff::activeHotliners()->with('user')->orderBy('rotation_group')->get();

        $overrides = HrShiftOverride::with('substitute')
            ->whereBetween('data', [$from->toDateString(), $to->toDateString()])
            ->whereIn('hr_staff_id', $hotliners->pluck('id'))
            ->get()
            ->keyBy(fn ($o) => $o->data->toDateString() . '_' . $o->hr_staff_id);

        // Férias aprovadas: o turno fica a descoberto até haver substituição
        $vacations = HrVacation::approved()
            ->whereIn('hr_staff_id', $hotliners->pluck('id'))
            ->overlapping($from->toDateString(), $to->toDateString())
            ->get()
            ->groupBy('hr_staff_id');

        // Faltas de dia inteiro: igual às férias
        $absences = HrAbsence::whereIn('hr_staff_id', $hotliners->pluck('id'))
            ->whereNull('hora_inicio')
            ->overlapping($from->toDateString(), $to->toDateString())
            ->get()
            ->groupBy('hr_staff_id');

        $days = [];
        foreach (CarbonPeriod::create($from, $to) as $date) {
            $cells = [];
            foreach ($hotliners as $staff) {
                $shift = $this->scheduledShift($staff, $date);
                $override = $overrides->get($date->toDateString() . '_' . $staff->id);
                $vacation = $vacations->get($staff->id)?->first(fn ($v) => $date->between($v->data_inicio, $v->data_fim));
                $absence = $absences->get($staff->id)?->first(fn ($a) => $date->between($a->data_inicio, $a->data_fim));

                if ($override && $shift) {
                    $shift['inicio'] = $override->hora_inicio ? substr($override->hora_inicio, 0, 5) : $shift['inicio'];
                    $shift['fim'] = $override->hora_fim ? substr($override->hora_fim, 0, 5) : $shift['fim'];
                }

                $cells[$staff->id] = [
                    'staff' => $staff,
                    'shift' => $shift,
                    'override' => $override,
                    'vacation' => $vacation,
                    'absence' => $absence,
                    'worker' => $override ? $override->substitute : (($vacation || $absence) ? null : $staff->user),
                ];
            }
            $days[] = ['date' => $date->copy(), 'cells' => $cells];
        }

        return $days;
    }

    /**
     * Lista "plana" de turnos (só dias com turno), com início/fim em data-hora e cor do hotliner.
     */
    public function shifts(Carbon $from, Carbon $to, ?Collection $hotliners = null): Collection
    {
        $hotliners ??= HrStaff::activeHotliners()->with('user')->orderBy('rotation_group')->get();
        $reasons = config('hr.override_reasons');

        $shifts = collect();
        foreach ($this->schedule($from, $to, $hotliners) as $day) {
            foreach ($day['cells'] as $cell) {
                if (!$cell['shift']) {
                    continue;
                }

                $start = Carbon::parse($day['date']->toDateString() . ' ' . $cell['shift']['inicio']);
                $end = Carbon::parse($day['date']->toDateString() . ' ' . $cell['shift']['fim']);
                if ($end->lte($start)) {
                    $end->addDay();
                }

                $override = $cell['override'];
                $onVacation = !$override && $cell['vacation'];
                $absent = !$override && !$onVacation && $cell['absence'];
                $uncovered = ($override && !$override->substitute_user_id) || $onVacation || $absent;

                $shifts->push((object) [
                    'date' => $day['date'],
                    'start' => $start,
                    'end' => $end,
                    'hours' => self::formatHours($cell['shift']),
                    'funcao' => 'Hotliner',
                    'staff' => $cell['staff'],
                    'name' => $cell['worker']->name ?? 'Sem substituto',
                    'override' => $override,
                    'override_note' => $override
                        ? 'Substitui ' . $cell['staff']->user->name . ' (' . ($reasons[$override->motivo] ?? $override->motivo) . ')'
                        : ($onVacation ? $cell['staff']->user->name . ' está de férias'
                            : ($absent ? $cell['staff']->user->name . ' faltou (' . $cell['absence']->motivo_label . ')' : null)),
                    'uncovered' => $uncovered,
                    'color' => $uncovered
                        ? self::UNCOVERED_COLOR
                        : self::COLORS[$hotliners->search(fn ($s) => $s->id === $cell['staff']->id) % count(self::COLORS)],
                ]);
            }
        }

        return $shifts;
    }

    public static function formatHours(?array $shift): string
    {
        if (!$shift) {
            return 'Folga';
        }

        $nextDay = $shift['fim'] <= $shift['inicio'] ? ' (+1)' : '';

        return $shift['inicio'] . '–' . $shift['fim'] . $nextDay;
    }
}
