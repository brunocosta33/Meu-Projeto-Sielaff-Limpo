<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrTimeEntry extends Model
{
    public const STATES = [
        'pendente' => ['label' => 'Pendente', 'class' => 'badge-warning'],
        'aprovado' => ['label' => 'Aprovado', 'class' => 'badge-success'],
        'rejeitado' => ['label' => 'Rejeitado', 'class' => 'badge-secondary'],
    ];

    protected $fillable = [
        'hr_staff_id',
        'data',
        'tipo',
        'horas',
        'valor',
        'descricao',
        'estado',
        'referencia',
        'created_by',
        'decided_by',
        'decided_at',
    ];

    protected $casts = [
        'data' => 'date',
        'horas' => 'float',
        'valor' => 'float',
        'decided_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(HrStaff::class, 'hr_staff_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function getTypeAttribute(): array
    {
        return config('hr.time_entry_types.' . $this->tipo, ['label' => $this->tipo, 'sign' => 1, 'unit' => 'horas', 'color' => '#6c757d', 'icon' => 'fa-clock']);
    }

    /** Quantidade com sinal (+ a favor do colaborador, − a favor da empresa / acerto). */
    public function getSignedAmountAttribute(): float
    {
        $amount = $this->type['unit'] === 'euros' ? $this->valor : $this->horas;

        return $this->type['sign'] * $amount;
    }

    /** Formata horas decimais como "7h30". */
    public static function formatHours(float $hours, bool $withSign = false): string
    {
        $sign = $hours < 0 ? '−' : ($withSign && $hours > 0 ? '+' : '');
        $minutes = (int) round(abs($hours) * 60);

        return $sign . intdiv($minutes, 60) . 'h' . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT);
    }

    public static function formatEuros(float $value, bool $withSign = false): string
    {
        $sign = $value < 0 ? '−' : ($withSign && $value > 0 ? '+' : '');

        return $sign . number_format(abs($value), 2, ',', ' ') . ' €';
    }
}
