<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use Faker\Factory as Faker;

class InventorySeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $warehouses = Warehouse::pluck('id')->toArray();
        $products   = Product::pluck('id')->toArray();
        $users      = User::pluck('id')->toArray();

        // Jika tidak ada data yang diperlukan, skip seeder
        if (empty($warehouses) || empty($products) || empty($users)) {
            $this->command->warn('Skipping InventorySeeder: Missing required data (warehouses, products, or users)');
            return;
        }

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
                'reserved'     => $faker->numberBetween(0, 50), // reduced from 100
                'min_stock'    => $faker->numberBetween(20, 50),
                'max_stock'    => $faker->numberBetween(200, 600),
                'updated_by'   => $faker->randomElement($users), // use actual user IDs
            ]);
        }

        $this->command->info('InventorySeeder completed successfully');
    }
}
