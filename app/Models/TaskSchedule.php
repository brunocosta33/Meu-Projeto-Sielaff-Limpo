<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Task;
use App\Models\User;


class TaskSchedule extends Model
{
    protected $fillable = [
        'task_id',
        'data_limite',
        'hora_limite',
        'prioridade',
        'activa',
        'grupo',
        'repetir',
        'estado',
        'user_id',
        'description', // ← Este campo tem de estar aqui!
    ];


    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('estado', 'comentarios', 'data_conclusao')
            ->withTimestamps();
    }

    // App\Models\TaskSchedule.php

    protected $casts = [
        'data_limite' => 'date',
        'hora_limite' => 'datetime:H:i', // apenas se quiseres hora como objeto Carbon também
    ];




}
