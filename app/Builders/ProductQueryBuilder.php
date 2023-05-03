<?php

namespace App\Builders;

use App\Models\Product;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class ProductQueryBuilder extends QueryBuilder
{
    public function __construct()
    {
        $query = Product::query();
        parent::__construct($query);
        $this->allowedFilters([
            AllowedFilter::exact('brand_id'),
            AllowedFilter::scope('category_id'),
            AllowedFilter::scope('price'),
            AllowedFilter::scope('size'),
        ]);
        $this->allowedSorts([
            AllowedSort::field('title'),
            AllowedSort::field('price'),
        ]);
        $this->allowedIncludes([
            AllowedInclude::relationship('brand'),
            AllowedInclude::relationship('categories'),
        ]);
    }
    public function size($query, $value)
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
        return $query->where('id',$searchBy);
    }
}
