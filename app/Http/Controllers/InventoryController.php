<?php
namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $filter      = $request->query('filter', 'active');
        $inventories = match ($filter) {
            'trashed' => Inventory::onlyTrashed()->get(),
            'all' => Inventory::withTrashed()->get(),
            default => Inventory::all(),
        };

        return Inertia::render('Inventory/Index', [
            'inventories' => $inventories,
            'filter'      => $filter,
        ]);
    }

    public function json(Request $request)
    {
        $search = $request->input('search.value', '');
        $filter = $request->input('filter', 'active');

        $query = match ($filter) {
            'trashed' => Inventory::onlyTrashed(),
            'all' => Inventory::withTrashed(),
            default => Inventory::query(),
        };

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('warehouse_id', 'like', "%{$search}%")
                    ->orWhere('product_id', 'like', "%{$search}%");
            });
        }

        $columns = ['id', 'warehouse_id', 'product_id', 'quantity', 'reserved', 'min_stock', 'max_stock', 'updated_by', 'updated_at'];
        if ($request->filled('order')) {
            $orderColumn = $columns[$request->order[0]['column']] ?? 'id';
            $query->orderBy($orderColumn, $request->order[0]['dir']);
        }

        $data = DataTable::paginate($query, $request);

        $data['data'] = collect($data['data'])->map(function ($inventory) {
            return [
                'id'           => $inventory->id,
                'warehouse_id' => $inventory->warehouse_id,
                'product_id'   => $inventory->product_id,
                'quantity'     => $inventory->quantity,
                'reserved'     => $inventory->reserved,
                'min_stock'    => $inventory->min_stock,
                'max_stock'    => $inventory->max_stock,
                'updated_by'   => $inventory->updated_by,
                'trashed'      => $inventory->trashed(),
                'actions'      => '',
            ];
        });

        return response()->json($data);
    }

    public function create()
    {
        return Inertia::render('Inventory/Form');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'warehouse_id' => 'required|integer',
            'product_id'   => 'required|integer',
            'quantity'     => 'required|integer',
            'reserved'     => 'nullable|integer',
            'min_stock'    => 'nullable|integer',
            'max_stock'    => 'nullable|integer',
            'updated_by'   => 'nullable|integer',
        ]);

        Inventory::create($validatedData);

        return redirect()->route('inventories.index')->with('success', 'Inventory berhasil dibuat.');
    }

    public function edit($id)
    {
        $inventory = Inventory::withTrashed()->findOrFail($id);
        return Inertia::render('Inventory/Form', [
            'inventory' => $inventory,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'warehouse_id' => 'required|integer',
            'product_id'   => 'required|integer',
            'quantity'     => 'required|integer',
            'reserved'     => 'nullable|integer',
            'min_stock'    => 'nullable|integer',
            'max_stock'    => 'nullable|integer',
            'updated_by'   => 'nullable|integer',
        ]);

        $inventory = Inventory::withTrashed()->findOrFail($id);
        $inventory->update($validatedData);

        return redirect()->route('inventories.index')->with('success', 'Inventory berhasil diperbarui.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventories.index')->with('success', 'Inventory berhasil dihapus.');
    }

    public function trashed()
    {
        $inventories = Inventory::onlyTrashed()->get();
        return Inertia::render('Inventory/Trashed', [
            'inventories' => $inventories,
        ]);
    }

    public function restore($id)
    {
        Inventory::onlyTrashed()->where('id', $id)->restore();
        return redirect()->route('inventories.index')->with('success', 'Inventory berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        Inventory::onlyTrashed()->where('id', $id)->forceDelete();
        return redirect()->route('inventories.index')->with('success', 'Inventory berhasil dihapus permanen.');
    }
}
