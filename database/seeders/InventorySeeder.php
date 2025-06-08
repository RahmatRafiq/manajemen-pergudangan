<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Faker\Factory as Faker;

class InventorySeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $warehouses = Warehouse::pluck('id')->toArray();
        $products   = Product::pluck('id')->toArray();

        $usedCombinations = [];

        // Looping sebanyak jumlah yang diinginkan
        for ($i = 0; $i < 100; $i++) {
            $warehouse_id = $faker->randomElement($warehouses);
            $product_id   = $faker->randomElement($products);
            $key          = $warehouse_id . '-' . $product_id;

            // Cek apakah kombinasi ini sudah ada
            if (in_array($key, $usedCombinations)) {
                continue; // lewati dan coba kombinasi baru
            }

            $usedCombinations[] = $key;

            Inventory::create([
                'warehouse_id' => $warehouse_id,
                'product_id'   => $product_id,
                'quantity'     => $faker->numberBetween(50, 500),
                'reserved'     => $faker->numberBetween(0, 100),
                'min_stock'    => $faker->numberBetween(20, 50),
                'max_stock'    => $faker->numberBetween(200, 600),
                'updated_by'   => $faker->numberBetween(1, 100), // pastikan user id valid
            ]);
        }
    }
}
