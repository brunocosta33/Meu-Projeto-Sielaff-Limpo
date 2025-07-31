<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FlyerProduct_Campaign extends Pivot
{
    protected $table = 'flyersproduct_campaign';
    use HasFactory;
   
}

