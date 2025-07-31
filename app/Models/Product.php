<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'products';

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function flyersCampaignProducts()
    {
        return $this->belongsToMany(Flyer::class, 'flyersproduct_campaign', 'product_id', 'flyer_id')
                    ->using(FlyerProduct_Campaign::class);
    }

    public function flyersSuperPriceProducts()
    {
        return $this->belongsToMany(Flyer::class, 'flyers_superprice_products', 'product_id', 'flyer_id');
    }

    public function brandProduct()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function categoryProduct()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}


