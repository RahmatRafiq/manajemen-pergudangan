<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userWarehouseIds = $user->warehouses()->pluck('warehouses.id');
        
        // If user has no warehouse access, show global data (for admin/manager)
        $isGlobalAccess = $userWarehouseIds->isEmpty();
        
        // Get warehouse filter from request
        $selectedWarehouseId = $request->query('warehouse_id');
        if ($selectedWarehouseId && !$isGlobalAccess && !$userWarehouseIds->contains($selectedWarehouseId)) {
            $selectedWarehouseId = null; // Reset if user doesn't have access
        }
        
        // Build query based on access level
        $inventoryQuery = Inventory::query();
        $transactionQuery = StockTransaction::query();
        
        if (!$isGlobalAccess) {
            $inventoryQuery->whereIn('warehouse_id', $userWarehouseIds);
            $transactionQuery->whereHas('inventory', function ($query) use ($userWarehouseIds) {
                $query->whereIn('warehouse_id', $userWarehouseIds);
            });
        }
        
        // Apply warehouse filter if selected
        if ($selectedWarehouseId) {
            $inventoryQuery->where('warehouse_id', $selectedWarehouseId);
            $transactionQuery->whereHas('inventory', function ($query) use ($selectedWarehouseId) {
                $query->where('warehouse_id', $selectedWarehouseId);
            });
        }
        
        // Basic Statistics
        $totalProducts = Product::count();
        $totalWarehouses = $isGlobalAccess ? Warehouse::count() : $userWarehouseIds->count();
        $totalInventoryValue = $inventoryQuery->sum('quantity');
        $totalUsers = User::count();
        
        // Stock Alerts
        $lowStockItems = (clone $inventoryQuery)->lowStock()->count();
        $overstockItems = (clone $inventoryQuery)->overstock()->count();
        $stockAlerts = $lowStockItems + $overstockItems;
        
        // Recent Transactions (last 7 days)
        $recentTransactions = (clone $transactionQuery)
            ->with(['inventory.product', 'inventory.warehouse', 'creator'])
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Transaction Statistics (last 30 days)
        $transactionStats = (clone $transactionQuery)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                'type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(ABS(quantity)) as total_quantity')
            )
            ->groupBy('type')
            ->get()
            ->keyBy('type');
        
        // Daily Transaction Chart (last 14 days)
        $dailyTransactions = (clone $transactionQuery)
            ->where('created_at', '>=', now()->subDays(14))
            ->select(
                DB::raw('DATE(created_at) as date'),
                'type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(ABS(quantity)) as quantity')
            )
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get()
            ->groupBy('date');
        
        // Top Moving Products (last 30 days)
        $topMovingProducts = (clone $transactionQuery)
            ->with(['inventory.product'])
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                'inventory_id',
                DB::raw('SUM(ABS(quantity)) as total_movement'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('inventory_id')
            ->orderBy('total_movement', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'product_name' => $item->inventory->product->name ?? 'Unknown',
                    'product_sku' => $item->inventory->product->sku ?? 'Unknown',
                    'warehouse_name' => $item->inventory->warehouse->name ?? 'Unknown',
                    'total_movement' => $item->total_movement,
                    'transaction_count' => $item->transaction_count,
                    'current_stock' => $item->inventory->quantity ?? 0,
                ];
            });
        
        // Low Stock Items Detail
        $lowStockDetails = (clone $inventoryQuery)
            ->lowStock()
            ->with(['product', 'warehouse'])
            ->orderBy('quantity', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'warehouse_name' => $item->warehouse->name,
                    'current_stock' => $item->quantity,
                    'min_stock' => $item->min_stock,
                    'percentage' => $item->min_stock > 0 ? round(($item->quantity / $item->min_stock) * 100, 1) : 0,
                ];
            });
        
        // Movement Analysis
        $movementAnalysis = Inventory::getSortedGlobalWithMovement('month')
            ->take(5)
            ->map(function ($item) {
                return [
                    'product_name' => $item->product->name ?? 'Unknown',
                    'warehouse_name' => $item->warehouse->name ?? 'Unknown',
                    'total_quantity' => $item->total_quantity,
                    'total_movement' => $item->total_movement,
                    'movement_ratio' => $item->movement_ratio,
                    'movement_category' => $item->movement_category,
                    'recommendation' => $item->recommendation,
                ];
            });
        
        // Warehouse Performance (if global access)
        $warehousePerformance = [];
        if ($isGlobalAccess) {
            $warehousePerformance = Warehouse::with(['users'])
                ->withCount(['users'])
                ->get()
                ->map(function ($warehouse) {
                    $inventoryCount = Inventory::where('warehouse_id', $warehouse->id)->count();
                    $totalStock = Inventory::where('warehouse_id', $warehouse->id)->sum('quantity');
                    $recentTransactions = StockTransaction::whereHas('inventory', function ($query) use ($warehouse) {
                        $query->where('warehouse_id', $warehouse->id);
                    })->where('created_at', '>=', now()->subDays(7))->count();
                    
                    return [
                        'id' => $warehouse->id,
                        'name' => $warehouse->name,
                        'reference' => $warehouse->reference,
                        'users_count' => $warehouse->users_count,
                        'inventory_count' => $inventoryCount,
                        'total_stock' => $totalStock,
                        'recent_activity' => $recentTransactions,
                    ];
                });
        }
        
        // Available warehouses for filter
        $availableWarehouses = $isGlobalAccess 
            ? Warehouse::all() 
            : Warehouse::whereIn('id', $userWarehouseIds)->get();
        
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_products' => $totalProducts,
                'total_warehouses' => $totalWarehouses,
                'total_inventory_value' => $totalInventoryValue,
                'total_users' => $totalUsers,
                'stock_alerts' => $stockAlerts,
                'low_stock_items' => $lowStockItems,
                'overstock_items' => $overstockItems,
            ],
            'transaction_stats' => $transactionStats,
            'daily_transactions' => $dailyTransactions,
            'recent_transactions' => $recentTransactions,
            'top_moving_products' => $topMovingProducts,
            'low_stock_details' => $lowStockDetails,
            'movement_analysis' => $movementAnalysis,
            'warehouse_performance' => $warehousePerformance,
            'is_global_access' => $isGlobalAccess,
            'available_warehouses' => $availableWarehouses,
            'selected_warehouse_id' => $selectedWarehouseId,
            'user_role' => $user->roles->first()->name ?? 'user',
        ]);
    }
}
