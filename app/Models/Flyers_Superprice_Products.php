<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Flyers_Superprice_Products extends Pivot
{
    protected $table = 'flyers_superprice_products';
    use HasFactory;
}
