<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSchedule extends Model
{
    protected $fillable = [
        'task_id',
        'data_limite',
        'hora_limite',
        'prioridade',
        'activa',
        'grupo',
        'repete',
        'estado',
        'user_id', // responsável principal
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_schedule_user', 'task_schedule_id', 'user_id')
                    ->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id'); // responsável (pode ser usado em "minhas tarefas")
    }
}
