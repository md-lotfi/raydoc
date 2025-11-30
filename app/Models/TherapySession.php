<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TherapySession extends Model
{
    protected $fillable = [
        'user_id',
        'patient_id',
        'scheduled_at',
        'duration_minutes',
        'actual_start_at',
        'actual_end_at',
        'focus_area',
        'notes',
        'homework_assigned',
        'status',
        'cancelled_at',
        'cancellation_reason',
        'billing_status',
        'metadata',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'actual_start_at' => 'datetime',
        'actual_end_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function user(): BelongsTo
    {
        // This is the Therapist/Doctor
        return $this->belongsTo(User::class);
    }
}
