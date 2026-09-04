<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimal_places',
        'is_base_currency',
        'is_active',
    ];

    protected $casts = [
        'is_base_currency' => 'boolean',
        'is_active' => 'boolean',
        'decimal_places' => 'integer',
    ];

    public function exchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'currency_code', 'code');
    }
}
