<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'currency_code',
        'rate_to_base',
        'effective_date',
    ];

    protected $casts = [
        'rate_to_base' => 'decimal:6',
        'effective_date' => 'date',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}
