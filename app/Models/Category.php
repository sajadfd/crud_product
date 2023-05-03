<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // public function productCategories()
    // {
    //     return $this->hasMany(ProductCategory::class);
    // }

    // public function categoryParents()
    // {
    //     return $this->hasMany(CategoryParent::class);
    // }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }
}
