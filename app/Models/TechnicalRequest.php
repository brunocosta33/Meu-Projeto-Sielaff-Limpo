<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Store;
use App\Models\Machine;
use App\Models\User;
use App\Models\TechnicalRequestMachine;
use App\Models\TechnicalRequestFile;

class TechnicalRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'machine_id',
        'created_by',
        'updated_by',
        'assigned_technician_id',
        'origem',
        'tipo_servico',
        'zona',
        'descricao_problema',
        'prioridade',
        'estado',
        'observacoes',
        'data_pedido',
        'data_resolucao',
        'data_agendamento', 
    ];

    protected $casts = [
        'data_pedido' => 'date',
        'data_resolucao' => 'datetime',
        'data_agendamento' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function assignedPersonLabel(): string
    {
        if (!$this->assignedTechnician) {
            return __('Por atribuir');
        }

        return $this->assignedTechnician->name ?: $this->assignedTechnician->email;
    }

    public function assignedPersonTypeLabel(): string
    {
        if (!$this->assignedTechnician) {
            return __('Responsável');
        }

        return $this->assignedTechnician->hasRole('admin')
            || $this->assignedTechnician->hasRole('administrator')
            || $this->assignedTechnician->hasRole('administrador')
                ? __('Pessoa')
                : __('Técnico');
    }

    public function machines()
    {
        return $this->hasMany(TechnicalRequestMachine::class, 'technical_request_id');
    }

    public function files()
    {
        return $this->hasMany(TechnicalRequestFile::class);
    }
}
