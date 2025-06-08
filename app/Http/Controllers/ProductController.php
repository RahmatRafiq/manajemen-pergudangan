<?php
namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filter       = $request->query('filter', 'active');
        $warehouseIds = auth()->user()->warehouses->pluck('id');
        $productIds   = Inventory::whereIn('warehouse_id', $warehouseIds)->pluck('product_id')->unique();

        $products = match ($filter) {
            'trashed' => Product::onlyTrashed()->whereIn('id', $productIds)->with(['category', 'creator'])->get(),
            'all' => Product::withTrashed()->whereIn('id', $productIds)->with(['category', 'creator'])->get(),
            default => Product::whereIn('id', $productIds)->with(['category', 'creator'])->get(),
        };

        return Inertia::render('Product/Index', [
            'products'   => $products,
            'filter'     => $filter,
            'categories' => Category::all(),
        ]);
        }

        public function json(Request $request)
        {
        $search       = $request->input('search.value', '');
        $filter       = $request->input('filter', 'active');
        $warehouseIds = auth()->user()->warehouses->pluck('id');
        $productIds   = Inventory::whereIn('warehouse_id', $warehouseIds)->pluck('product_id')->unique();

        $query = match ($filter) {
            'trashed' => Product::onlyTrashed()->whereIn('id', $productIds)->with(['category', 'creator']),
            'all' => Product::withTrashed()->whereIn('id', $productIds)->with(['category', 'creator']),
            default => Product::whereIn('id', $productIds)->with(['category', 'creator']),
        };

        if ($search) {
            $query->where(function ($q) use ($search) {
            $q->where('sku', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $columns = ['id', 'sku', 'name', 'category_id', 'unit', 'created_at', 'updated_at'];
        if ($request->filled('order')) {
            $orderColumn = $columns[$request->order[0]['column']] ?? 'id';
            $query->orderBy($orderColumn, $request->order[0]['dir']);
        }

        $data = DataTable::paginate($query, $request);

        $data['data'] = collect($data['data'])->map(function ($product) {
            return [
            'id'         => $product->id,
            'sku'        => $product->sku,
            'name'       => $product->name,
            'category'   => $product->category?->name,
            'unit'       => $product->unit,
            'created_by' => $product->creator?->name,
            'trashed'    => $product->trashed(),
            'actions'    => '',
            ];
        });

        return response()->json($data);
        }

        public function create()
        {
        $categories = Category::all();
        $warehouses = auth()->user()->warehouses;

        return Inertia::render('Product/Form', [
            'categories'       => $categories,
            'categoryOptions'  => $categories->map(fn($c) => ['value' => $c->id, 'label' => $c->name]),
            'warehouseOptions' => $warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name]),
        ]);
        }

        public function store(Request $request)
        {
        $validatedData = $request->validate([
            'name'         => 'required|string|max:255',
            'category_id'  => 'required|exists:categories,id',
            'description'  => 'nullable|string',
            'unit'         => 'required|string|max:50',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        if (! auth()->user()->warehouses->pluck('id')->contains($request->warehouse_id)) {
            abort(403, 'Unauthorized');
        }

        $validatedData['created_by'] = auth()->id();

        $product = Product::create($validatedData);

        Inventory::create([
            'warehouse_id' => $request->warehouse_id,
            'product_id'   => $product->id,
            'quantity'     => 0,
            'reserved'     => 0,
            'min_stock'    => 0,
            'max_stock'    => 0,
        ]);

        return redirect()->route('product.index')->with('success', 'Produk berhasil dibuat.');
        }

        public function edit($id)
        {
        $product    = Product::withTrashed()->findOrFail($id);
        $categories = Category::all();
        $warehouses = auth()->user()->warehouses;

        return Inertia::render('Product/Form', [
            'product'          => $product,
            'categories'       => $categories,
            'categoryOptions'  => $categories->map(fn($c) => ['value' => $c->id, 'label' => $c->name]),
            'warehouseOptions' => $warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name]),
        ]);
        }

        public function update(Request $request, $id)
        {
        $validatedData = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'unit'        => 'required|string|max:50',
        ]);

        $product = Product::withTrashed()->findOrFail($id);

        $product->update($validatedData);

        return redirect()->route('product.index')->with('success', 'Produk berhasil diperbarui.');
        }

        public function destroy(Product $product)
        {
        $product->delete();
        return redirect()->route('product.index')->with('success', 'Produk berhasil dihapus.');
        }

        public function trashed()
        {
        $warehouseIds = auth()->user()->warehouses->pluck('id');
        $productIds   = Inventory::whereIn('warehouse_id', $warehouseIds)->pluck('product_id')->unique();

        $products = Product::onlyTrashed()->whereIn('id', $productIds)->with(['category', 'creator'])->get();
        return Inertia::render('Product/Trashed', [
            'products' => $products,
        ]);
    }

    public function restore($id)
    {
        Product::onlyTrashed()->where('id', $id)->restore();
        return redirect()->route('product.index')->with('success', 'Produk berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        Product::onlyTrashed()->where('id', $id)->forceDelete();
        return redirect()->route('product.index')->with('success', 'Produk berhasil dihapus permanen.');
    }
}
