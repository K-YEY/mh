<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'requires_expiry_date',
        'requires_reference_no',
        'is_active',
    ];

    protected $casts = [
        'requires_expiry_date' => 'boolean',
        'requires_reference_no' => 'boolean',
        'is_active' => 'boolean',
    ];
}
