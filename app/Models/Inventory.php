<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
}
