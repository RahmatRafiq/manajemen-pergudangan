<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use SoftDeletes;

    protected $table = 'regions';

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}
