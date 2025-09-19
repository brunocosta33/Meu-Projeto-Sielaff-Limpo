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
        'quantidade_total',
    ];

    public function stockBalances()
    {
        return $this->hasMany(StockBalance::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
