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

    public function productCategories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function productPositions()
    {
        return $this->hasMany(ProductPosition::class);
    }
    // public function categories()
    // {
    //     return $this->belongsToMany(Category::class, 'product_categories', 'product_id', 'category_id')
    //         ->withPivot('parent_category_id', 'category_path');
    // }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }
}
