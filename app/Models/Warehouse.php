<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

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

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_warehouses');
    }

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
