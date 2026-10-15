<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreMaintenanceRecord extends Model
{
    protected $fillable = [
        'store_maintenance_id',
        'ano',
        'data_realizada',
        'user_id',
        'observacoes',
        'documento_path',
        'documento_nome',
        'created_by',
    ];

    protected $casts = [
        'data_realizada' => 'date',
    ];

    public function maintenance()
    {
        return $this->belongsTo(StoreMaintenance::class, 'store_maintenance_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
