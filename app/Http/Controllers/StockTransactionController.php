<?php

namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Models\StockTransaction;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockTransactionController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $transactions = match ($filter) {
            'trashed' => StockTransaction::onlyTrashed()->with(['inventory.product', 'inventory.warehouse', 'creator', 'approver'])->get(),
            'all'     => StockTransaction::withTrashed()->with(['inventory.product', 'inventory.warehouse', 'creator', 'approver'])->get(),
            default   => StockTransaction::with(['inventory.product', 'inventory.warehouse', 'creator', 'approver'])->get(),
        };

        return Inertia::render('StockTransaction/Index', [
            'transactions' => $transactions,
            'filter'       => $filter,
            'products'     => Product::all(),
            'warehouses'   => Warehouse::all(),
        ]);
    }

    public function json(Request $request)
    {
        $search = $request->input('search.value', '');
        $filter = $request->input('filter', 'all');

        $query = match ($filter) {
            'trashed' => StockTransaction::onlyTrashed()->with(['inventory.product', 'inventory.warehouse', 'creator', 'approver']),
            'all'     => StockTransaction::withTrashed()->with(['inventory.product', 'inventory.warehouse', 'creator', 'approver']),
            default   => StockTransaction::with(['inventory.product', 'inventory.warehouse', 'creator', 'approver']),
        };

        if ($search) {
            $query->whereHas('inventory.product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            })->orWhereHas('inventory.warehouse', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            })->orWhere('reference', 'like', "%{$search}%");
        }

        $columns = ['id', 'type', 'quantity', 'reference', 'description', 'created_by', 'approved_by', 'created_at', 'approved_at'];
        if ($request->filled('order')) {
            $orderColumn = $columns[$request->order[0]['column']] ?? 'id';
            $query->orderBy($orderColumn, $request->order[0]['dir']);
        }

        $data = DataTable::paginate($query, $request);

        $data['data'] = collect($data['data'])->map(function ($trx) {
            return [
                'id'           => $trx->id,
                'type'         => $trx->type,
                'warehouse'    => $trx->inventory?->warehouse?->name,
                'product'      => $trx->inventory?->product?->name,
                'sku'          => $trx->inventory?->product?->sku,
                'quantity'     => $trx->quantity,
                'reference'    => $trx->reference,
                'description'  => $trx->description,
                'created_by'   => $trx->creator?->name,
                'approved_by'  => $trx->approver?->name,
                'created_at'   => $trx->created_at,
                'approved_at'  => $trx->approved_at,
                'trashed'      => $trx->trashed(),
                'actions'      => '',
            ];
        });

        return response()->json($data);
    }

    public function create()
    {
        return Inertia::render('StockTransaction/Form', [
            'inventories' => Inventory::with(['product', 'warehouse'])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'type'         => 'required|in:in,out,adjustment,transfer',
            'quantity'     => 'required|integer|min:1',
            'reference'    => 'nullable|string|max:255',
            'description'  => 'nullable|string',
        ]);
        $validated['created_by'] = auth()->id();

        $trx = StockTransaction::create($validated);

        // Update inventory quantity
        $inventory = $trx->inventory;
        if ($trx->type === 'in') {
            $inventory->increment('quantity', $trx->quantity);
        } elseif ($trx->type === 'out') {
            $inventory->decrement('quantity', $trx->quantity);
        }
        // adjustment/transfer: silakan tambah logic sesuai kebutuhan

        return redirect()->route('stock-transaction.index')->with('success', 'Transaksi stok berhasil dicatat.');
    }

    public function edit($id)
    {
        $trx = StockTransaction::withTrashed()->findOrFail($id);
        return Inertia::render('StockTransaction/Form', [
            'transaction' => $trx,
            'inventories' => Inventory::with(['product', 'warehouse'])->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'type'         => 'required|in:in,out,adjustment,transfer',
            'quantity'     => 'required|integer|min:1',
            'reference'    => 'nullable|string|max:255',
            'description'  => 'nullable|string',
        ]);

        $trx = StockTransaction::withTrashed()->findOrFail($id);

        // Catatan: Untuk update transaksi, Anda perlu logic khusus untuk mengembalikan stok lama sebelum update, jika diizinkan.
        $trx->update($validated);

        return redirect()->route('stock-transaction.index')->with('success', 'Transaksi stok berhasil diperbarui.');
    }

    public function destroy(StockTransaction $stockTransaction)
    {
        $stockTransaction->delete();
        return redirect()->route('stock-transaction.index')->with('success', 'Transaksi stok berhasil dihapus.');
    }

    public function trashed()
    {
        $transactions = StockTransaction::onlyTrashed()->with(['inventory.product', 'inventory.warehouse', 'creator', 'approver'])->get();
        return Inertia::render('StockTransaction/Trashed', [
            'transactions' => $transactions,
        ]);
    }

    public function restore($id)
    {
        StockTransaction::onlyTrashed()->where('id', $id)->restore();
        return redirect()->route('stock-transaction.index')->with('success', 'Transaksi stok berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        StockTransaction::onlyTrashed()->where('id', $id)->forceDelete();
        return redirect()->route('stock-transaction.index')->with('success', 'Transaksi stok berhasil dihapus permanen.');
    }
}