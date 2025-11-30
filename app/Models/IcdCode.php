<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IcdCode extends Model
{
    protected $fillable = [
        'code', 'description',
    ];
}
