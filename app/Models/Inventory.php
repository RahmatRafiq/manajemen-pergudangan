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
        'quantity'  => 'integer',
        'reserved'  => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
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
}
