<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Flyers_FlyerBanner extends Pivot
{
    protected $table = 'flyers_flyerbanner';
    use HasFactory;
   
}
