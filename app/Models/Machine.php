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

    /**
     * Assistências técnicas associadas a esta máquina (o que foi feito).
     */
    public function technicalRequests()
    {
        return $this->hasMany(TechnicalRequest::class, 'machine_id');
    }

    /**
     * Movimentos de stock ligados a esta máquina.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'machine_id');
    }

    /**
     * Apenas os consumos de peças nesta máquina (o que foi gasto).
     */
    public function consumptions()
    {
        return $this->hasMany(StockMovement::class, 'machine_id')
            ->where('movement_type', 'consumed');
    }

    /**
     * Etiqueta legível: nº de série + loja.
     */
    public function label(): string
    {
        $store = $this->store;
        $storeLabel = $store
            ? trim(($store->codigo_loja ? $store->codigo_loja . ' - ' : '') . $store->nome_loja)
            : null;

        return $storeLabel
            ? $this->serial_number . ' (' . $storeLabel . ')'
            : (string) $this->serial_number;
    }
}
