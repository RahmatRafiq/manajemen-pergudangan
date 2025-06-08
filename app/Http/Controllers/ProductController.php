<?php
namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filter   = $request->query('filter', 'active');
        $products = match ($filter) {
            'trashed' => Product::onlyTrashed()->with(['category', 'creator'])->get(),
            'all' => Product::withTrashed()->with(['category', 'creator'])->get(),
            default => Product::with(['category', 'creator'])->get(),
        };

        return Inertia::render('Product/Index', [
            'products'   => $products,
            'filter'     => $filter,
            'categories' => Category::all(),
        ]);
    }

    public function json(Request $request)
    {
        $search = $request->input('search.value', '');
        $filter = $request->input('filter', 'active');

        $query = match ($filter) {
            'trashed' => Product::onlyTrashed()->with(['category', 'creator']),
            'all' => Product::withTrashed()->with(['category', 'creator']),
            default => Product::with(['category', 'creator']),
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
        return Inertia::render('Product/Form', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'sku'         => 'required|string|max:100|unique:products',
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'unit'        => 'required|string|max:50',
        ]);

        $validatedData['created_by'] = auth()->id();

        Product::create($validatedData);

        return redirect()->route('products.index')->with('success', 'Produk berhasil dibuat.');
    }

    public function edit($id)
    {
        $product    = Product::withTrashed()->findOrFail($id);
        $categories = Category::all();
        return Inertia::render('Product/Form', [
            'product'    => $product,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'sku'         => 'required|string|max:100|unique:products,sku,' . $id,
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'unit'        => 'required|string|max:50',
        ]);

        $product = Product::withTrashed()->findOrFail($id);
        $product->update($validatedData);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function trashed()
    {
        $products = Product::onlyTrashed()->with(['category', 'creator'])->get();
        return Inertia::render('Product/Trashed', [
            'products' => $products,
        ]);
    }

    public function restore($id)
    {
        Product::onlyTrashed()->where('id', $id)->restore();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        Product::onlyTrashed()->where('id', $id)->forceDelete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus permanen.');
    }
}
