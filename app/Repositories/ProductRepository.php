<?php

namespace App\Repositories;

use App\Models\Product;
use Spatie\QueryBuilder\QueryBuilder;

class ProductRepository
{
    public function getProducts(QueryBuilder $query)
    {
        $bindings = $query->getQuery()->getBindings();
        if (count($bindings) > 0) {
            $products = $query->paginate(10);
            if ($products->total() == 0) {
                return response()->json(null);
            }
            return response()->json($products);
        } else {
            return response()->json(null);
        }
    }

    public function createProduct(array $data)
    {
        return Product::create($data);
    }

    public function updateProduct(Product $product, array $data)
    {
        return $product->update($data);
    }

    public function deleteProduct(Product $product)
    {
        return $product->delete();
    }
}
