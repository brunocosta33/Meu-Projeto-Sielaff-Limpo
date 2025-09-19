<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'quantidade',
        'origem_id',
        'destino_id',
        'tipo_movimento',
        'observacoes',
        'user_id',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function origem()
    {
        return $this->belongsTo(Location::class, 'origem_id');
    }

    public function destino()
    {
        return $this->belongsTo(Location::class, 'destino_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
