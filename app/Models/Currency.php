<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'currency_name',
        'code',
        'symbol',
        'thousand_separator',
        'decimal_separator',
        'exchange_rate',
    ];

    /*protected $casts = [
        'standard_rate' => 'decimal:2',
        'min_duration_minutes' => 'integer',
        'max_duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];*/
}
