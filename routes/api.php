<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

//TODO: use this example for testing purposes
// products
Route::post('/products', [ProductController::class, 'store']);
// products/1  
Route::put('/products/{id}', [ProductController::class, 'update']);
// products/destroy?id=1
Route::delete('/products/{product}', [ProductController::class, 'destroy']);
// products/1
Route::get('/products/{product}', [ProductController::class, 'show']);
// products?filter[size]=S,M&search=NP001&searchBy=sku
Route::get('/products', [ProductController::class, 'index']);



// json for testing post and put requests

// {
//     "title": "New Product",
//     "sku": "NP002",
//     "slug": "new_product_url3",
//     "brand_id": "1",
//     "categories": ["2.3.13", "7.22"],
//     "positions": [
//         {
//             "price": 11.99,
//             "size": "M"
//         },
//         {
//             "price": 9.99,
//             "size": "L"
//         }
//     ]
// }