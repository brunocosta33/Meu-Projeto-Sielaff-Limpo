<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Uma resposta de quem está a tratar da tarefa. */
class BoardTaskComment extends Model
{
    protected $fillable = ['board_task_id', 'user_id', 'tipo', 'texto'];

    /** Uma nota de passagem é escrita pelo sistema, não por alguém a comentar. */
    public function isPassagem(): bool
    {
        return $this->tipo === 'passagem';
    }

    public function task()
    {
        return $this->belongsTo(BoardTask::class, 'board_task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
