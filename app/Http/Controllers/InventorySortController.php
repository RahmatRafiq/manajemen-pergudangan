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

    public function global()
    {
        $inventories = Inventory::getSortedGlobal();
        return inertia('Inventory/SortedGlobal', [
            'inventories' => $inventories,
        ]);
    }
    public function jsonByWarehouse(Request $request, $warehouseId)
    {
        $query = Inventory::where('warehouse_id', $warehouseId)->with('product');
        return DataTable::paginate($query, $request);
    }

    public function jsonGlobal(Request $request)
    {
        $query = Inventory::query()->with('product');
        return DataTable::paginate($query, $request);
    }
}
