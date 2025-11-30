<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnosis extends Model
{
    protected $fillable = [
        'icd_code_id',
        'description',
        'metadata',
        'start_date',
        'type',
        'condition_status',
        'patient_id',
        'user_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'start_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function icdCode(): BelongsTo
    {
        return $this->belongsTo(IcdCode::class);
    }

    public function user(): BelongsTo
    {
        // This is the physician/user who recorded the diagnosis
        return $this->belongsTo(User::class);
    }
}
