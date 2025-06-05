<?php
namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $filter  = $request->query('filter', 'active');
        $regions = match ($filter) {
            'trashed' => Region::onlyTrashed()->get(),
            'all' => Region::withTrashed()->get(),
            default => Region::all(),
        };

        return Inertia::render('Region/Index', [
            'regions' => $regions,
            'filter'  => $filter,
        ]);
    }

    public function json(Request $request)
    {
        $search = $request->input('search.value', '');
        $filter = $request->input('filter', 'active');

        $query = match ($filter) {
            'trashed' => Region::onlyTrashed(),
            'all' => Region::withTrashed(),
            default => Region::query(),
        };

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $columns = ['id', 'code', 'name', 'description', 'created_at', 'updated_at'];
        if ($request->filled('order')) {
            $orderColumn = $columns[$request->order[0]['column']] ?? 'id';
            $query->orderBy($orderColumn, $request->order[0]['dir']);
        }

        $data = DataTable::paginate($query, $request);

        $data['data'] = collect($data['data'])->map(function ($region) {
            return [
                'id'          => $region->id,
                'code'        => $region->code,
                'name'        => $region->name,
                'description' => $region->description,
                'trashed'     => $region->trashed(),
                'actions'     => '',
            ];
        });

        return response()->json($data);
    }

    public function create()
    {
        return Inertia::render('Region/Form');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code'        => 'required|string|max:50|unique:regions,code',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        Region::create($validatedData);

        return redirect()->route('regions.index')->with('success', 'Region berhasil dibuat.');
    }

    public function edit($id)
    {
        $region = Region::withTrashed()->findOrFail($id);
        return Inertia::render('Region/Form', [
            'region' => $region,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'code'        => 'required|string|max:50|unique:regions,code,' . $id,
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $region = Region::withTrashed()->findOrFail($id);
        $region->update($validatedData);

        return redirect()->route('regions.index')->with('success', 'Region berhasil diperbarui.');
    }

    public function destroy(Region $region)
    {
        $region->delete();
        return redirect()->route('regions.index')->with('success', 'Region berhasil dihapus.');
    }

    public function trashed()
    {
        $regions = Region::onlyTrashed()->get();
        return Inertia::render('Region/Trashed', [
            'regions' => $regions,
        ]);
    }

    public function restore($id)
    {
        Region::onlyTrashed()->where('id', $id)->restore();
        return redirect()->route('regions.index')->with('success', 'Region berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        Region::onlyTrashed()->where('id', $id)->forceDelete();
        return redirect()->route('regions.index')->with('success', 'Region berhasil dihapus permanen.');
    }
}
