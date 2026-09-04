<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OceanFreightTariff extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'shipping_line_id',
        'pol_id',
        'pod_id',
        'container_type',
        'currency_code',
        'buy_rate',
        'transit_time_days',
        'valid_from',
        'valid_to',
        'free_days_demurrage',
        'notes',
    ];

    protected $casts = [
        'buy_rate' => 'decimal:2',
        'transit_time_days' => 'integer',
        'free_days_demurrage' => 'integer',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function shippingLine(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'shipping_line_id');
    }

    public function pol(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'pol_id');
    }

    public function pod(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'pod_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}
