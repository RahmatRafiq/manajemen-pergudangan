<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StockTestController extends Controller
{
    /**
     * Test low stock alert
     */
    public function testLowStock(Request $request)
    {
        try {
            $inventory = Inventory::with(['product', 'warehouse'])->first();
            
            if (!$inventory) {
                return response()->json(['error' => 'No inventory found'], 404);
            }

            // Set quantity below minimum to trigger low stock alert
            $oldQuantity = $inventory->quantity;
            $inventory->update([
                'quantity' => max(0, ($inventory->min_stock ?? 10) - 1),
                'min_stock' => $inventory->min_stock ?? 10,
            ]);

            Log::info('Low stock test triggered', [
                'inventory_id' => $inventory->id,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $inventory->quantity,
                'min_stock' => $inventory->min_stock,
            ]);

            return response()->json([
                'message' => 'Low stock alert test completed',
                'inventory' => [
                    'id' => $inventory->id,
                    'product' => $inventory->product->name,
                    'warehouse' => $inventory->warehouse->name,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $inventory->quantity,
                    'min_stock' => $inventory->min_stock,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Low stock test failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Test overstock alert
     */
    public function testOverstock(Request $request)
    {
        try {
            $inventory = Inventory::with(['product', 'warehouse'])->first();
            
            if (!$inventory) {
                return response()->json(['error' => 'No inventory found'], 404);
            }

            // Set quantity above maximum to trigger overstock alert
            $oldQuantity = $inventory->quantity;
            $inventory->update([
                'quantity' => ($inventory->max_stock ?? 100) + 10,
                'max_stock' => $inventory->max_stock ?? 100,
            ]);

            Log::info('Overstock test triggered', [
                'inventory_id' => $inventory->id,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $inventory->quantity,
                'max_stock' => $inventory->max_stock,
            ]);

            return response()->json([
                'message' => 'Overstock alert test completed',
                'inventory' => [
                    'id' => $inventory->id,
                    'product' => $inventory->product->name,
                    'warehouse' => $inventory->warehouse->name,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $inventory->quantity,
                    'max_stock' => $inventory->max_stock,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Overstock test failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get current stock alerts
     */
    public function getStockAlerts()
    {
        try {
            $lowStockItems = Inventory::lowStock()
                ->with(['product', 'warehouse'])
                ->get();

            $overstockItems = Inventory::overstock()
                ->with(['product', 'warehouse'])
                ->get();

            return response()->json([
                'low_stock' => $lowStockItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product->name,
                        'warehouse_name' => $item->warehouse->name,
                        'current_quantity' => $item->quantity,
                        'min_stock' => $item->min_stock,
                        'status' => 'low_stock'
                    ];
                }),
                'overstock' => $overstockItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product->name,
                        'warehouse_name' => $item->warehouse->name,
                        'current_quantity' => $item->quantity,
                        'max_stock' => $item->max_stock,
                        'status' => 'overstock'
                    ];
                }),
                'total_alerts' => $lowStockItems->count() + $overstockItems->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Get stock alerts failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reset inventory to normal levels
     */
    public function resetInventory(Request $request)
    {
        try {
            $inventory = Inventory::with(['product', 'warehouse'])->first();
            
            if (!$inventory) {
                return response()->json(['error' => 'No inventory found'], 404);
            }

            $oldQuantity = $inventory->quantity;
            $newQuantity = rand(($inventory->min_stock ?? 10) + 5, ($inventory->max_stock ?? 100) - 5);
            
            $inventory->update([
                'quantity' => $newQuantity,
                'min_stock' => $inventory->min_stock ?? 10,
                'max_stock' => $inventory->max_stock ?? 100,
            ]);

            return response()->json([
                'message' => 'Inventory reset to normal levels',
                'inventory' => [
                    'id' => $inventory->id,
                    'product' => $inventory->product->name,
                    'warehouse' => $inventory->warehouse->name,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $inventory->quantity,
                    'min_stock' => $inventory->min_stock,
                    'max_stock' => $inventory->max_stock,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Reset inventory failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
