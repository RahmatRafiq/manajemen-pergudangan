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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->reference)) {
                $transaction->reference = static::generateReference($transaction->type);
            }
        });
    }

    public static function generateReference($type)
    {
        $prefix = match($type) {
            'in' => 'SIN',
            'out' => 'SOUT',
            'adjustment' => 'SADJ',
            'transfer' => 'STRF',
            default => 'STK',
        };

        $today = now()->format('Ymd');
        $count = static::whereDate('created_at', today())
            ->where('type', $type)
            ->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $today, $count);
    }

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