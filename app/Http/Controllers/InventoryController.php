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
        $filter = $request->query('filter', 'active');
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $inventories = match ($filter) {
                'trashed' => Inventory::onlyTrashed()->with(['product', 'warehouse'])->get(),
                'all' => Inventory::withTrashed()->with(['product', 'warehouse'])->get(),
                default => Inventory::with(['product', 'warehouse'])->get(),
            };
        } else {
            $userWarehouseIds = $user->warehouses->pluck('id');
            $inventories = match ($filter) {
                'trashed' => Inventory::onlyTrashed()->whereIn('warehouse_id', $userWarehouseIds)->with(['product', 'warehouse'])->get(),
                'all' => Inventory::withTrashed()->whereIn('warehouse_id', $userWarehouseIds)->with(['product', 'warehouse'])->get(),
                default => Inventory::whereIn('warehouse_id', $userWarehouseIds)->with(['product', 'warehouse'])->get(),
            };
        }

        return Inertia::render('Inventory/Index', [
            'inventories' => $inventories,
            'filter' => $filter,
            'products' => Product::all(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function json(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search.value', '');
        $filter = $request->input('filter', 'active');

        if ($user->hasRole('admin')) {
            $query = match ($filter) {
                'trashed' => Inventory::onlyTrashed()->with(['product', 'warehouse']),
                'all' => Inventory::withTrashed()->with(['product', 'warehouse']),
                default => Inventory::with(['product', 'warehouse']),
            };
        } else {
            $userWarehouseIds = $user->warehouses->pluck('id');
            $query = match ($filter) {
                'trashed' => Inventory::onlyTrashed()->whereIn('warehouse_id', $userWarehouseIds)->with(['product', 'warehouse']),
                'all' => Inventory::withTrashed()->whereIn('warehouse_id', $userWarehouseIds)->with(['product', 'warehouse']),
                default => Inventory::whereIn('warehouse_id', $userWarehouseIds)->with(['product', 'warehouse']),
            };
        }

        if ($search) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })->orWhereHas('warehouse', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $columns = ['id', 'warehouse_id', 'product_id', 'quantity', 'min_stock', 'max_stock', 'updated_at'];
        if ($request->filled('order')) {
            $orderColumn = $columns[$request->order[0]['column']] ?? 'id';
            $query->orderBy($orderColumn, $request->order[0]['dir']);
        }

        $data = DataTable::paginate($query, $request);

        $data['data'] = collect($data['data'])->map(function ($inventory) {
            return [
                'id' => $inventory->id,
                'warehouse' => $inventory->warehouse?->name,
                'product' => $inventory->product?->name,
                'sku' => $inventory->product?->sku,
                'quantity' => $inventory->quantity,
                'min_stock' => $inventory->min_stock,
                'max_stock' => $inventory->max_stock,
                'updated_at' => $inventory->updated_at,
                'trashed' => $inventory->trashed(),
                'actions' => '',
            ];
        });

        return response()->json($data);
    }

    public function create()
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $warehouses = Warehouse::all();
            $usedProductIds = Inventory::whereNull('deleted_at')->pluck('product_id')->unique();
            $products = Product::whereNotIn('id', $usedProductIds)->get();
        } else {
            $warehouses = $user->warehouses;
            $usedProductIds = Inventory::whereIn('warehouse_id', $warehouses->pluck('id'))
                ->whereNull('deleted_at')
                ->pluck('product_id')
                ->unique();
            $products = Product::whereNotIn('id', $usedProductIds)->get();
        }

        return Inertia::render('Inventory/Form', [
            'products' => $products,
            'warehouses' => $warehouses,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'reserved' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ]);

        $deletedInventory = Inventory::onlyTrashed()
            ->where('warehouse_id', $validatedData['warehouse_id'])
            ->where('product_id', $validatedData['product_id'])
            ->first();

        if ($deletedInventory) {
            $deletedInventory->restore();
            $validatedData['updated_by'] = auth()->id();
            $deletedInventory->update($validatedData);
            
            return redirect()->route('inventory.index')->with('success', 'Inventory yang sudah dihapus berhasil dipulihkan dan diperbarui.');
        }

        $existingInventory = Inventory::where('warehouse_id', $validatedData['warehouse_id'])
            ->where('product_id', $validatedData['product_id'])
            ->whereNull('deleted_at')
            ->first();

        if ($existingInventory) {
            return back()->withErrors([
                'product_id' => 'Produk ini sudah ada di gudang yang dipilih.'
            ])->withInput();
        }

        $validatedData['updated_by'] = auth()->id();

        Inventory::create($validatedData);

        return redirect()->route('inventory.index')->with('success', 'Inventory berhasil dibuat.');
    }

    public function edit($id)
    {
        $inventory = Inventory::withTrashed()->findOrFail($id);
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $warehouses = Warehouse::all();
            $usedProductIds = Inventory::where('warehouse_id', $inventory->warehouse_id)
                ->where('id', '!=', $inventory->id)
                ->whereNull('deleted_at')
                ->pluck('product_id')
                ->unique();
            $products = Product::whereNotIn('id', $usedProductIds)->get();
        } else {
            $warehouses = $user->warehouses;
            $usedProductIds = Inventory::whereIn('warehouse_id', $warehouses->pluck('id'))
                ->where('id', '!=', $inventory->id)
                ->whereNull('deleted_at')
                ->pluck('product_id')
                ->unique();
            $products = Product::whereNotIn('id', $usedProductIds)->get();
        }

        return Inertia::render('Inventory/Form', [
            'inventory' => $inventory,
            'products' => $products,
            'warehouses' => $warehouses,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'reserved' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ]);

        $inventory = Inventory::withTrashed()->findOrFail($id);

        $existingInventory = Inventory::where('warehouse_id', $validatedData['warehouse_id'])
            ->where('product_id', $validatedData['product_id'])
            ->where('id', '!=', $id)
            ->whereNull('deleted_at')
            ->first();

        if ($existingInventory) {
            return back()->withErrors([
                'product_id' => 'Produk ini sudah ada di gudang yang dipilih.'
            ])->withInput();
        }

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
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $inventories = Inventory::onlyTrashed()->with(['product', 'warehouse'])->get();
        } else {
            $userWarehouseIds = $user->warehouses->pluck('id');
            $inventories = Inventory::onlyTrashed()->whereIn('warehouse_id', $userWarehouseIds)->with(['product', 'warehouse'])->get();
        }

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
