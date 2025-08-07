<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentFile extends Model
{
    protected $fillable = [
        'appointment_id',
        'file_path',
        'file_name',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
