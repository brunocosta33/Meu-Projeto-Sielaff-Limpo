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
        'horas_propostas',
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
        'horas_propostas' => 'float',
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

    public function files()
    {
        return $this->hasMany(HrTimeEntryFile::class);
    }

    /** Pedidos à espera de decisão da gestão: pendentes ou com proposta de trabalho suplementar. */
    public function scopeAwaitingDecision($query)
    {
        return $query->where(fn ($q) => $q->where('estado', 'pendente')->orWhereNotNull('horas_propostas'));
    }

    /** Tem horas de trabalho suplementar propostas pelo técnico à espera de aprovação? */
    public function hasProposal(): bool
    {
        return $this->horas_propostas !== null;
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function getTypeAttribute(): array
    {
        return config('hr.time_entry_types.' . $this->tipo, ['label' => $this->tipo, 'sign' => 1, 'color' => '#6c757d', 'icon' => 'fa-clock']);
    }

    /** Horas com sinal (+ a favor do colaborador, − a favor da empresa / acerto). */
    public function getSignedAmountAttribute(): float
    {
        return $this->type['sign'] * $this->horas;
    }

    /** Formata horas decimais como "7h30". */
    public static function formatHours(float $hours, bool $withSign = false): string
    {
        $sign = $hours < 0 ? '−' : ($withSign && $hours > 0 ? '+' : '');
        $minutes = (int) round(abs($hours) * 60);

        return $sign . intdiv($minutes, 60) . 'h' . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT);
    }
}
