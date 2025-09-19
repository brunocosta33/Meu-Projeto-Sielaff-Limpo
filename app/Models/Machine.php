<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    protected $fillable = [
        'store_id',
        'serial_number',
        'ip_address',   // 🔹 novo campo
        'descricao',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
