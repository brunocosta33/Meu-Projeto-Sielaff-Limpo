<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Installation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'team_id',
        'scheduled_date',
        'scheduled_time',
        'tipo_servico',
        'status',
        'observacoes',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

     public function pdfs()
    {
        return $this->hasMany(InstallationPdf::class);
    }
}
