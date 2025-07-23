<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Inventory extends Model
{
    use SoftDeletes, LogsActivity;
    protected $table = 'inventories';

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class, 'inventory_id');
    }

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity',
        'reserved',
        'min_stock',
        'max_stock',
        'updated_by',
    ];
    protected $casts = [
        'quantity'   => 'integer',
        'reserved'   => 'integer',
        'min_stock'  => 'integer',
        'max_stock'  => 'integer',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public static function getSortedByWarehouse($warehouseId)
    {
        return static::where('warehouse_id', $warehouseId)
            ->orderBy('quantity', 'desc')
            ->with('product')
            ->get();
    }
    public static function getSortedGlobal()
    {
        return static::select('product_id')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->with('product')
            ->get();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['warehouse_id', 'product_id', 'quantity', 'reserved', 'min_stock', 'max_stock', 'updated_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Inventory {$eventName}");
    }
    
    public function isLowStock(): bool
    {
        return !is_null($this->min_stock) && $this->quantity <= $this->min_stock;
    }

    public function isOverstock(): bool
    {
        return !is_null($this->max_stock) && $this->quantity >= $this->max_stock;
    }

    public function needsStockAlert(): bool
    {
        return $this->isLowStock() || $this->isOverstock();
    }

    public function getStockStatus(): string
    {
        if ($this->isLowStock()) {
            return 'low';
        } elseif ($this->isOverstock()) {
            return 'high';
        }
        return 'normal';
    }

    public function getAvailableStock(): int
    {
        return $this->quantity - $this->reserved;
    }

    public function scopeLowStock($query)
    {
        return $query->whereNotNull('min_stock')
                    ->whereColumn('quantity', '<=', 'min_stock');
    }

    public function scopeOverstock($query)
    {
        return $query->whereNotNull('max_stock')
                    ->whereColumn('quantity', '>=', 'max_stock');
    }

    public function scopeNeedsAttention($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('min_stock')->whereColumn('quantity', '<=', 'min_stock');
        })->orWhere(function ($q) {
            $q->whereNotNull('max_stock')->whereColumn('quantity', '>=', 'max_stock');
        });
    }
    
    public static function getSortedGlobalWithMovement($period = 'month')
    {
        $dateRange = self::getDateRange($period);
        
        $perWarehouseData = static::select([
                'inventories.id',
                'inventories.product_id',
                'inventories.warehouse_id',
                'inventories.quantity',
                DB::raw('COALESCE(SUM(ABS(stock_transactions.quantity)), 0) as warehouse_movement'),
                DB::raw('COUNT(stock_transactions.id) as warehouse_transaction_count')
            ])
            ->leftJoin('stock_transactions', function($join) use ($dateRange) {
                $join->on('inventories.id', '=', 'stock_transactions.inventory_id')
                     ->whereBetween('stock_transactions.created_at', $dateRange)
                     ->whereNull('stock_transactions.deleted_at');
            })
            ->whereNull('inventories.deleted_at')
            ->groupBy('inventories.id', 'inventories.product_id', 'inventories.warehouse_id', 'inventories.quantity')
            ->with(['product', 'warehouse'])
            ->get();
            
        $globalData = $perWarehouseData->groupBy('product_id')->map(function($productInventories, $productId) {
            $totalQuantity = $productInventories->sum('quantity');
            $totalMovement = $productInventories->sum('warehouse_movement');
            $totalTransactions = $productInventories->sum('warehouse_transaction_count');
            $product = $productInventories->first()->product;
            
            $movementCategory = 'no_movement';
            if ($totalMovement >= 100) {
                $movementCategory = 'high_movement';
            } elseif ($totalMovement >= 20) {
                $movementCategory = 'medium_movement';
            } elseif ($totalMovement > 0) {
                $movementCategory = 'low_movement';
            }
            
            return (object) [
                'product_id' => $productId,
                'total_quantity' => $totalQuantity,
                'total_movement' => $totalMovement,
                'transaction_count' => $totalTransactions,
                'movement_category' => $movementCategory,
                'product' => $product,
                'warehouses' => $productInventories->pluck('warehouse')->unique(),
                'warehouse_count' => $productInventories->count(),
                'recommendation' => self::getRecommendation((object)[
                    'total_movement' => $totalMovement,
                    'total_quantity' => $totalQuantity,
                ])
            ];
        })->sortByDesc('total_movement')->values();
        
        return $globalData;
    }
    
    protected static function getDateRange($period)
    {
        $now = now();
        
        switch ($period) {
            case 'week':
                return [$now->startOfWeek()->toDateString(), $now->endOfWeek()->toDateString()];
            case 'month':
                return [$now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString()];
            case 'year':
                return [$now->startOfYear()->toDateString(), $now->endOfYear()->toDateString()];
            default:
                return [$now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString()];
        }
    }
    
    protected static function getRecommendation($item)
    {
        $totalMovement = $item->total_movement;
        $totalStock = $item->total_quantity;
        
        if ($totalMovement == 0) {
            return [
                'status' => 'danger',
                'text' => 'Tidak ada pergerakan - pertimbangkan untuk tidak menambah stock bulan depan',
                'action' => 'stop_reorder'
            ];
        }
        
        if ($totalMovement >= 100) {
            return [
                'status' => 'success',
                'text' => 'Pergerakan tinggi - stock berputar dengan baik',
                'action' => 'maintain_or_increase'
            ];
        }
        
        if ($totalMovement >= 20) {
            return [
                'status' => 'info',
                'text' => 'Pergerakan sedang - pertahankan level stock',
                'action' => 'maintain'
            ];
        }
        
        if ($totalMovement > 0) {
            return [
                'status' => 'warning',
                'text' => 'Pergerakan rendah - kurangi pesanan bulan depan',
                'action' => 'reduce_reorder'
            ];
        }
        
        return [
            'status' => 'info',
            'text' => 'Pergerakan normal - pertahankan level stock',
            'action' => 'maintain'
        ];
    }
    
    public static function getMovementStatistics($period = 'month')
    {
        $inventories = self::getSortedGlobalWithMovement($period);
        
        return [
            'total_products' => $inventories->count(),
            'no_movement' => $inventories->where('movement_category', 'no_movement')->count(),
            'low_movement' => $inventories->where('movement_category', 'low_movement')->count(),
            'medium_movement' => $inventories->where('movement_category', 'medium_movement')->count(),
            'high_movement' => $inventories->where('movement_category', 'high_movement')->count(),
            'slow_movers' => $inventories->whereIn('movement_category', ['no_movement', 'low_movement'])->count(),
            'recommendations' => [
                'stop_reorder' => $inventories->where('recommendation.action', 'stop_reorder')->count(),
                'reduce_reorder' => $inventories->where('recommendation.action', 'reduce_reorder')->count(),
                'maintain' => $inventories->where('recommendation.action', 'maintain')->count(),
                'increase_if_needed' => $inventories->where('recommendation.action', 'increase_if_needed')->count(),
            ]
        ];
    }
}
