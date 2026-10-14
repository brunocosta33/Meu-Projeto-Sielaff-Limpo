<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrHoliday extends Model
{
    protected $fillable = [
        'data',
        'nome',
        'recorrente',
        'created_by',
    ];

    protected $casts = [
        'data' => 'date',
        'recorrente' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
