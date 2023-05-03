<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Faker\faker;

beforeEach(function () {
    $this->seed();
});


it('can create a new product', function () {
    $data = [
        'title' => 'New Product',
        'sku' => 'NP0011',
        'slug' => 'new_product_url11',
        'brand_id' => 1,
        'categories' => [
            '2.11.13',
            '7.11',
            '12'
        ],
        'positions' => [
            [
                'price' => 11.99,
                'size' => 'Q'
            ],
            [
                'price' => 9.99,
                'size' => 'T'
            ]
        ]
    ];

    $response = $this->postJson('/api/products', $data);

    $response->assertStatus(201)
        ->assertJsonFragment(['title' => $data['title']])
        ->assertJsonFragment(['sku' => $data['sku']])
        ->assertJsonFragment(['slug' => $data['slug']])
        ->assertJsonFragment(['brand_id' => $data['brand_id']])
        ->assertJsonFragment(['categories' => $data['categories']])
        ->assertJsonFragment(['positions' => $data['positions']]);
});

it('can retrieve a product', function () {
    $product = Product::inRandomOrder()->first();

    $response = $this->getJson('/api/products/1');

    $response->assertOk()
        ->assertJsonFragment(['title' => $product->title])
        ->assertJsonFragment(['sku' => $product->sku])
        ->assertJsonFragment(['slug' => $product->slug])
        ->assertJsonFragment(['brand_id' => $product->brand_id])
        ->assertJsonFragment(['categories' => $product->categories->toArray()])
        ->assertJsonFragment(['positions' => $product->positions->toArray()]);
});

it('can update a product', function () {
    $product = Product::inRandomOrder()->first();

    $data = [
        'title' => faker()->name(),
        'slug' => faker()->slug(),
        'brand_id' => $product->brand_id,
        'categories' => [
            '2.11.13',
            '7.11',
            '12'
        ],
        'positions' => [
            [
                'price' => 11.99,
                'size' => 'Q'
            ],
            [
                'price' => 9.99,
                'size' => 'T'
            ]
        ]
    ];

    $response = $this->putJson('/api/products/' . $product->id, $data);

    $response->assertOk()
        ->assertJsonFragment(['title' => $data['title']])
        ->assertJsonFragment(['slug' => $data['slug']])
        ->assertJsonFragment(['brand_id' => $data['brand_id']])
        ->assertJsonFragment(['categories' => $data['categories']])
        ->assertJsonFragment(['positions' => $data['positions']]);
});

it('can delete a product', function () {
    $product = Product::inRandomOrder()->first();

    $response = $this->deleteJson('/api/products/' . $product->id);

    $response->assertNoContent();

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});
