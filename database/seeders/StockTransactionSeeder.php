<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StockTransaction;
use App\Models\Inventory;
use App\Models\User;
use Faker\Factory as Faker;
use Carbon\Carbon;

class StockTransactionSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        
        $inventories = Inventory::with(['product', 'warehouse'])->get();
        $users = User::pluck('id')->toArray();

        if ($inventories->isEmpty() || empty($users)) {
            $this->command->warn('Skipping StockTransactionSeeder: Missing required data (inventories or users)');
            return;
        }

        $transactionTypes = [
            'in' => ['purchase', 'return', 'adjustment_in', 'transfer_in'],
            'out' => ['sale', 'return_to_supplier', 'adjustment_out', 'transfer_out', 'damage', 'expired']
        ];

        // Generate transaksi untuk 3 bulan terakhir
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();

        foreach ($inventories as $inventory) {
            // Buat 5-15 transaksi per inventory
            $transactionCount = $faker->numberBetween(5, 15);
            
            for ($i = 0; $i < $transactionCount; $i++) {
                $transactionDate = $faker->dateTimeBetween($startDate, $endDate);
                
                // Tentukan type transaksi (80% out, 20% in untuk simulasi real)
                $isInbound = $faker->boolean(20); // 20% chance inbound
                $typeCategory = $isInbound ? 'in' : 'out';
                $transactionType = $faker->randomElement($transactionTypes[$typeCategory]);
                
                // Tentukan quantity berdasarkan type
                if ($isInbound) {
                    $quantity = $faker->numberBetween(10, 100); // positive for inbound
                } else {
                    $quantity = -$faker->numberBetween(5, 50); // negative for outbound
                }

                // Generate reference number
                $reference = strtoupper($transactionType) . '-' . $faker->bothify('####-###');
                
                // Create transaction
                StockTransaction::create([
                    'inventory_id' => $inventory->id,
                    'type' => $transactionType,
                    'quantity' => $quantity,
                    'reference' => $reference,
                    'description' => $this->generateDescription($transactionType, $inventory, $faker),
                    'created_by' => $faker->randomElement($users),
                    'approved_by' => $faker->boolean(80) ? $faker->randomElement($users) : null, // 80% approved
                    'approved_at' => $faker->boolean(80) ? $transactionDate : null,
                    'created_at' => $transactionDate,
                    'updated_at' => $transactionDate,
                ]);
            }
        }

        // Generate beberapa transaksi untuk periode minggu ini (untuk testing weekly filter)
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        
        $recentInventories = $inventories->random(min(10, $inventories->count()));
        
        foreach ($recentInventories as $inventory) {
            $transactionCount = $faker->numberBetween(1, 3);
            
            for ($i = 0; $i < $transactionCount; $i++) {
                $transactionDate = $faker->dateTimeBetween($weekStart, $weekEnd);
                $isInbound = $faker->boolean(30);
                $typeCategory = $isInbound ? 'in' : 'out';
                $transactionType = $faker->randomElement($transactionTypes[$typeCategory]);
                
                if ($isInbound) {
                    $quantity = $faker->numberBetween(5, 30);
                } else {
                    $quantity = -$faker->numberBetween(3, 20);
                }

                $reference = strtoupper($transactionType) . '-' . $faker->bothify('####-###');
                
                StockTransaction::create([
                    'inventory_id' => $inventory->id,
                    'type' => $transactionType,
                    'quantity' => $quantity,
                    'reference' => $reference,
                    'description' => $this->generateDescription($transactionType, $inventory, $faker),
                    'created_by' => $faker->randomElement($users),
                    'approved_by' => $faker->randomElement($users),
                    'approved_at' => $transactionDate,
                    'created_at' => $transactionDate,
                    'updated_at' => $transactionDate,
                ]);
            }
        }

        // Generate some dead stock scenarios (no transactions for certain products)
        $deadStockInventories = $inventories->random(min(5, $inventories->count()));
        foreach ($deadStockInventories as $inventory) {
            // Hapus transaksi recent untuk simulasi dead stock
            StockTransaction::where('inventory_id', $inventory->id)
                ->where('created_at', '>=', Carbon::now()->subMonth())
                ->delete();
        }

        $this->command->info('StockTransactionSeeder completed successfully');
        $this->command->info('Total transactions created: ' . StockTransaction::count());
    }

    private function generateDescription($type, $inventory, $faker)
    {
        $descriptions = [
            'purchase' => "Pembelian stock {$inventory->product->name} untuk {$inventory->warehouse->name}",
            'sale' => "Penjualan {$inventory->product->name} dari {$inventory->warehouse->name}",
            'return' => "Return {$inventory->product->name} ke {$inventory->warehouse->name}",
            'return_to_supplier' => "Return {$inventory->product->name} ke supplier",
            'adjustment_in' => "Stock opname - penambahan {$inventory->product->name}",
            'adjustment_out' => "Stock opname - pengurangan {$inventory->product->name}",
            'transfer_in' => "Transfer masuk {$inventory->product->name} ke {$inventory->warehouse->name}",
            'transfer_out' => "Transfer keluar {$inventory->product->name} dari {$inventory->warehouse->name}",
            'damage' => "Barang rusak - {$inventory->product->name}",
            'expired' => "Barang kadaluarsa - {$inventory->product->name}",
        ];

        return $descriptions[$type] ?? "Transaksi {$type} untuk {$inventory->product->name}";
    }
}
