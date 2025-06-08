<?php
namespace Database\Seeders;

use App\Models\Region;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {
            Region::create([
                'code'        => $faker->unique()->lexify('RG?'), // misal: RGA, RGB, RGC
                'name'        => ucfirst($faker->state),
                'description' => $faker->sentence(6),
            ]);
        }
    }
}
