<?php
namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Models\Inventory;
use Illuminate\Http\Request;

class InventorySortController extends Controller
{
    public function byWarehouse($warehouseId)
    {
        $inventories = Inventory::getSortedByWarehouse($warehouseId);
        return inertia('Inventory/SortedByWarehouse', [
            'warehouse_id' => $warehouseId,
            'inventories' => $inventories,
        ]);
    }

    public function global(Request $request)
    {
        $period = $request->input('period', 'month'); // default month
        $inventories = Inventory::getSortedGlobalWithMovement($period);
        
        return inertia('Inventory/SortedGlobal', [
            'inventories' => $inventories,
            'period' => $period,
            'periods' => [
                'week' => 'Minggu Ini',
                'month' => 'Bulan Ini', 
                'year' => 'Tahun Ini'
            ]
        ]);
    }
    public function jsonByWarehouse(Request $request, $warehouseId)
    {
        $query = Inventory::where('warehouse_id', $warehouseId)->with('product');
        return DataTable::paginate($query, $request);
    }

    public function jsonGlobal(Request $request)
    {
        $period = $request->input('period', 'month');
        $movementFilter = $request->input('movement_filter'); // no_movement, low_movement, etc.
        
        $inventories = Inventory::getSortedGlobalWithMovement($period);
        
        // Apply movement filter if specified
        if ($movementFilter) {
            $inventories = $inventories->where('movement_category', $movementFilter);
        }
        
        // Convert to array for DataTable processing
        $data = $inventories->toArray();
        
        return response()->json([
            'data' => $data,
            'total' => count($data),
            'filtered' => count($data),
            'period' => $period,
            'summary' => [
                'no_movement' => $inventories->where('movement_category', 'no_movement')->count(),
                'low_movement' => $inventories->where('movement_category', 'low_movement')->count(),
                'medium_movement' => $inventories->where('movement_category', 'medium_movement')->count(),
                'high_movement' => $inventories->where('movement_category', 'high_movement')->count(),
            ]
        ]);
    }
    
    public function getMovementStatistics(Request $request)
    {
        $period = $request->input('period', 'month');
        $statistics = Inventory::getMovementStatistics($period);
        
        return response()->json([
            'statistics' => $statistics,
            'period' => $period
        ]);
    }
}
