<?php
namespace Database\Seeders;

use App\Models\Region;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run()
    {
        $regions = [
            ['name' => 'DKI Jakarta', 'code' => 'JKT', 'description' => 'Daerah Khusus Ibukota Jakarta'],
            ['name' => 'Jawa Barat', 'code' => 'JBR', 'description' => 'Provinsi Jawa Barat'],
            ['name' => 'Jawa Tengah', 'code' => 'JTG', 'description' => 'Provinsi Jawa Tengah'],
            ['name' => 'Jawa Timur', 'code' => 'JTM', 'description' => 'Provinsi Jawa Timur'],
            ['name' => 'Sumatera Utara', 'code' => 'SMU', 'description' => 'Provinsi Sumatera Utara'],
            ['name' => 'Sumatera Selatan', 'code' => 'SMS', 'description' => 'Provinsi Sumatera Selatan'],
            ['name' => 'Kalimantan Timur', 'code' => 'KLT', 'description' => 'Provinsi Kalimantan Timur'],
            ['name' => 'Sulawesi Selatan', 'code' => 'SLS', 'description' => 'Provinsi Sulawesi Selatan'],
        ];

        foreach ($regions as $region) {
            Region::firstOrCreate(
                ['code' => $region['code']], 
                [
                    'name' => $region['name'],
                    'description' => $region['description']
                ]
            );
        }

        $this->command->info('RegionSeeder completed successfully');
    }
}
