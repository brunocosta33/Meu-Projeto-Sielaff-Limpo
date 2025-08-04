<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'description'];

    public function schedules()
    {
        return $this->hasMany(TaskSchedule::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'task_schedule_user');
    }
    
}
