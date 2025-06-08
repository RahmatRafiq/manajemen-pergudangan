<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $faker     = Faker::create();
        $categories = Category::pluck('id')->all();
        $users      = User::pluck('id')->all();

        // Buat 50 product
        for ($i = 0; $i < 50; $i++) {
            Product::create([
                'sku'         => strtoupper('SKU-' . $faker->bothify('???-####')),
                'name'        => ucfirst($faker->words(2, true)),
                'category_id' => $faker->randomElement($categories),
                'description' => $faker->paragraph(2),
                'unit'        => $faker->randomElement(['pcs', 'box', 'kg', 'ltr']),
                'created_by'  => $faker->randomElement($users),
            ]);
        }
    }
}
