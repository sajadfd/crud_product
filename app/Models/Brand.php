<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    protected $fillable = ['title'];
    public function products()
    {
        return $this->hasMany(Product::class);
    }    public function size($query, $value)
    {
        $sizes = explode(',', $value);
        return $query->whereHas('productPositions', function ($q) use ($sizes) {
            $q->whereIn('size', $sizes);
        });
    }
    public function search($query, $search)
    {
        return $query->where('like', '%' . $search . '%');
    }
    public function searchBy($query, $searchBy = 'id')
    {
        return $query->where($searchBy);
    }
}
