<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Faker\Factory as Faker;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $types = ['Raw Material', 'Finished Goods', 'Consumable'];

        // Buat 10 kategori
        for ($i = 0; $i < 10; $i++) {
            Category::create([
                'name'        => ucfirst($faker->unique()->word) . ' Category',
                'type'        => $faker->randomElement($types),
                'description' => $faker->sentence(6),
            ]);
        }
    }
}
