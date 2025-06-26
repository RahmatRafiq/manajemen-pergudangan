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

        // Check if required data exists
        if (empty($categories) || empty($users)) {
            $this->command->warn('Skipping ProductSeeder: Missing required data (categories or users)');
            return;
        }

        // Product names untuk lebih realistis
        $productNames = [
            'Laptop Gaming', 'Mouse Wireless', 'Keyboard Mechanical', 'Monitor LED', 'Headset Gaming',
            'Speaker Bluetooth', 'Webcam HD', 'Hard Drive External', 'SSD Internal', 'RAM DDR4',
            'Printer Inkjet', 'Scanner Document', 'Router WiFi', 'Switch Network', 'Cable HDMI',
            'Power Bank', 'Charger USB-C', 'Case Smartphone', 'Screen Protector', 'Tablet Android',
            'Smartwatch', 'Earphone Wireless', 'Microphone USB', 'Cooling Pad', 'USB Hub',
            'Adapter HDMI', 'Cable VGA', 'Memory Card', 'Flash Drive', 'Docking Station',
            'Standing Desk', 'Office Chair', 'Desk Lamp', 'Whiteboard', 'Marker Board',
            'Stapler Heavy Duty', 'Paper Shredder', 'Laminator Machine', 'Binding Machine', 'Label Printer',
            'Coffee Machine', 'Water Dispenser', 'Air Purifier', 'Humidifier', 'Desk Fan',
            'Filing Cabinet', 'Storage Box', 'Folder File', 'Binder Ring', 'Paper A4'
        ];

        // Buat 50 product dengan nama yang lebih realistis
        for ($i = 0; $i < 50; $i++) {
            $productName = $faker->randomElement($productNames);
            $brand = $faker->randomElement(['Logitech', 'HP', 'Dell', 'Asus', 'Canon', 'Samsung', 'Sony', 'Generic']);
            
            Product::create([
                'sku'         => strtoupper('SKU-' . $faker->bothify('???-####')),
                'name'        => $brand . ' ' . $productName . ' ' . $faker->bothify('##??'),
                'category_id' => $faker->randomElement($categories),
                'description' => $faker->paragraph(2),
                'unit'        => $faker->randomElement(['pcs', 'box', 'set', 'unit', 'pack']),
                'created_by'  => $faker->randomElement($users),
            ]);
        }

        $this->command->info('ProductSeeder completed successfully');
    }
}
