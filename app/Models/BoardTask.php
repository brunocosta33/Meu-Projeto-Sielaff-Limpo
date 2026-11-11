<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Um cartão do Quadro: uma coisa dada a alguém para fazer. */
class BoardTask extends Model
{
    public const ESTADOS = [
        'por_fazer' => ['label' => 'Por fazer', 'icon' => 'fa-inbox', 'color' => '#475569', 'bg' => '#f1f5f9'],
        'em_curso' => ['label' => 'Em curso', 'icon' => 'fa-running', 'color' => '#b45309', 'bg' => '#fff7ed'],
        'feito' => ['label' => 'Feito', 'icon' => 'fa-check', 'color' => '#15803d', 'bg' => '#dcfce7'],
    ];

    protected $fillable = [
        'titulo', 'descricao', 'estado', 'assigned_to', 'prazo',
        'iniciado_em', 'iniciado_por', 'concluido_em', 'concluido_por', 'created_by',
    ];

    protected $casts = [
        'prazo' => 'date',
        'iniciado_em' => 'datetime',
        'concluido_em' => 'datetime',
    ];

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function iniciador()
    {
        return $this->belongsTo(User::class, 'iniciado_por');
    }

    public function concluidor()
    {
        return $this->belongsTo(User::class, 'concluido_por');
    }

    public function comments()
    {
        return $this->hasMany(BoardTaskComment::class)->with('user')->orderBy('id');
    }

    public function estadoConfig(): array
    {
        return self::ESTADOS[$this->estado] ?? self::ESTADOS['por_fazer'];
    }

    public function isFeito(): bool
    {
        return $this->estado === 'feito';
    }

    /** Já passou do prazo e ainda não está feita. */
    public function atrasada(): bool
    {
        return $this->prazo !== null && !$this->isFeito() && $this->prazo->isPast() && !$this->prazo->isToday();
    }

    public function paraHoje(): bool
    {
        return $this->prazo !== null && !$this->isFeito() && $this->prazo->isToday();
    }

    /** Iniciais para a bolinha do cartão. */
    public function iniciais(): string
    {
        $nome = trim((string) ($this->responsavel->name ?? ''));
        if ($nome === '') {
            return '?';
        }

        $partes = preg_split('/\s+/', $nome);

        return mb_strtoupper(mb_substr($partes[0], 0, 1) . (count($partes) > 1 ? mb_substr(end($partes), 0, 1) : ''));
    }

    /** Cor estável por pessoa, para se reconhecer o cartão de longe. */
    public function corDoResponsavel(): string
    {
        $cores = ['#0f4c81', '#b91c1c', '#15803d', '#6d28d9', '#b45309', '#0e7490', '#be185d', '#4d7c0f'];

        return $cores[($this->assigned_to ?? 0) % count($cores)];
    }
}
