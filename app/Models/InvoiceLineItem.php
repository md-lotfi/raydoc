<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'therapy_session_id',
        'billing_code_id',
        'service_description',
        'unit_price',
        'units',
        'subtotal',
        'metadata',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'units' => 'integer',
        'subtotal' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the invoice that this line item belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the specific session that generated this charge.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(TherapySession::class, 'therapy_session_id');
    }

    /**
     * Get the billing code used for this charge.
     */
    public function billingCode(): BelongsTo
    {
        return $this->belongsTo(BillingCode::class);
    }
}
