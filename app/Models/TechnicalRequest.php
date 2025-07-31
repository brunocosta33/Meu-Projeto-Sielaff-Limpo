<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Store;

class TechnicalRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id', 'origem', 'descricao_problema', 'prioridade', 'estado', 'observacoes', 'data_pedido'
    ];
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

}