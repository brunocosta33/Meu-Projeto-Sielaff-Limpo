<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Store;

class TechnicalRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'machine_id',
        'origem',
        'tipo_servico',
        'descricao_problema',
        'prioridade',
        'estado',
        'observacoes',
        'data_pedido',
        'data_resolucao',
        'data_agendamento', 
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    public function machines()
    {
        return $this->hasMany(TechnicalRequestMachine::class, 'technical_request_id');
    }
}
