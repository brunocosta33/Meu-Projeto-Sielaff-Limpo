<?php

namespace App\Models;

use App\Services\WorkingDays;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StoreMaintenance extends Model
{
    protected $fillable = [
        'store_id',
        'data_contratada',
        'ativo',
        'observacoes',
    ];

    protected $casts = [
        'data_contratada' => 'date',
        'ativo' => 'boolean',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function records()
    {
        return $this->hasMany(StoreMaintenanceRecord::class);
    }

    /** Data da manutenção num ano (mesmo dia e mês da data contratada; 29/02 passa a 28/02). */
    public function dueDate(int $year): Carbon
    {
        $month = $this->data_contratada->month;
        $day = min($this->data_contratada->day, Carbon::create($year, $month, 1)->daysInMonth);

        return Carbon::create($year, $month, $day)->startOfDay();
    }

    /** Feriados por ano (nacionais + da empresa), partilhados por todas as lojas no mesmo pedido. */
    private static array $holidayCache = [];

    /** Dia útil para as manutenções: segunda a sábado, sem feriados. */
    public static function isMaintenanceDay(Carbon $date): bool
    {
        $year = $date->year;
        self::$holidayCache[$year] ??= app(WorkingDays::class)->holidays($year);

        return !$date->isSunday() && !isset(self::$holidayCache[$year][$date->toDateString()]);
    }

    /** Anda $days dias úteis (segunda a sábado, sem feriados) para trás (-1) ou para a frente (+1). */
    private static function shiftWorkingDays(Carbon $from, int $days, int $direction): Carbon
    {
        $date = $from->copy();
        while ($days > 0) {
            $date->addDays($direction);
            if (self::isMaintenanceDay($date)) {
                $days--;
            }
        }

        return $date;
    }

    /** Dias úteis (segunda a sábado, sem feriados) depois de $from até $to, inclusive. */
    public static function workingDaysBetween(Carbon $from, Carbon $to): int
    {
        $count = 0;
        for ($d = $from->copy()->addDay(); $d->lte($to); $d->addDay()) {
            if (self::isMaintenanceDay($d)) {
                $count++;
            }
        }

        return $count;
    }

    /** Prazo do ano: [início, fim] = X dias úteis antes e depois da data contratada. */
    public function window(int $year): array
    {
        $days = (int) config('maintenance.window_working_days', 12);
        $due = $this->dueDate($year);

        return [self::shiftWorkingDays($due, $days, -1), self::shiftWorkingDays($due, $days, 1)];
    }

    /** O contrato já existia nesse ano? */
    public function appliesTo(int $year): bool
    {
        return $this->data_contratada->year <= $year;
    }

    /** Estado num ano: feita, feita_fora, atrasada, janela, proxima ou futura. */
    public function status(int $year, ?StoreMaintenanceRecord $record, ?Carbon $today = null): string
    {
        $today = ($today ?? now())->copy()->startOfDay();
        [$start, $end] = $this->window($year);

        if ($record) {
            return $record->data_realizada->between($start, $end) ? 'feita' : 'feita_fora';
        }
        if ($today->gt($end)) {
            return 'atrasada';
        }
        if ($today->gte($start)) {
            return 'janela';
        }

        return $today->gte($start->copy()->subDays((int) config('maintenance.upcoming_days', 30))) ? 'proxima' : 'futura';
    }
}
