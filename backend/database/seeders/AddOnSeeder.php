<?php

namespace Database\Seeders;

use App\Models\AddOn;
use Illuminate\Database\Seeder;

class AddOnSeeder extends Seeder
{
    public function run(): void
    {
        $addOns = [
            // Rice & sides
            ['name' => 'Java Rice',           'price' => 20.00],
            ['name' => 'Plain Rice',          'price' => 15.00],
            ['name' => 'Egg',                 'price' => 15.00],

            // Siomai upgrade
            ['name' => 'Fried (upgrade)',     'price' => 5.00],

            // Chicken & wings flavors (₱0 — used as flavor selector)
            ['name' => 'Honey BBQ',           'price' => 0.00],
            ['name' => 'Garlic Butter',       'price' => 0.00],
            ['name' => 'Japanese BBQ',        'price' => 0.00],
            ['name' => 'Spicy BBQ',           'price' => 0.00],
            ['name' => 'Yangnyeom',           'price' => 0.00],
            ['name' => 'Fireball Buffalo',    'price' => 0.00],

            // Fries flavors (₱0 — used as flavor selector)
            ['name' => 'Cheese Flavor',       'price' => 0.00],
            ['name' => 'BBQ Flavor',          'price' => 0.00],
            ['name' => 'Sour Cream Flavor',   'price' => 0.00],
        ];

        foreach ($addOns as $addOn) {
            AddOn::create(array_merge($addOn, ['is_active' => true]));
        }
    }
}
