<?php

use App\Models\Product;
use Illuminate\Support\Facades\Log;

it('can create a product', function () {
    $data = [
        'title' => 'New Product',
        'sku' => 'NP001',
        'slug' => 'new_product_url1',
        'brand_id' => '1',
        'categories' => [
            '2.11.13',
            '7.11',
            '12',
        ],
        'positions' => [
            [
                'price' => 11.99,
                'size' => 'X',
            ],
            [
                'price' => 9.99,
                'size' => 'S',
            ],
        ],
    ];
    $response = $this->post('/api/products', $data);
    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Product created successfully',
        ]);
    $this->assertDatabaseHas('products', [
        'title' => 'New Product',
        'sku' => 'NP001',
        'slug' => 'new_product_url1',
        'brand_id' => '1',
    ]);
    $product = Product::where('slug', 'new_product_url1')->first();
    $this->assertNotNull($product);
    $this->assertDatabaseHas('product_categories', [
        'product_id' => $product->id,
        'category_id' => '13',
        'parent_category_id' => '11',
        'category_path' => '2.11.13',
    ]);

    $this->assertDatabaseHas('product_categories', [
        'product_id' => $product->id,
        'category_id' => '11',
        'parent_category_id' => '7',
        'category_path' => '7.11',
    ]);
    $this->assertDatabaseHas('product_categories', [
        'product_id' => $product->id,
        'category_id' => '12',
        'parent_category_id' => null,
        'category_path' => '12',
    ]);
    $this->assertDatabaseHas('product_positions', [
        'product_id' => $product->id,
        'price' => 11.99,
        'size' => 'X',
    ]);

    $this->assertDatabaseHas('product_positions', [
        'product_id' => $product->id,
        'price' => 9.99,
        'size' => 'S',
    ]);
});
it('can update a product', function () {
    $product = Product::factory()->create();
     $data = [
        'title' => 'New Product Title',
        'slug' => 'new_slug',
        'sku' => 'NP0012',
        'brand_id' => '2',
        'categories' => [
            '5.11.13',
            '7',
            '12',
        ],
        'positions' => [
            [
                'price' => 15.99,
                'size' => 'S',
            ],
            [
                'price' => 12.99,
                'size' => 'XL',
            ],
        ],
    ];
    $response = $this->put("/api/products/{$product->id}", $data);
    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Product updated successfully',
        ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'title' => 'New Product Title',
        'slug' => 'new_slug',
        'sku' => 'NP0012',
        'brand_id' => '2',
    ]);

    $this->assertDatabaseMissing('products', [
        'slug' => 'existing_product',
    ]);

    $this->assertDatabaseHas('product_categories', [
        'product_id' => $product->id,
        'category_id' => '13',
        'parent_category_id' => '11',
        'category_path' => '5.11.13',
    ]);

    $this->assertDatabaseHas('product_categories', [
        'product_id' => $product->id,
        'category_id' => '7',
        'parent_category_id' => null,
        'category_path' => '7',
    ]);

    $this->assertDatabaseHas('product_categories', [
        'product_id' => $product->id,
        'category_id' => '12',
        'parent_category_id' => null,
        'category_path' => '12',
    ]);

    $this->assertDatabaseHas('product_positions', [
        'product_id' => $product->id,
        'price' => 15.99,
        'size' => 'S',
    ]);

    $this->assertDatabaseHas('product_positions', [
        'product_id' => $product->id,
        'price' => 12.99,
        'size' => 'XL',
    ]);
});

it('can delete a product', function () {
    $product = Product::factory()->create();

    $response = $this->delete("/api/products/destroy?id={$product->id}");
    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Product deleted successfully.',
        ]);

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);

    $this->assertDatabaseMissing('product_categories', [
        'product_id' => $product->id,
    ]);

    $this->assertDatabaseMissing('product_positions', [
        'product_id' => $product->id,
    ]);
});
