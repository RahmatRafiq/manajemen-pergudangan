<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SafeDatabaseSeeder extends Seeder
{
    /**
     * Run database seeders with safety checks
     */
    public function run(): void
    {
        $this->command->info('🔍 Running safety checks...');
        
        // Check if required tables exist
        $requiredTables = ['users', 'roles', 'permissions', 'regions', 'categories'];
        
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $this->command->error("❌ Required table '{$table}' does not exist. Please run migrations first.");
                return;
            }
        }

        $this->command->info('✅ Safety checks passed!');
        
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            $this->call([
                // 1. Authentication & Authorization
                RoleSeeder::class,
                PermissionSeeder::class, 
                RolePermissionSeeder::class,
                UserSeeder::class,
                
                // 2. Master Data
                RegionSeeder::class,
                CategorySeeder::class,
                WarehouseSeeder::class,
                ProductSeeder::class,
                
                // 3. Inventory Data
                InventorySeeder::class,
                
                // 4. Transaction Data
                StockTransactionSeeder::class,
            ]);
            
            $this->command->info('🎉 All seeders completed successfully!');
            
        } catch (\Exception $e) {
            $this->command->error('❌ Error during seeding: ' . $e->getMessage());
            throw $e;
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        $this->showSummary();
    }
    
    private function showSummary()
    {
        $this->command->info('📊 Seeding Summary:');
        $this->command->info('👥 Users: ' . \App\Models\User::count());
        $this->command->info('🏢 Regions: ' . \App\Models\Region::count());
        $this->command->info('🏪 Warehouses: ' . \App\Models\Warehouse::count());
        $this->command->info('📂 Categories: ' . \App\Models\Category::count());
        $this->command->info('📦 Products: ' . \App\Models\Product::count());
        $this->command->info('📋 Inventories: ' . \App\Models\Inventory::count());
        $this->command->info('🔄 Stock Transactions: ' . \App\Models\StockTransaction::count());
        $this->command->info('');
        $this->command->info('🚀 You can now access: /inventory/sorted/global');
    }
}
