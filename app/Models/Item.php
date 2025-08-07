<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'referencia',
        'quantidade_atual',
        'tipo',
        'unidade_medida',
    ];

    public function movimentos()
    {
        return $this->hasMany(StockMovement::class);
    }
}
