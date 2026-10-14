<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrAbsence extends Model
{
    public const STATES = [
        'aguarda' => ['label' => 'A aguardar justificação', 'color' => '#f9a825', 'bg' => '#fff4cc', 'text' => '#7a5600', 'priority' => 2],
        'justificada' => ['label' => 'Justificada', 'color' => '#2e7d32', 'bg' => '#dff0e0', 'text' => '#1b5e20', 'priority' => 1],
        'injustificada' => ['label' => 'Injustificada', 'color' => '#c62828', 'bg' => '#fbdcdc', 'text' => '#a31515', 'priority' => 3],
    ];

    protected $fillable = [
        'hr_staff_id',
        'data_inicio',
        'data_fim',
        'hora_inicio',
        'hora_fim',
        'dias_uteis',
        'horas',
        'motivo',
        'situacao',
        'observacoes',
        'documento_path',
        'documento_nome',
        'created_by',
        'decided_by',
        'decided_at',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'horas' => 'float',
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

    public function scopeOverlapping($query, $from, $to)
    {
        return $query->whereDate('data_inicio', '<=', $to)->whereDate('data_fim', '>=', $from);
    }

    public function isPartial(): bool
    {
        return $this->hora_inicio !== null;
    }

    public function getMotivoLabelAttribute(): string
    {
        return config('hr.absence_reasons.' . $this->motivo, $this->motivo);
    }

    public function getSituacaoLabelAttribute(): string
    {
        return self::STATES[$this->situacao]['label'] ?? $this->situacao;
    }

    public function getSituacaoColorAttribute(): string
    {
        return self::STATES[$this->situacao]['color'] ?? '#6c757d';
    }

    /** Estilo de etiqueta legível: fundo claro, texto escuro e contorno na cor da situação. */
    public static function badgeStyle(string $situacao): string
    {
        $s = self::STATES[$situacao] ?? ['bg' => '#eee', 'text' => '#333', 'color' => '#999'];

        return "background: {$s['bg']}; color: {$s['text']}; border: 1px solid {$s['color']}; font-weight: 600;";
    }

    /** Texto curto da duração: "3 dias úteis" ou "09:00–11:00 (2 h)". */
    public function getDuracaoAttribute(): string
    {
        if ($this->isPartial()) {
            return substr($this->hora_inicio, 0, 5) . '–' . substr($this->hora_fim, 0, 5)
                . ' (' . rtrim(rtrim(number_format($this->horas, 2, ',', ''), '0'), ',') . ' h)';
        }

        return $this->dias_uteis . ' ' . ($this->dias_uteis === 1 ? 'dia útil' : 'dias úteis');
    }
}
