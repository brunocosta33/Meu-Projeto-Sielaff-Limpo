<?php

namespace App\Services;

use App\Models\HrHoliday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * Dias úteis em Portugal: segunda a sexta, excluindo feriados nacionais
 * e os feriados próprios registados em Recursos Humanos → Feriados.
 */
class WorkingDays
{
    private array $cache = [];

    /** @return array<string, string> 'Y-m-d' => nome do feriado (nacionais + próprios) */
    public function holidays(int $year): array
    {
        if (isset($this->cache[$year])) {
            return $this->cache[$year];
        }

        $holidays = $this->nationalHolidays($year);

        $custom = HrHoliday::where('recorrente', true)
            ->orWhereYear('data', $year)
            ->get();
        foreach ($custom as $holiday) {
            $holidays[$year . '-' . $holiday->data->format('m-d')] ??= $holiday->nome;
        }

        ksort($holidays);

        return $this->cache[$year] = $holidays;
    }

    /** @return array<string, string> feriados nacionais obrigatórios */
    public function nationalHolidays(int $year): array
    {
        $easter = $this->easter($year);

        $holidays = [
            "$year-01-01" => __('Ano Novo'),
            $easter->copy()->subDays(2)->toDateString() => __('Sexta-feira Santa'),
            $easter->toDateString() => __('Páscoa'),
            "$year-04-25" => __('Dia da Liberdade'),
            "$year-05-01" => __('Dia do Trabalhador'),
            $easter->copy()->addDays(60)->toDateString() => __('Corpo de Deus'),
            "$year-06-10" => __('Dia de Portugal'),
            "$year-08-15" => __('Assunção de Nossa Senhora'),
            "$year-10-05" => __('Implantação da República'),
            "$year-11-01" => __('Dia de Todos os Santos'),
            "$year-12-01" => __('Restauração da Independência'),
            "$year-12-08" => __('Imaculada Conceição'),
            "$year-12-25" => __('Natal'),
        ];

        ksort($holidays);

        return $holidays;
    }

    public function forgetCache(): void
    {
        $this->cache = [];
    }

    public function isWorkingDay(Carbon $date): bool
    {
        return !$date->isWeekend() && !isset($this->holidays($date->year)[$date->toDateString()]);
    }

    public function count(Carbon $from, Carbon $to): int
    {
        if ($to->lt($from)) {
            return 0;
        }

        $days = 0;
        foreach (CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()) as $date) {
            if ($this->isWorkingDay($date)) {
                $days++;
            }
        }

        return $days;
    }

    /** Dias úteis de um período que caem dentro de um determinado ano. */
    public function countInYear(Carbon $from, Carbon $to, int $year): int
    {
        $start = $from->copy()->max(Carbon::create($year, 1, 1));
        $end = $to->copy()->min(Carbon::create($year, 12, 31));

        return $this->count($start, $end);
    }

    /** Domingo de Páscoa (algoritmo de Meeus/Jones/Butcher, calendário gregoriano). */
    private function easter(int $year): Carbon
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($year, $month, $day);
    }
}
