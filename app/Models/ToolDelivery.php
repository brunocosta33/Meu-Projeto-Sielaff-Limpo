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
        'technician_id', 'tipo', 'artigo', 'item_id', 'quantidade', 'ocorrido_em', 'observacoes', 'created_by',
    ];

    protected $casts = ['ocorrido_em' => 'datetime'];

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tipoConfig(): array
    {
        return self::TIPOS[$this->tipo] ?? self::TIPOS['entrega'];
    }

    public function tipoLabel(): string
    {
        return __($this->tipoConfig()['label']);
    }

    public function isEntrega(): bool
    {
        return $this->tipo !== 'devolucao';
    }
}
