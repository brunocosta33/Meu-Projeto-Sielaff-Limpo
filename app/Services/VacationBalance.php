<?php

namespace App\Services;

use App\Models\HrStaff;
use App\Models\HrVacation;
use App\Models\HrVacationBalance;

/**
 * Saldo de férias de um colaborador num ano:
 * direito + transitados do ano anterior - aprovadas - pendentes.
 */
class VacationBalance
{
    public function __construct(private WorkingDays $workingDays)
    {
    }

    public function forYear(HrStaff $staff, int $year): array
    {
        $record = HrVacationBalance::where('hr_staff_id', $staff->id)->where('ano', $year)->first();

        $direito = $record?->dias_direito ?? (int) config('hr.vacation_default_days', 25);
        $transitadosAuto = $record?->dias_transitados === null;
        $transitados = $transitadosAuto ? $this->carryOver($staff, $year) : $record->dias_transitados;

        $aprovados = $this->usedDays($staff, $year, 'aprovado');
        $pendentes = $this->usedDays($staff, $year, 'pendente');
        $total = $direito + $transitados;

        return [
            'direito' => $direito,
            'transitados' => $transitados,
            'transitados_auto' => $transitadosAuto,
            'total' => $total,
            'aprovados' => $aprovados,
            'pendentes' => $pendentes,
            'disponiveis' => $total - $aprovados - $pendentes,
        ];
    }

    /** Dias que sobraram do ano anterior (só se o colaborador já estava registado nesse ano). */
    public function carryOver(HrStaff $staff, int $year): int
    {
        $previous = $year - 1;
        if (!$staff->created_at || $staff->created_at->year > $previous) {
            return 0;
        }

        $balance = $this->forYear($staff, $previous);

        return max(0, $balance['total'] - $balance['aprovados']);
    }

    public function usedDays(HrStaff $staff, int $year, string $estado): int
    {
        return HrVacation::where('hr_staff_id', $staff->id)
            ->where('estado', $estado)
            ->overlapping("$year-01-01", "$year-12-31")
            ->get()
            ->sum(fn ($v) => $this->workingDays->countInYear($v->data_inicio, $v->data_fim, $year));
    }
}
