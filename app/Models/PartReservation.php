<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class PartReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'store_id',
        'quantity',
        'reserved_at',
        'notes',
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
