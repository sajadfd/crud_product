<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'slug', 'sku', 'brand_id'];
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }
    public function positions()
    {
        return $this->hasMany(ProductPosition::class);
    }
    // for deleting all related products in another tables
    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($product) {
            $product->categories()->detach();
            $product->positions()->delete();
        });
    }
}
