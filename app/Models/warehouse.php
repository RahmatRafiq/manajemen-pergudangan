<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'region_id',
        'reference',
        'name',
        'address',
        'phone',
        'manager',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($warehouse) {
            if (empty($warehouse->reference)) {
                $warehouse->reference = 'WH-' . strtoupper(uniqid());
            }
        });
    }
}
