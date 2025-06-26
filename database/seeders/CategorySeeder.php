<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Faker\Factory as Faker;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Electronics', 'type' => 'Finished Goods', 'description' => 'Electronic devices and components'],
            ['name' => 'Computer Hardware', 'type' => 'Finished Goods', 'description' => 'Computer parts and accessories'],
            ['name' => 'Audio & Video', 'type' => 'Finished Goods', 'description' => 'Audio and video equipment'],
            ['name' => 'Networking', 'type' => 'Finished Goods', 'description' => 'Network equipment and cables'],
            ['name' => 'Storage Devices', 'type' => 'Finished Goods', 'description' => 'Data storage solutions'],
            ['name' => 'Mobile Accessories', 'type' => 'Finished Goods', 'description' => 'Smartphone and tablet accessories'],
            ['name' => 'Office Equipment', 'type' => 'Finished Goods', 'description' => 'Office machines and tools'],
            ['name' => 'Furniture', 'type' => 'Finished Goods', 'description' => 'Office and workspace furniture'],
            ['name' => 'Stationery', 'type' => 'Consumable', 'description' => 'Office supplies and stationery'],
            ['name' => 'Consumables', 'type' => 'Consumable', 'description' => 'Consumable office items']
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']], 
                [
                    'type' => $category['type'],
                    'description' => $category['description']
                ]
            );
        }

        $this->command->info('CategorySeeder completed successfully');
    }
}
