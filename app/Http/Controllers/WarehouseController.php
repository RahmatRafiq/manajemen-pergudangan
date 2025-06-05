<?php
namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $filter     = $request->query('filter', 'active');
        $warehouses = match ($filter) {
            'trashed' => Warehouse::onlyTrashed()->get(),
            'all' => Warehouse::withTrashed()->get(),
            default => Warehouse::all(),
        };

        return Inertia::render('Warehouse/Index', [
            'warehouses' => $warehouses,
            'filter'     => $filter,
        ]);
    }

    public function json(Request $request)
    {
        $search = $request->input('search.value', '');
        $filter = $request->input('filter', 'active');

        $query = match ($filter) {
            'trashed' => Warehouse::onlyTrashed(),
            'all' => Warehouse::withTrashed(),
            default => Warehouse::query(),
        };

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('manager', 'like', "%{$search}%");
            });
        }

        $columns = ['id', 'reference', 'name', 'address', 'phone', 'manager', 'created_at', 'updated_at'];
        if ($request->filled('order')) {
            $orderColumn = $columns[$request->order[0]['column']] ?? 'id';
            $query->orderBy($orderColumn, $request->order[0]['dir']);
        }

        $data = DataTable::paginate($query, $request);

        $data['data'] = collect($data['data'])->map(function ($warehouse) {
            return [
                'id'        => $warehouse->id,
                'reference' => $warehouse->reference,
                'name'      => $warehouse->name,
                'address'   => $warehouse->address,
                'phone'     => $warehouse->phone,
                'manager'   => $warehouse->manager,
                'trashed'   => $warehouse->trashed(),
                'actions'   => '',
            ];
        });

        return response()->json($data);
    }

    public function create()
    {
        return Inertia::render('Warehouse/Form');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'region_id' => 'required|integer',
            'name'      => 'required|string|max:255',
            'address'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:50',
            'manager'   => 'nullable|string|max:255',
        ]);

        Warehouse::create($validatedData);

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dibuat.');
    }

    public function edit($id)
    {
        $warehouse = Warehouse::withTrashed()->findOrFail($id);
        return Inertia::render('Warehouse/Form', [
            'warehouse' => $warehouse,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'region_id' => 'required|integer',
            'name'      => 'required|string|max:255',
            'address'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:50',
            'manager'   => 'nullable|string|max:255',
        ]);

        $warehouse = Warehouse::withTrashed()->findOrFail($id);
        $warehouse->update($validatedData);

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dihapus.');
    }

    public function trashed()
    {
        $warehouses = Warehouse::onlyTrashed()->get();
        return Inertia::render('Warehouse/Trashed', [
            'warehouses' => $warehouses,
        ]);
    }

    public function restore($id)
    {
        Warehouse::onlyTrashed()->where('id', $id)->restore();
        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        Warehouse::onlyTrashed()->where('id', $id)->forceDelete();
        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dihapus permanen.');
    }
}
