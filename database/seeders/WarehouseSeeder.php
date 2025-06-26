<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use App\Models\Region;
use Faker\Factory as Faker;

class WarehouseSeeder extends Seeder
{
    public function run()
    {
        $faker     = Faker::create();
        $regions   = Region::pluck('id')->all();  // ambil semua ID region

        // Check if regions exist
        if (empty($regions)) {
            $this->command->warn('Skipping WarehouseSeeder: No regions found. Please run RegionSeeder first.');
            return;
        }

        $warehouseNames = [
            'Jakarta Pusat',
            'Surabaya Timur', 
            'Bandung Utara',
            'Medan Selatan',
            'Makassar Barat'
        ];

        for ($i = 0; $i < 5; $i++) {
            Warehouse::create([
                'region_id'  => $faker->randomElement($regions),
                'name'       => $warehouseNames[$i] . ' Warehouse',
                'address'    => $faker->address,
                'phone'      => $faker->phoneNumber,
                'reference'  => 'WH-' . strtoupper($faker->bothify('########')), 
            ]);
        }

        $this->command->info('WarehouseSeeder completed successfully');
    }
}
