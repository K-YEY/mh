<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargeItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'revenue_account_id',
        'expense_account_id',
    ];

    public function revenueAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'revenue_account_id');
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }
}
