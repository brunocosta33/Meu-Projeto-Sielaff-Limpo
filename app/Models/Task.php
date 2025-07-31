<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'titulo',
        'descricao',
    ];

    public function schedules()
    {
        return $this->hasMany(TaskSchedule::class);
    }
}
