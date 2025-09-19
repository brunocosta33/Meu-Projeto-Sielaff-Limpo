<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TechnicalRequestMachine extends Model
{
    protected $fillable = ['technical_request_id','machine_id','user_id','observacoes'];

    public function request()
    {
        return $this->belongsTo(TechnicalRequest::class, 'technical_request_id');
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class, 'machine_id');
    }

    public function parts()
    {
        return $this->hasMany(TechnicalRequestMachinePart::class, 'tr_machine_id');
    }
}
