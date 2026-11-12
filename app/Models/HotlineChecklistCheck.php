<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A validação de uma tarefa da checklist, por uma pessoa, num dia. */
class HotlineChecklistCheck extends Model
{
    protected $fillable = ['item_id', 'user_id', 'data', 'estado', 'nota'];

    protected $casts = ['data' => 'date'];

    public function item()
    {
        return $this->belongsTo(HotlineChecklistItem::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isProblema(): bool
    {
        return $this->estado === 'problema';
    }

    /** A hora a que foi validada — é isso que se mostra na célula. */
    public function hora(): string
    {
        return $this->created_at?->format('H:i') ?? '';
    }
}
