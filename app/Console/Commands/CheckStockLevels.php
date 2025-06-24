<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\User;
use App\Notifications\StockAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckStockLevels extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'stock:check-levels 
                           {--warehouse-id= : Check only specific warehouse}
                           {--send-notifications : Send notifications for alerts}';

    /**
     * The console command description.
     */
    protected $description = 'Check inventory stock levels and identify items that need attention';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking stock levels...');

        $query = Inventory::needsAttention()->with(['product', 'warehouse']);

        if ($warehouseId = $this->option('warehouse-id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        $alertItems = $query->get();

        if ($alertItems->isEmpty()) {
            $this->info('✅ All stock levels are within normal ranges.');
            return self::SUCCESS;
        }

        $this->warn("⚠️  Found {$alertItems->count()} items requiring attention:");

        $lowStockCount = 0;
        $overstockCount = 0;

        foreach ($alertItems as $item) {
            $status = $item->getStockStatus();
            $icon = $status === 'low' ? '🔻' : '🔺';
            $alertType = $status === 'low' ? 'LOW STOCK' : 'OVERSTOCK';
            
            if ($status === 'low') {
                $lowStockCount++;
            } else {
                $overstockCount++;
            }

            $this->line("{$icon} [{$alertType}] {$item->product->name} @ {$item->warehouse->name}");
            $this->line("    Current: {$item->quantity} | Min: {$item->min_stock} | Max: {$item->max_stock}");

            // Send notifications if requested
            if ($this->option('send-notifications')) {
                $this->sendNotifications($item, $status === 'low' ? 'low_stock' : 'overstock');
            }
        }

        $this->info("\n📊 Summary:");
        $this->line("   Low Stock Items: {$lowStockCount}");
        $this->line("   Overstock Items: {$overstockCount}");
        $this->line("   Total Items: {$alertItems->count()}");

        if ($this->option('send-notifications')) {
            $this->info("\n✉️  Notifications sent to relevant users.");
        } else {
            $this->comment("\n💡 Tip: Use --send-notifications to send alerts to users.");
        }

        return self::SUCCESS;
    }    /**
     * Send notifications for stock alert.
     */
    private function sendNotifications(Inventory $inventory, string $alertType): void
    {
        try {
            $recipients = User::role(['admin'])->get();

            // If no admin users found, get all users
            if ($recipients->isEmpty()) {
                $recipients = User::limit(10)->get(); // Limit to avoid spam
            }

            foreach ($recipients as $user) {
                $user->notify(new StockAlertNotification($inventory, $alertType));
            }

            Log::info("Stock alert notifications sent via command", [
                'inventory_id' => $inventory->id,
                'alert_type' => $alertType,
                'recipients_count' => $recipients->count(),
            ]);

        } catch (\Exception $e) {
            $this->error("Failed to send notifications for inventory {$inventory->id}: {$e->getMessage()}");
            
            Log::error("Failed to send stock alert notifications via command", [
                'inventory_id' => $inventory->id,
                'alert_type' => $alertType,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
