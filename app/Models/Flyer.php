<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flyer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'flyers';

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function flyerBanners()
    {
        return $this->belongsToMany(FlyerBanner::class, 'flyers_flyerbanner', 'flyer_id', 'flyerbanner_id');
    }

    public function campaignProducts()
    {
        return $this->belongsToMany(Product::class, 'flyersproduct_campaign', 'flyer_id', 'product_id')
                    ->using(FlyerProduct_Campaign::class);
    }

    public function superPriceProducts()
    {
        return $this->belongsToMany(Product::class, 'flyers_superprice_products', 'flyer_id', 'product_id');
    }


}
