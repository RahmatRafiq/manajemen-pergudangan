<?php
namespace App\Models;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    // Mass assignable attributes (optional, but recommended)
    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'description',
        'unit',
        'created_by',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->sku)) {
                $last         = static::withTrashed()->latest('id')->first();
                $next         = $last ? $last->id + 1 : 1;
                $product->sku = sprintf('PRD-%04d', $next);
            }
        });
    }
}
