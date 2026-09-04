<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerDeposit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'master_container_id',
        'party_id',
        'deposit_type',
        'amount',
        'currency_code',
        'status',
        'deducted_amount',
        'deduction_reason',
        'refund_date',
    ];

    protected $casts = [
        'refund_date' => 'date',
        'amount' => 'decimal:2',
        'deducted_amount' => 'decimal:2',
    ];

    public function masterContainer(): BelongsTo
    {
        return $this->belongsTo(MasterContainer::class, 'master_container_id');
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}
