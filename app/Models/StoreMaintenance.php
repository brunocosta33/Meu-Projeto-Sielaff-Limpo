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

    /** O mês contratado nesse ano (dia 1). */
    public function dueDate(int $year): Carbon
    {
        return Carbon::create($year, $this->data_contratada->month, 1)->startOfDay();
    }

    /** [meses antes, meses depois] do mês contratado em que ainda conta como feita a tempo. */
    public function tolerancia(): array
    {
        $t = config('maintenance.tolerancia_meses', []);

        return $t[$this->store?->clienteKey()] ?? $t['default'] ?? [0, 0];
    }

    /** Pode ser feita fora do mês contratado (Lidl) ou só nesse mês (Sonae)? */
    public function isFlexivel(): bool
    {
        return array_sum($this->tolerancia()) > 0;
    }

    /**
     * Prazo do ano: do início do mês contratado (menos a tolerância) ao fim do mês
     * contratado (mais a tolerância). O último dia é a data limite.
     */
    public function window(int $year): array
    {
        $mes = $this->dueDate($year)->startOfMonth();
        [$antes, $depois] = $this->tolerancia();

        return [
            $mes->copy()->subMonthsNoOverflow($antes)->startOfMonth(),
            $mes->copy()->addMonthsNoOverflow($depois)->endOfMonth()->startOfDay(),
        ];
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
