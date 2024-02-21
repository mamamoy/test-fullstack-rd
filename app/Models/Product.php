<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model
{
    use HasFactory;
    use HasSlug;

    protected $guarded = ['id'];
    protected $appends = ['stock_qty', 'in_stock'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('product_name')
            ->saveSlugsTo('slug');
    }

    public function stock(){
        return $this->hasOne(Stock::class, 'product_id');
    }

    public function getStockQtyAttribute()
    {
        return $this->stock ? $this->stock->qty : 0;
    }
    public function getInStockAttribute()
    {
        return $this->attributes['inStock'] ? 'Stock satisfied' : 'Need more stock';
    }
}
