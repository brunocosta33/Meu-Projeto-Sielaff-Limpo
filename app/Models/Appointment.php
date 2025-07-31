<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
    'store_id',
    'supplier_id',
    'scheduled_date',
    'scheduled_time',
    'tipo_servico',
    'status',
    'observacoes',
    'logistica_team_id',
    'tecnica_team_id'
];

    public function store() {
        return $this->belongsTo(Store::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }


    public function team()
    {
        return $this->belongsTo(Team::class);
    }
    public function logisticaTeam() {
    return $this->belongsTo(Team::class, 'logistica_team_id');
    }

    public function tecnicaTeam() {
        return $this->belongsTo(Team::class, 'tecnica_team_id');
    }


}
