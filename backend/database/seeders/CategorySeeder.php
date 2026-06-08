<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Siomai',              'icon' => '🥟', 'sort_order' => 1],
            ['name' => 'Everyday Meals',      'icon' => '🍳', 'sort_order' => 2],
            ['name' => 'Boneless Chicken',    'icon' => '🍗', 'sort_order' => 3],
            ['name' => 'Chicken Wings',       'icon' => '🔥', 'sort_order' => 4],
            ['name' => 'Whole Chicken',       'icon' => '🐔', 'sort_order' => 5],
            ['name' => 'Crispy Liempo',       'icon' => '🥩', 'sort_order' => 6],
            ['name' => 'French Fries',        'icon' => '🍟', 'sort_order' => 7],
            ['name' => 'Beverages',           'icon' => '🥤', 'sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            Category::create(array_merge($cat, ['is_active' => true]));
        }
    }
}
