<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            RegionSeeder::class,
            WarehouseSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            InventorySeeder::class,
        ]);

    }
}
