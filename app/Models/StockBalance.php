<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'location_id',
        'quantidade',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
