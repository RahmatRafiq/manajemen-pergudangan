<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;
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
}
