<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    public const TYPES = [
        'warehouse_in' => 'Entrada em armazém',
        'to_technician' => 'Enviado para técnico',
        'from_technician' => 'Devolvido ao armazém',
        'consumed' => 'Consumido',
        'adjustment' => 'Ajuste manual',
    ];

    protected $fillable = [
        'item_id',
        'part_id',
        'technician_id',
        'movement_type',
        'quantity',
        'source',
        'destination',
        'notes',
        'moved_at',
        'created_by',
    ];

    protected $casts = [
        'moved_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
