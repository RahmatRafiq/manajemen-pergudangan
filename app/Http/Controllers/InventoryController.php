<?php
namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $filter      = $request->query('filter', 'active');
        $inventories = match ($filter) {
            'trashed' => Inventory::onlyTrashed()->with(['product', 'warehouse'])->get(),
            'all' => Inventory::withTrashed()->with(['product', 'warehouse'])->get(),
            default => Inventory::with(['product', 'warehouse'])->get(),
        };

        return Inertia::render('Inventory/Index', [
            'inventories' => $inventories,
            'filter'      => $filter,
            'products'    => Product::all(),
            'warehouses'  => Warehouse::all(),
        ]);
    }

    public function json(Request $request)
    {
        $search = $request->input('search.value', '');
        $filter = $request->input('filter', 'active');

        $query = match ($filter) {
            'trashed' => Inventory::onlyTrashed()->with(['product', 'warehouse']),
            'all' => Inventory::withTrashed()->with(['product', 'warehouse']),
            default => Inventory::with(['product', 'warehouse']),
        };

        if ($search) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })->orWhereHas('warehouse', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $columns = ['id', 'warehouse_id', 'product_id', 'quantity', 'reserved', 'min_stock', 'max_stock', 'updated_at'];
        if ($request->filled('order')) {
            $orderColumn = $columns[$request->order[0]['column']] ?? 'id';
            $query->orderBy($orderColumn, $request->order[0]['dir']);
        }

        $data = DataTable::paginate($query, $request);

        $data['data'] = collect($data['data'])->map(function ($inventory) {
            return [
                'id'         => $inventory->id,
                'warehouse'  => $inventory->warehouse?->name,
                'product'    => $inventory->product?->name,
                'sku'        => $inventory->product?->sku,
                'quantity'   => $inventory->quantity,
                'reserved'   => $inventory->reserved,
                'min_stock'  => $inventory->min_stock,
                'max_stock'  => $inventory->max_stock,
                'updated_at' => $inventory->updated_at,
                'trashed'    => $inventory->trashed(),
                'actions'    => '',
            ];
        });

        return response()->json($data);
    }

    public function create()
    {
        return Inertia::render('Inventory/Form', [
            'products'   => Product::all(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id'   => 'required|exists:products,id',
            'quantity'     => 'required|integer|min:0',
            'reserved'     => 'nullable|integer|min:0',
            'min_stock'    => 'nullable|integer|min:0',
            'max_stock'    => 'nullable|integer|min:0',
        ]);

        $validatedData['updated_by'] = auth()->id();

        Inventory::create($validatedData);

        return redirect()->route('inventory.index')->with('success', 'Inventory berhasil dibuat.');
    }

    public function edit($id)
    {
        $inventory = Inventory::withTrashed()->findOrFail($id);
        return Inertia::render('Inventory/Form', [
            'inventory'  => $inventory,
            'products'   => Product::all(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id'   => 'required|exists:products,id',
            'quantity'     => 'required|integer|min:0',
            'reserved'     => 'nullable|integer|min:0',
            'min_stock'    => 'nullable|integer|min:0',
            'max_stock'    => 'nullable|integer|min:0',
        ]);

        $inventory                   = Inventory::withTrashed()->findOrFail($id);
        $validatedData['updated_by'] = auth()->id();
        $inventory->update($validatedData);

        return redirect()->route('inventory.index')->with('success', 'Inventory berhasil diperbarui.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventory.index')->with('success', 'Inventory berhasil dihapus.');
    }

    public function trashed()
    {
        $inventories = Inventory::onlyTrashed()->with(['product', 'warehouse'])->get();
        return Inertia::render('Inventory/Trashed', [
            'inventories' => $inventories,
        ]);
    }

    public function restore($id)
    {
        Inventory::onlyTrashed()->where('id', $id)->restore();
        return redirect()->route('inventory.index')->with('success', 'Inventory berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        Inventory::onlyTrashed()->where('id', $id)->forceDelete();
        return redirect()->route('inventory.index')->with('success', 'Inventory berhasil dihapus permanen.');
    }
}
