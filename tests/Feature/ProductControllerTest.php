<?php

namespace Tests\Feature;

use App\Http\Controllers\ProductController;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Database\Factories\ProductFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_new_product()
    {
        $data = [
            'title' => 'New Product',
            'sku' => 'NP0011',
            'slug' => 'new_product_url11',
            'brand_id' => 1,
            'categories' => ['2.11.13', '7.11', '12'],
            'positions' => [
                ['size' => 'Q', 'price' => 11.99],
                ['size' => 'T', 'price' => 9.99],
            ],
        ];
    
        $response = $this->postJson('/api/products', $data);
    
        $response->assertStatus(201);
        $response->assertJson(['message' => 'Product created successfully']);
    }

    public function test_can_retrieve_a_product()
    {
        $product = Product::factory()->create();
    
        $response = $this->getJson('/api/products/' . $product->id);
    
        $response->assertOk();
        $response->assertJson(['title' => $product->title]);
    }

    public function test_can_update_a_product()
    {
        $product = Product::factory()->create();
    
        $data = [
            'title' => 'Updated Product Title',
            'sku' => 'UPD001',
            'slug' => 'updated_product_url',
            'brand_id' => 2,
            'categories' => ['1.2.5', '8'],
            'positions' => [
                ['size' => 'Q', 'price' => 12.99],
                ['size' => 'T', 'price' => 10.99],
            ],
        ];
    
        $response = $this->putJson('/api/products/' . $product->id, $data);
    
        $response->assertOk();
        $response->assertJson(['message' => 'Product updated successfully']);
    }
    public function test_can_delete_a_product()
    {
        $product = ProductFactory::new()->create();

        $request = new ProductRequest(['id' => $product->id]);
        $controller = new ProductController(new ProductRepository(new Product()));
        $response = $controller->destroy($request);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Product deleted successfully']);
        $this->assertNull(Product::find($product->id));
    }
}
