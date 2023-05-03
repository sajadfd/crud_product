<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPosition extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'size', 'price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
