<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlyerBanner extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'flyer_banners';

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function flyer()
    {
        return $this->belongsToMany(Flyer::class, 'flyers_flyerbanner', 'flyerbanner_id', 'flyer_id')
                    ->using(Flyers_FlyerBanner::class);
    }

   
}
