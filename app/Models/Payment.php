<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_id',
        'status',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Get the user (admin/staff) who recorded this payment.
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the invoice this payment was applied against.
     */
    public function invoice(): BelongsTo
    {
        // invoice_id is nullable because some payments (e.g., retainer) might not be tied to a specific invoice immediately
        return $this->belongsTo(Invoice::class);
    }
}
