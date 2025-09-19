<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['nome', 'tipo', 'store_id'];

    // 🔹 Uma Location (cliente) pode estar associada a uma Store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // (opcional: se quiseres ver todos os saldos diretamente)
    public function stockBalances()
    {
        return $this->hasMany(StockBalance::class);
    }
}
