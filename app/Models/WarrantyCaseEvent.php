<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyCaseEvent extends Model
{
    protected $fillable = ['warranty_case_id', 'fase', 'data', 'nota', 'user_id'];

    protected $casts = ['data' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
