<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'sku' => $this->faker->unique()->isbn10,
            'slug' => $this->faker->slug,
            'brand_id' => 1,
            'categories' => ['2.11.13', '7.11', '12'],
            'positions' => [
                ['size' => 'Q', 'price' => 11.99],
                ['size' => 'T', 'price' => 9.99],
            ],
        ];
    }
}