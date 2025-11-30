<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'phone_number',
        'address', // Include if you add it to the form
        'user_id',
        'is_active',
        'avatar',
        'email',
        'city',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'metadata' => 'json',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function therapySessions()
    {
        return $this->hasMany(TherapySession::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
