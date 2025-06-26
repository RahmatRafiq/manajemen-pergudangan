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
    
    /**
     * Check if current stock is below minimum threshold
     */
    public function isLowStock(): bool
    {
        return !is_null($this->min_stock) && $this->quantity <= $this->min_stock;
    }

    /**
     * Check if current stock exceeds maximum threshold
     */
    public function isOverstock(): bool
    {
        return !is_null($this->max_stock) && $this->quantity >= $this->max_stock;
    }

    /**
     * Check if current stock needs alert
     */
    public function needsStockAlert(): bool
    {
        return $this->isLowStock() || $this->isOverstock();
    }

    /**
     * Get stock status
     */
    public function getStockStatus(): string
    {
        if ($this->isLowStock()) {
            return 'low';
        } elseif ($this->isOverstock()) {
            return 'high';
        }
        return 'normal';
    }

    /**
     * Get available stock (quantity - reserved)
     */
    public function getAvailableStock(): int
    {
        return $this->quantity - $this->reserved;
    }

    /**
     * Scope for low stock items
     */
    public function scopeLowStock($query)
    {
        return $query->whereNotNull('min_stock')
                    ->whereColumn('quantity', '<=', 'min_stock');
    }

    /**
     * Scope for overstock items
     */
    public function scopeOverstock($query)
    {
        return $query->whereNotNull('max_stock')
                    ->whereColumn('quantity', '>=', 'max_stock');
    }

    /**
     * Scope for items needing attention
     */
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
        
        return static::select([
                'inventories.product_id',
                'inventories.warehouse_id',
                DB::raw('SUM(inventories.quantity) as total_quantity'),
                DB::raw('COALESCE(SUM(ABS(stock_transactions.quantity)), 0) as total_movement'),
                DB::raw('COUNT(stock_transactions.id) as transaction_count'),
                DB::raw('CASE 
                    WHEN COALESCE(SUM(ABS(stock_transactions.quantity)), 0) = 0 THEN "no_movement"
                    WHEN COALESCE(SUM(ABS(stock_transactions.quantity)), 0) < 10 THEN "low_movement"
                    WHEN COALESCE(SUM(ABS(stock_transactions.quantity)), 0) < 50 THEN "medium_movement"
                    ELSE "high_movement"
                END as movement_category')
            ])
            ->leftJoin('stock_transactions', function($join) use ($dateRange) {
                $join->on('inventories.id', '=', 'stock_transactions.inventory_id')
                     ->whereBetween('stock_transactions.created_at', $dateRange)
                     ->whereNull('stock_transactions.deleted_at');
            })
            ->groupBy('inventories.product_id', 'inventories.warehouse_id')
            ->orderBy('total_movement', 'asc') // Sort by movement (least active first)
            ->orderBy('total_quantity', 'desc')
            ->with(['product', 'warehouse'])
            ->get()
            ->map(function($item) {
                // Calculate movement ratio (movement per stock)
                $item->movement_ratio = $item->total_quantity > 0 
                    ? round($item->total_movement / $item->total_quantity, 2) 
                    : 0;
                    
                // Add recommendation
                $item->recommendation = self::getRecommendation($item);
                
                return $item;
            });
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
        if ($item->total_movement == 0) {
            return [
                'status' => 'danger',
                'text' => 'Tidak ada pergerakan - pertimbangkan untuk tidak menambah stock bulan depan',
                'action' => 'stop_reorder'
            ];
        }
        
        if ($item->movement_ratio < 0.1) {
            return [
                'status' => 'warning', 
                'text' => 'Pergerakan sangat lambat - kurangi pesanan bulan depan',
                'action' => 'reduce_reorder'
            ];
        }
        
        if ($item->movement_ratio < 0.5) {
            return [
                'status' => 'info',
                'text' => 'Pergerakan normal - pertahankan level stock',
                'action' => 'maintain'
            ];
        }
        
        return [
            'status' => 'success',
            'text' => 'Pergerakan tinggi - stock berputar dengan baik',
            'action' => 'increase_if_needed'
        ];
    }
    
    /**
     * Get movement statistics for dashboard
     */
    public static function getMovementStatistics($period = 'month')
    {
        $inventories = self::getSortedGlobalWithMovement($period);
        
        return [
            'total_products' => $inventories->count(),
            'no_movement' => $inventories->where('movement_category', 'no_movement')->count(),
            'low_movement' => $inventories->where('movement_category', 'low_movement')->count(),
            'medium_movement' => $inventories->where('movement_category', 'medium_movement')->count(),
            'high_movement' => $inventories->where('movement_category', 'high_movement')->count(),
            'avg_movement_ratio' => $inventories->avg('movement_ratio'),
            'slow_movers' => $inventories->where('movement_category', 'no_movement')
                                       ->orWhere('movement_category', 'low_movement')
                                       ->count(),
            'recommendations' => [
                'stop_reorder' => $inventories->where('recommendation.action', 'stop_reorder')->count(),
                'reduce_reorder' => $inventories->where('recommendation.action', 'reduce_reorder')->count(),
                'maintain' => $inventories->where('recommendation.action', 'maintain')->count(),
                'increase_if_needed' => $inventories->where('recommendation.action', 'increase_if_needed')->count(),
            ]
        ];
    }
}
