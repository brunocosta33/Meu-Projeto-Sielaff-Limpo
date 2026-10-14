<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrVacation extends Model
{
    public const STATES = [
        'pendente' => 'Pendente',
        'aprovado' => 'Aprovado',
        'rejeitado' => 'Rejeitado',
    ];

    protected $fillable = [
        'hr_staff_id',
        'data_inicio',
        'data_fim',
        'dias_uteis',
        'estado',
        'observacoes',
        'motivo_rejeicao',
        'created_by',
        'decided_by',
        'decided_at',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
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

    public function scopeApproved($query)
    {
        return $query->where('estado', 'aprovado');
    }

    /** Pendentes e aprovadas (as que ocupam dias). */
    public function scopeActive($query)
    {
        return $query->whereIn('estado', ['pendente', 'aprovado']);
    }

    public function scopeOverlapping($query, $from, $to)
    {
        return $query->whereDate('data_inicio', '<=', $to)->whereDate('data_fim', '>=', $from);
    }
}
