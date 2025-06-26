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
        $this->command->info('🌱 Starting database seeding...');
        
        $this->call([
            // 1. Authentication & Authorization
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            
            // 2. Master Data
            RegionSeeder::class,
            WarehouseSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            
            // 3. Inventory Data
            InventorySeeder::class,
            
            // 4. Transaction Data (depends on inventory)
            StockTransactionSeeder::class,
        ]);

        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->info('📊 You can now test the inventory movement analysis feature.');
    }
}
