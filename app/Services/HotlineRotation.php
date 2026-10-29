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
 * Calcula as escalas rotativas a partir das regras em config/hr.php:
 * hotliners (semanas alternadas) e técnicos de piquete (sábados alternados, Norte + Sul).
 * Só as substituições são gravadas na base de dados; o resto é calculado.
 */
class HotlineRotation
{
    public const COLORS = ['#6f42c1', '#00897b', '#ef6c00', '#3949ab'];
    public const PIQUETE_COLORS = ['#2e7d32', '#0277bd', '#6d4c41', '#ad1457'];
    public const UNCOVERED_COLOR = '#c62828';

    /** Colaboradores de uma escala: 'hotline', 'piquete' ou 'todos'. */
    public static function staffFor(string $kind = 'todos'): Collection
    {
        $hotline = $kind !== 'piquete' ? HrStaff::activeHotliners()->with('user')->orderBy('rotation_group')->get() : collect();
        $piquete = $kind !== 'hotline' ? HrStaff::activePiquete()->with('user')->orderBy('rotation_group')->orderBy('zona')->get() : collect();

        return $hotline->concat($piquete)->values();
    }

    /** Escala a que o colaborador pertence. */
    public static function kindOf(HrStaff $staff): string
    {
        return $staff->funcao === 'tecnico' ? 'piquete' : 'hotline';
    }

    /**
     * Semanas inteiras entre a segunda de referência e a semana de uma data.
     * Conta só com datas, em UTC: as datas que chegam do calendário trazem o fuso colado
     * (ex.: +01:00 no horário de verão) e, passada a mudança da hora, esse offset deixa de
     * corresponder à hora local — o startOfWeek caía na segunda-feira errada e o ciclo saltava.
     */
    private static function weeksSince(string $reference, Carbon $date): int
    {
        $inicio = Carbon::parse($reference, 'UTC')->startOfWeek();
        $semana = Carbon::parse($date->toDateString(), 'UTC')->startOfWeek();

        return intdiv((int) $inicio->diffInDays($semana, false), 7);
    }

    public function weekType(Carbon $date, string $group): string
    {
        $weeks = self::weeksSince(config('hr.reference_monday'), $date);
        $even = $weeks % 2 === 0;

        // Grupo A faz fim de semana nas semanas pares, grupo B nas ímpares
        return ($group === 'A') === $even ? 'fim_de_semana' : 'semana';
    }

    /** Turno previsto pela rotação, ou null se for folga. */
    public function scheduledShift(HrStaff $staff, Carbon $date): ?array
    {
        if (self::kindOf($staff) === 'piquete') {
            return $this->piqueteShift($staff, $date);
        }

        $pattern = config('hr.week_patterns.' . $this->weekType($date, $staff->rotation_group));
        $hours = $pattern['days'][$date->dayOfWeekIso] ?? null;

        return $hours ? ['inicio' => $hours[0], 'fim' => $hours[1]] : null;
    }

    /** Piquete: sábados alternados; o grupo A está de serviço no sábado de referência. */
    public function piqueteShift(HrStaff $staff, Carbon $date): ?array
    {
        $config = config('hr.piquete');
        if (!in_array($date->dayOfWeekIso, $config['days'], true) || !$staff->rotation_group) {
            return null;
        }

        $weeks = self::weeksSince($config['reference_saturday'], $date);
        $groupOnDuty = $weeks % 2 === 0 ? 'A' : 'B';

        return $staff->rotation_group === $groupOnDuty
            ? ['inicio' => $config['hours'][0], 'fim' => $config['hours'][1]]
            : null;
    }

    /**
     * Escala para um intervalo de datas: um item por dia, com uma célula por colaborador.
     */
    public function schedule(Carbon $from, Carbon $to, ?Collection $hotliners = null): array
    {
        $hotliners ??= self::staffFor('hotline');

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
     * Lista "plana" de turnos (só dias com turno), com início/fim em data-hora e cor do colaborador.
     */
    public function shifts(Carbon $from, Carbon $to, ?Collection $hotliners = null): Collection
    {
        $hotliners ??= self::staffFor('hotline');
        $reasons = config('hr.override_reasons');
        $colors = self::colorMap($hotliners);
        // A cor é da pessoa que está a trabalhar, não de quem estava escalado:
        // cada um tem sempre a mesma cor, mesmo quando faz o turno de outro
        $colorByUser = $hotliners->filter(fn ($s) => $s->user_id)->mapWithKeys(fn ($s) => [$s->user_id => $colors[$s->id]]);

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
                    'kind' => self::kindOf($cell['staff']),
                    'funcao' => self::kindOf($cell['staff']) === 'piquete' ? __(config('hr.piquete.label')) : __('Hotliner'),
                    'zona' => $cell['staff']->zona ? __(config('hr.zones.' . $cell['staff']->zona)) : null,
                    'staff' => $cell['staff'],
                    'name' => $cell['worker']->name ?? __('Sem substituto'),
                    // Quem estava escalado e porque não está: o calendário mostra-o na própria marca
                    'titular' => $cell['staff']->user->name,
                    'motivo' => $override
                        ? __($reasons[$override->motivo] ?? $override->motivo)
                        : ($onVacation ? __('Férias') : ($absent ? $cell['absence']->motivo_label : null)),
                    'public_motivo' => $override
                        ? self::publicReason($override->motivo)
                        : ($onVacation ? __('Férias') : ($absent ? __('Ausente') : null)),
                    'worker_id' => $cell['worker']->id ?? null,
                    'override' => $override,
                    'override_note' => $override
                        ? __('Substitui :nome (:motivo)', ['nome' => $cell['staff']->user->name, 'motivo' => __($reasons[$override->motivo] ?? $override->motivo)])
                        : ($onVacation ? __(':nome está de férias', ['nome' => $cell['staff']->user->name])
                            : ($absent ? __(':nome faltou (:motivo)', ['nome' => $cell['staff']->user->name, 'motivo' => $cell['absence']->motivo_label]) : null)),
                    // Versão sem o motivo real, para quem não é gestão de RH (férias e trocas ficam visíveis)
                    'public_note' => $override
                        ? __('Substitui :nome (:motivo)', ['nome' => $cell['staff']->user->name, 'motivo' => self::publicReason($override->motivo)])
                        : ($onVacation ? __(':nome está de férias', ['nome' => $cell['staff']->user->name])
                            : ($absent ? __(':nome ausente', ['nome' => $cell['staff']->user->name]) : null)),
                    'uncovered' => $uncovered,
                    'troca' => (bool) $override && $override->motivo === 'troca',
                    'color' => $uncovered
                        ? self::UNCOVERED_COLOR
                        : ($colorByUser[$cell['worker']->id ?? null] ?? $colors[$cell['staff']->id]),
                ]);
            }
        }

        return $shifts;
    }

    /** Motivo de substituição que todos podem ver: férias e trocas; o resto aparece como "Ausente". */
    public static function publicReason(string $motivo): string
    {
        return match ($motivo) {
            'ferias' => __('Férias'),
            'troca' => __('Troca'),
            default => __('Ausente'),
        };
    }

    /** Cor de cada colaborador: hotliners e técnicos com paletas diferentes. */
    public static function colorMap(Collection $staff): array
    {
        $map = [];
        foreach ($staff->groupBy(fn ($s) => self::kindOf($s)) as $kind => $group) {
            $palette = $kind === 'piquete' ? self::PIQUETE_COLORS : self::COLORS;
            foreach ($group->values() as $i => $member) {
                $map[$member->id] = $palette[$i % count($palette)];
            }
        }

        return $map;
    }

    public static function formatHours(?array $shift): string
    {
        if (!$shift) {
            return __('Folga');
        }

        $nextDay = $shift['fim'] <= $shift['inicio'] ? ' (+1)' : '';

        return $shift['inicio'] . '–' . $shift['fim'] . $nextDay;
    }
}
