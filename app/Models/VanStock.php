<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VanStock extends Model
{
    protected $fillable = ['technician_id', 'part_id', 'quantity', 'type'];

  
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

}