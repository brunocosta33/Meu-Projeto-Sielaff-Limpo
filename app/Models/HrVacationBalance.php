<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrVacationBalance extends Model
{
    protected $fillable = [
        'hr_staff_id',
        'ano',
        'dias_direito',
        'dias_transitados',
    ];

    public function staff()
    {
        return $this->belongsTo(HrStaff::class, 'hr_staff_id');
    }
}
