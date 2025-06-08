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

        for ($i = 0; $i < 5; $i++) {
            Warehouse::create([
                'region_id'  => $faker->randomElement($regions),
                'name'       => $faker->company . ' Warehouse',
                'address'    => $faker->address,
                'phone'      => $faker->phoneNumber,
                'reference'  => 'WH-' . strtoupper($faker->bothify('########')), 
            ]);
        }
    }
}
