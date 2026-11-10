<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Entrega (ou devolução) de uma peça ou ferramenta a um técnico. Só registo, não é stock. */
class ToolDelivery extends Model
{
    public const TIPOS = [
        'entrega' => ['label' => 'Entrega', 'icon' => 'fa-arrow-right', 'color' => '#0369a1', 'bg' => '#e0f2fe'],
        'devolucao' => ['label' => 'Devolução', 'icon' => 'fa-undo', 'color' => '#15803d', 'bg' => '#dcfce7'],
    ];

    protected $fillable = [
        'technician_id', 'tipo', 'artigo', 'codigo', 'item_id', 'quantidade', 'ocorrido_em', 'observacoes',
        'proalpha_em', 'proalpha_por', 'created_by',
    ];

    protected $casts = ['ocorrido_em' => 'datetime', 'proalpha_em' => 'datetime'];

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Quem passou o movimento no ProAlpha. */
    public function proalphaUser()
    {
        return $this->belongsTo(User::class, 'proalpha_por');
    }

    /** Já foi passado no ProAlpha? */
    public function isProalpha(): bool
    {
        return $this->proalpha_em !== null;
    }

    /** Ainda por lançar no ProAlpha. */
    public function scopePorLancar($query)
    {
        return $query->whereNull('proalpha_em');
    }

    public function scopeLancadas($query)
    {
        return $query->whereNotNull('proalpha_em');
    }

    public function tipoConfig(): array
    {
        return self::TIPOS[$this->tipo] ?? self::TIPOS['entrega'];
    }

    public function tipoLabel(): string
    {
        return __($this->tipoConfig()['label']);
    }

    /** "699015800 — Sensor", ou só o nome se o artigo não tiver código. */
    public function artigoCompleto(): string
    {
        return $this->codigo ? $this->codigo . ' — ' . $this->artigo : $this->artigo;
    }

    public function isEntrega(): bool
    {
        return $this->tipo !== 'devolucao';
    }
}
