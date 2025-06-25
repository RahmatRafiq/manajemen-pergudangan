<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\User;
use App\Events\StockLevelChanged;
use App\Notifications\StockAlertNotification;
use Illuminate\Support\Facades\Log;

class InventoryObserver
{
    /**
     * Handle the Inventory "updated" event.
     */
    public function updated(Inventory $inventory): void
    {
        // Get the original quantity before update
        $originalQuantity = $inventory->getOriginal('quantity');
        $newQuantity = $inventory->quantity;

        // Only proceed if quantity actually changed
        if ($originalQuantity === $newQuantity) {
            return;
        }

        Log::info("Inventory quantity changed", [
            'inventory_id' => $inventory->id,
            'product_id' => $inventory->product_id,
            'warehouse_id' => $inventory->warehouse_id,
            'old_quantity' => $originalQuantity,
            'new_quantity' => $newQuantity,
            'min_stock' => $inventory->min_stock,
            'max_stock' => $inventory->max_stock,
        ]);

        // Determine alert type
        $alertType = $this->determineAlertType($inventory);

        // Broadcast stock level change event
        event(new StockLevelChanged($inventory, $originalQuantity, $newQuantity, $alertType));

        // Send notifications if there's an alert
        if ($alertType) {
            $this->sendStockAlert($inventory, $alertType);
        }
    }

    /**
     * Handle the Inventory "created" event.
     */
    public function created(Inventory $inventory): void
    {
        // Check for alerts on newly created inventory
        $alertType = $this->determineAlertType($inventory);
        
        if ($alertType) {
            Log::info("New inventory created with alert", [
                'inventory_id' => $inventory->id,
                'alert_type' => $alertType,
            ]);

            // Broadcast the event
            event(new StockLevelChanged($inventory, 0, $inventory->quantity, $alertType));
            
            // Send notification
            $this->sendStockAlert($inventory, $alertType);
        }
    }

    /**
     * Determine if there should be an alert based on stock levels.
     */
    private function determineAlertType(Inventory $inventory): ?string
    {
        // Skip if min_stock or max_stock is not set
        if (is_null($inventory->min_stock) && is_null($inventory->max_stock)) {
            return null;
        }

        // Check for low stock
        if (!is_null($inventory->min_stock) && $inventory->quantity <= $inventory->min_stock) {
            return 'low_stock';
        }

        // Check for overstock
        if (!is_null($inventory->max_stock) && $inventory->quantity >= $inventory->max_stock) {
            return 'overstock';
        }

        return null;
    }

    /**
     * Send stock alert notifications to relevant users.
     */
    private function sendStockAlert(Inventory $inventory, string $alertType): void
    {
        try {
            // Load relationships if not already loaded
            $inventory->load(['product', 'warehouse']);

            // Get users who should receive stock alerts
            // You can customize this logic based on your requirements
            $recipients = $this->getStockAlertRecipients($inventory);

            foreach ($recipients as $user) {
                $user->notify(new StockAlertNotification($inventory, $alertType));
            }

            Log::info("Stock alert notifications sent", [
                'inventory_id' => $inventory->id,
                'alert_type' => $alertType,
                'recipients_count' => $recipients->count(),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send stock alert notifications", [
                'inventory_id' => $inventory->id,
                'alert_type' => $alertType,
                'error' => $e->getMessage(),
            ]);
        }
    }    /**
     * Get users who should receive stock alert notifications.
     */
    private function getStockAlertRecipients(Inventory $inventory)
    {
        // Get admin users and regular users (you can customize this based on your needs)
        $recipients = User::role(['admin', 'user'])->get();

        // If no admin users found, get all users
        if ($recipients->isEmpty()) {
            $recipients = User::limit(10)->get(); // Limit to avoid spam
        }

        // You can add more specific logic here, for example:
        // - Users assigned to specific warehouses
        // - Users responsible for specific product categories
        // - Users with specific permissions

        return $recipients;
    }
}
