<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'referencia',
        'descricao',
        'quantidade',
        'store_id',
        'reservado',
        'type',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function reservations()
    {
        return $this->hasMany(PartReservation::class);
    }

}

