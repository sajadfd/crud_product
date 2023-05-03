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
            'sku' => $this->faker->unique()->numberBetween(1000, 9999),
            'slug' => $this->faker->slug,
            'brand_id' => $this->faker->numberBetween(1, 10),

        ];
    }
}
