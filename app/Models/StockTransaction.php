<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'inventory_id',
        'type',
        'quantity',
        'reference',
        'description',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $dates = ['deleted_at'];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}