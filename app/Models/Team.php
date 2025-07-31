<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Appointment;
use App\Models\User;

class Team extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome',
        'contacto',
        'email',
        'observacoes'
    ];

    // Relação com os agendamentos (appointments)
  public function appointments()
    {
        return $this->belongsToMany(Appointment::class, 'appointment_tecnica_team')->withTimestamps();
    }
   public function users()
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id');
    }


}
