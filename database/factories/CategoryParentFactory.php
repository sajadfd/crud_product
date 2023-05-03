<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryParent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoryParent>
 */
class CategoryParentFactory extends Factory
{
    protected $model = CategoryParent::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => Category::factory(),
            'category_id' => Category::factory(),
        ];
    }
}
