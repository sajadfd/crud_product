<?php

namespace Database\Seeders;

use App\Models\CategoryParent;
use Illuminate\Database\Seeder;

class CategoryParentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CategoryParent::factory()->count(30)->create();
    }
}
