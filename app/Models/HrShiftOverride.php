<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrShiftOverride extends Model
{
    protected $fillable = [
        'data',
        'hr_staff_id',
        'substitute_user_id',
        'hora_inicio',
        'hora_fim',
        'motivo',
        'observacoes',
        'created_by',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(HrStaff::class, 'hr_staff_id');
    }

    public function substitute()
    {
        return $this->belongsTo(User::class, 'substitute_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
