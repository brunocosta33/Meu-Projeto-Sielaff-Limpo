<?php
// Arquivo removido por rollback do controle individual de ocorrências
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskScheduleOccurrence extends Model
{
    protected $fillable = [
        'task_schedule_id',
        'user_id',
        'data_ocorrencia',
        'estado',
        'data_conclusao',
    ];

    protected $dates = [
        'data_ocorrencia',
        'data_conclusao',
    ];

    public function taskSchedule()
    {
        return $this->belongsTo(TaskSchedule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
