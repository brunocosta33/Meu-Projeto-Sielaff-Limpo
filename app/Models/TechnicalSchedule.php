<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicalSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'technical_request_id', 'equipa_id', 'data', 'hora', 'local', 'estado', 'notas'
    ];

    public function request()
    {
        return $this->belongsTo(TechnicalRequest::class, 'technical_request_id');
    }
}

