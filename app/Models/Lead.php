<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }


    public function state()
    {
        return $this->belongsTo(State::class);
    }


    public function history()
    {
        return $this->hasMany(LeadHistory::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'relation_id');
    }

   

}
