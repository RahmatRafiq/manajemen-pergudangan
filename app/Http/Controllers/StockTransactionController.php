<?php
namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockTransactionController extends Controller
{
    protected function getUserWarehouseIds()
    {
        return auth()->user()->warehouses()->pluck('warehouses.id');
    }

    public function index(Request $request)
    {
        $userWarehouseIds = $this->getUserWarehouseIds();
        $filter           = $request->query('filter', 'active');
        $transactions     = match ($filter) {
            'trashed' => StockTransaction::onlyTrashed()
                ->whereHas('inventory', fn($q) => $q->whereIn('warehouse_id', $userWarehouseIds))
                ->with(['inventory.product', 'inventory.warehouse', 'creator', 'approver'])->get(),
            'all' => StockTransaction::withTrashed()
                ->whereHas('inventory', fn($q) => $q->whereIn('warehouse_id', $userWarehouseIds))
                ->with(['inventory.product', 'inventory.warehouse', 'creator', 'approver'])->get(),
            default => StockTransaction::whereHas('inventory', fn($q) => $q->whereIn('warehouse_id', $userWarehouseIds))
                ->with(['inventory.product', 'inventory.warehouse', 'creator', 'approver'])->get(),
        };

        return Inertia::render('StockTransaction/Index', [
            'transactions' => $transactions,
            'filter'       => $filter,
            'products'     => Product::all(),
            'warehouses'   => Warehouse::whereIn('id', $userWarehouseIds)->get(),
        ]);
    }

    public function json(Request $request)
    {
        $userWarehouseIds = $this->getUserWarehouseIds();
        $search           = $request->input('search.value', '');
        $filter           = $request->input('filter', 'active');

        $query = match ($filter) {
            'trashed' => StockTransaction::onlyTrashed()
                ->whereHas('inventory', fn($q) => $q->whereIn('warehouse_id', $userWarehouseIds))
                ->with(['inventory.product', 'inventory.warehouse', 'creator', 'approver']),
            'all' => StockTransaction::withTrashed()
                ->whereHas('inventory', fn($q) => $q->whereIn('warehouse_id', $userWarehouseIds))
                ->with(['inventory.product', 'inventory.warehouse', 'creator', 'approver']),
            default => StockTransaction::whereHas('inventory', fn($q) => $q->whereIn('warehouse_id', $userWarehouseIds))
                ->with(['inventory.product', 'inventory.warehouse', 'creator', 'approver']),
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

        // Kolom harus sesuai urutan DataTables frontend: [detail, id, type, warehouse, product, sku, quantity, reference, created_at, actions]
        $columns = [null, 'id', 'type', 'warehouse', 'product', 'sku', 'quantity', 'reference', 'created_at', null];

        // Mapping index kolom ke field database/relasi
        $columnMap = [
            1 => 'id',
            2 => 'type',
            3 => 'warehouse',
            4 => 'product',
            5 => 'sku',
            6 => 'quantity',
            7 => 'reference',
            8 => 'created_at',
        ];
        if ($request->filled('order')) {
            $orderIdx = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];
            $orderColumn = $columnMap[$orderIdx] ?? 'id';
            // Sorting relasi
            if ($orderColumn === 'warehouse') {
                $query->join('inventories as inv', 'stock_transactions.inventory_id', '=', 'inv.id')
                      ->join('warehouses as w', 'inv.warehouse_id', '=', 'w.id')
                      ->orderBy('w.name', $orderDir)
                      ->select('stock_transactions.*');
            } elseif ($orderColumn === 'product') {
                $query->join('inventories as inv', 'stock_transactions.inventory_id', '=', 'inv.id')
                      ->join('products as p', 'inv.product_id', '=', 'p.id')
                      ->orderBy('p.name', $orderDir)
                      ->select('stock_transactions.*');
            } elseif ($orderColumn === 'sku') {
                $query->join('inventories as inv', 'stock_transactions.inventory_id', '=', 'inv.id')
                      ->join('products as p', 'inv.product_id', '=', 'p.id')
                      ->orderBy('p.sku', $orderDir)
                      ->select('stock_transactions.*');
            } else {
                $query->orderBy($orderColumn, $orderDir);
            }
        }

        $data = DataTable::paginate($query, $request);

        $data['data'] = collect($data['data'])->map(function ($trx) {
            $stockBefore = null;
            $stockAfter = null;
            if ($trx->inventory_id && $trx->inventory) {
                $inventory = $trx->inventory;
                $afterTrx = $inventory->stockTransactions()
                    ->where(function($q) use ($trx) {
                        $q->where('created_at', '>', $trx->created_at)
                          ->orWhere(function($q2) use ($trx) {
                              $q2->where('created_at', $trx->created_at)
                                 ->where('id', '>', $trx->id);
                          });
                    })
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->get();
                $stock = $inventory->quantity;
                foreach ($afterTrx as $t) {
                    if ($t->type === 'in') {
                        $stock -= $t->quantity;
                    } elseif ($t->type === 'out') {
                        $stock += $t->quantity;
                    }
                }
                $stockAfter = $stock;
                if ($trx->type === 'in') {
                    $stockBefore = $stockAfter - $trx->quantity;
                } elseif ($trx->type === 'out') {
                    $stockBefore = $stockAfter + $trx->quantity;
                } else {
                    $stockBefore = $stockAfter;
                }
            }
            return [
                'id'          => $trx->id,
                'type'        => $trx->type,
                'warehouse'   => $trx->inventory?->warehouse?->name,
                'product'     => $trx->inventory?->product?->name,
                'sku'         => $trx->inventory?->product?->sku,
                'quantity'    => $trx->quantity,
                'reference'   => $trx->reference,
                'description' => $trx->description,
                'created_at'  => $trx->created_at,
                'approved_at' => $trx->approved_at,
                'trashed'     => $trx->trashed(),
                'stock_before'=> $stockBefore,
                'stock_after' => $stockAfter,
                'actions'     => '',
            ];
        });

        return response()->json($data);
    }

    public function create()
    {
        $userWarehouseIds = $this->getUserWarehouseIds();
        $inventories      = Inventory::with(['product', 'warehouse'])
            ->whereIn('warehouse_id', $userWarehouseIds)
            ->get();

        return Inertia::render('StockTransaction/Form', [
            'inventories' => $inventories,
        ]);
    }

    public function store(Request $request)
    {
        $userWarehouseIds = $this->getUserWarehouseIds();
        $validated        = $request->validate([
            'inventory_id' => [
                'required',
                function ($attribute, $value, $fail) use ($userWarehouseIds) {
                    $inventory = Inventory::find($value);
                    if (! $inventory || ! $userWarehouseIds->contains($inventory->warehouse_id)) {
                        $fail('Anda tidak berhak melakukan transaksi pada inventory ini.');
                    }
                },
            ],
            'type'         => 'required|in:in,out,adjustment,transfer',
            'quantity'     => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->type === 'out') {
                        $inventory = Inventory::find($request->inventory_id);
                        if ($inventory && $value > $inventory->quantity) {
                            $fail("Quantity tidak boleh melebihi stock yang tersedia ({$inventory->quantity}).");
                        }
                    }
                },
            ],
            'description'  => 'nullable|string',
        ]);
        $validated['created_by'] = auth()->id();

        $trx = StockTransaction::create($validated);

        $inventory = $trx->inventory;
        if ($trx->type === 'in') {
            $inventory->increment('quantity', $trx->quantity);
        } elseif ($trx->type === 'out') {
            $inventory->decrement('quantity', $trx->quantity);
        }

        return redirect()->route('stock-transaction.index')->with('success', 'Transaksi stok berhasil dicatat.');
    }

    public function edit($id)
    {
        $userWarehouseIds = $this->getUserWarehouseIds();
        $trx              = StockTransaction::withTrashed()->findOrFail($id);
        $inventories      = Inventory::with(['product', 'warehouse'])
            ->whereIn('warehouse_id', $userWarehouseIds)
            ->get();

        return Inertia::render('StockTransaction/Form', [
            'transaction' => $trx,
            'inventories' => $inventories,
        ]);
    }

    public function update(Request $request, $id)
    {
        $userWarehouseIds = $this->getUserWarehouseIds();
        $validated        = $request->validate([
            'inventory_id' => [
                'required',
                function ($attribute, $value, $fail) use ($userWarehouseIds) {
                    $inventory = Inventory::find($value);
                    if (! $inventory || ! $userWarehouseIds->contains($inventory->warehouse_id)) {
                        $fail('Anda tidak berhak melakukan transaksi pada inventory ini.');
                    }
                },
            ],
            'type'         => 'required|in:in,out,adjustment,transfer',
            'quantity'     => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($request, $id) {
                    if ($request->type === 'out') {
                        $currentTrx = StockTransaction::find($id);
                        $inventory = Inventory::find($request->inventory_id);
                        if ($inventory && $currentTrx) {
                            $availableStock = $inventory->quantity;
                            if ($currentTrx->type === 'out') {
                                $availableStock += $currentTrx->quantity;
                            } elseif ($currentTrx->type === 'in') {
                                $availableStock -= $currentTrx->quantity;
                            }
                            if ($value > $availableStock) {
                                $fail("Quantity tidak boleh melebihi stock yang tersedia ({$availableStock}).");
                            }
                        }
                    }
                },
            ],
            'description'  => 'nullable|string',
        ]);

        $trx = StockTransaction::withTrashed()->findOrFail($id);

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
        $userWarehouseIds = $this->getUserWarehouseIds();
        $transactions     = StockTransaction::onlyTrashed()
            ->whereHas('inventory', fn($q) => $q->whereIn('warehouse_id', $userWarehouseIds))
            ->with(['inventory.product', 'inventory.warehouse', 'creator', 'approver'])->get();
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
