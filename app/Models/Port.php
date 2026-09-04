<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'country_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
