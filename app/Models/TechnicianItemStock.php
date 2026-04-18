<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianItemStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'technician_id',
        'item_id',
        'quantity',
    ];

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
