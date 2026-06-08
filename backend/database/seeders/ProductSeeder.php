<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\AddOn;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $siomai      = Category::where('name', 'Siomai')->first();
        $meals       = Category::where('name', 'Everyday Meals')->first();
        $boneless    = Category::where('name', 'Boneless Chicken')->first();
        $wings       = Category::where('name', 'Chicken Wings')->first();
        $wholeChx    = Category::where('name', 'Whole Chicken')->first();
        $liempo      = Category::where('name', 'Crispy Liempo')->first();
        $fries       = Category::where('name', 'French Fries')->first();
        $beverages   = Category::where('name', 'Beverages')->first();

        // Add-ons we'll assign per category
        $javaRice      = AddOn::where('name', 'Java Rice')->first();
        $plainRice     = AddOn::where('name', 'Plain Rice')->first();
        $egg           = AddOn::where('name', 'Egg')->first();
        $friedUpgrade  = AddOn::where('name', 'Fried (upgrade)')->first();
        $honeyBBQ      = AddOn::where('name', 'Honey BBQ')->first();
        $garlicButter  = AddOn::where('name', 'Garlic Butter')->first();
        $japaneseBBQ   = AddOn::where('name', 'Japanese BBQ')->first();
        $spicyBBQ      = AddOn::where('name', 'Spicy BBQ')->first();
        $yangnyeom     = AddOn::where('name', 'Yangnyeom')->first();
        $fireball      = AddOn::where('name', 'Fireball Buffalo')->first();
        $cheeseFlavor  = AddOn::where('name', 'Cheese Flavor')->first();
        $bbqFlavor     = AddOn::where('name', 'BBQ Flavor')->first();
        $sourCream     = AddOn::where('name', 'Sour Cream Flavor')->first();

        $flavorAddOns  = [$honeyBBQ->id, $garlicButter->id, $japaneseBBQ->id, $spicyBBQ->id, $yangnyeom->id, $fireball->id];
        $riceEggAddOns = [$javaRice->id, $plainRice->id, $egg->id];
        $friesAddOns   = [$cheeseFlavor->id, $bbqFlavor->id, $sourCream->id];

        $products = [
            // ── Siomai (steam ₱50 base; Fried upgrade +₱5) ──────────────────
            ['category' => $siomai, 'name' => 'Pork Siomai',          'price' => 50, 'stock' => 100, 'addons' => [$friedUpgrade->id]],
            ['category' => $siomai, 'name' => 'Beef Siomai',          'price' => 50, 'stock' => 100, 'addons' => [$friedUpgrade->id]],
            ['category' => $siomai, 'name' => 'Chicken Siomai',       'price' => 50, 'stock' => 100, 'addons' => [$friedUpgrade->id]],
            ['category' => $siomai, 'name' => 'Dynamite Siomai',      'price' => 50, 'stock' => 80,  'addons' => [$friedUpgrade->id]],
            ['category' => $siomai, 'name' => 'Japanese Siomai',      'price' => 50, 'stock' => 80,  'addons' => [$friedUpgrade->id]],
            ['category' => $siomai, 'name' => 'Sharksfin Siomai',     'price' => 50, 'stock' => 60,  'addons' => []],
            ['category' => $siomai, 'name' => 'Chinese Kikiam',       'price' => 50, 'stock' => 80,  'addons' => []],

            // ── Everyday Meals (served with Java Rice & Egg) ─────────────────
            ['category' => $meals, 'name' => 'Beef Tapa',             'price' => 95, 'stock' => 40, 'addons' => $riceEggAddOns],
            ['category' => $meals, 'name' => 'Pork Tocino',           'price' => 95, 'stock' => 40, 'addons' => $riceEggAddOns],
            ['category' => $meals, 'name' => 'Pork Sisig',            'price' => 95, 'stock' => 40, 'addons' => $riceEggAddOns],
            ['category' => $meals, 'name' => 'Corned Beef',           'price' => 95, 'stock' => 40, 'addons' => $riceEggAddOns],
            ['category' => $meals, 'name' => 'Cheezy Hungarian',      'price' => 85, 'stock' => 40, 'addons' => $riceEggAddOns],
            ['category' => $meals, 'name' => 'Breakfast Longganisa',  'price' => 85, 'stock' => 40, 'addons' => $riceEggAddOns],
            ['category' => $meals, 'name' => 'Pork Ham',              'price' => 85, 'stock' => 40, 'addons' => $riceEggAddOns],
            ['category' => $meals, 'name' => 'Crispy Bacon',          'price' => 85, 'stock' => 40, 'addons' => $riceEggAddOns],
            ['category' => $meals, 'name' => 'Hotdog Bacon Wrap',     'price' => 85, 'stock' => 40, 'addons' => $riceEggAddOns],

            // ── Boneless Chicken sets (choose flavors as add-ons) ────────────
            ['category' => $boneless, 'name' => 'Solo Sulit Meal',        'price' => 95,  'stock' => 50, 'addons' => $flavorAddOns, 'desc' => '2 pcs, 1 flavor'],
            ['category' => $boneless, 'name' => 'Ka-Share Set',           'price' => 170, 'stock' => 40, 'addons' => $flavorAddOns, 'desc' => '4 pcs, 1 flavor'],
            ['category' => $boneless, 'name' => 'Small Barkada Combo',    'price' => 250, 'stock' => 30, 'addons' => $flavorAddOns, 'desc' => '6 pcs, 2 flavors'],
            ['category' => $boneless, 'name' => 'Solid Barkada Set',      'price' => 470, 'stock' => 20, 'addons' => $flavorAddOns, 'desc' => '12 pcs, 3 flavors'],

            // ── Chicken Wings sets ────────────────────────────────────────────
            ['category' => $wings, 'name' => 'Quick Bite Meal',       'price' => 85,  'stock' => 50, 'addons' => $flavorAddOns, 'desc' => '2 pcs, 1 flavor'],
            ['category' => $wings, 'name' => "Share Ko'to Set",       'price' => 160, 'stock' => 40, 'addons' => $flavorAddOns, 'desc' => '4 pcs, 2 flavors'],
            ['category' => $wings, 'name' => 'Chickahan Combo',       'price' => 240, 'stock' => 30, 'addons' => $flavorAddOns, 'desc' => '6 pcs, 2 flavors'],
            ['category' => $wings, 'name' => 'Barkada Wings',         'price' => 470, 'stock' => 20, 'addons' => $flavorAddOns, 'desc' => '12 pcs, 3 flavors'],

            // ── Whole Chicken & Liempo ────────────────────────────────────────
            ['category' => $wholeChx, 'name' => 'Fried Whole Chicken',  'price' => 220, 'stock' => 20, 'addons' => $riceEggAddOns],
            ['category' => $liempo,   'name' => 'Crispy Liempo',         'price' => 120, 'stock' => 30, 'addons' => $riceEggAddOns],

            // ── French Fries ──────────────────────────────────────────────────
            ['category' => $fries, 'name' => 'Small Fries',           'price' => 35, 'stock' => 100, 'addons' => $friesAddOns],
            ['category' => $fries, 'name' => 'Large Fries',           'price' => 55, 'stock' => 100, 'addons' => $friesAddOns],

            // ── Beverages ─────────────────────────────────────────────────────
            ['category' => $beverages, 'name' => 'Coke',                   'price' => 20, 'stock' => 200, 'addons' => []],
            ['category' => $beverages, 'name' => 'Sprite',                 'price' => 20, 'stock' => 200, 'addons' => []],
            ['category' => $beverages, 'name' => 'Royal',                  'price' => 20, 'stock' => 200, 'addons' => []],
            ['category' => $beverages, 'name' => 'Mountain Dew',           'price' => 20, 'stock' => 200, 'addons' => []],
            ['category' => $beverages, 'name' => 'Cobra',                  'price' => 20, 'stock' => 100, 'addons' => []],
            ['category' => $beverages, 'name' => 'Sting',                  'price' => 20, 'stock' => 100, 'addons' => []],
            ['category' => $beverages, 'name' => 'Bottled Water (500mL)',   'price' => 15, 'stock' => 200, 'addons' => []],
            ['category' => $beverages, 'name' => 'Bottled Water (1 Liter)', 'price' => 25, 'stock' => 100, 'addons' => []],
        ];

        foreach ($products as $data) {
            $product = Product::create([
                'category_id'         => $data['category']->id,
                'name'                => $data['name'],
                'description'         => $data['desc'] ?? null,
                'price'               => $data['price'],
                'stock_qty'           => $data['stock'],
                'low_stock_threshold' => max(5, (int)($data['stock'] * 0.15)),
                'is_available'        => true,
                'is_active'           => true,
            ]);

            if (!empty($data['addons'])) {
                $product->addOns()->attach($data['addons']);
            }
        }
    }
}
