<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingCode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'standard_rate',
        'min_duration_minutes',
        'max_duration_minutes',
        'is_active',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Casts the decimal from the database to a float with 2 decimal precision in PHP
        'standard_rate' => 'decimal:2',
        'min_duration_minutes' => 'integer',
        'max_duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the invoice line items that use this billing code.
     */
    public function lineItems()
    {
        return $this->hasMany(InvoiceLineItem::class);
    }
}
