<?php

namespace App\Models;

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

    /** Prazo do ano: [início, fim]. */
    public function window(int $year): array
    {
        $days = (int) config('maintenance.window_days', 15);
        $due = $this->dueDate($year);

        return [$due->copy()->subDays($days), $due->copy()->addDays($days)];
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
