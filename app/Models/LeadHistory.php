<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadHistory extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'leads_history';

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
