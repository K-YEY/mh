<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'party_id',
        'description',
        'currency_code',
        'exchange_rate_to_base',
        'debit',
        'credit',
    ];

    protected $casts = [
        'exchange_rate_to_base' => 'decimal:6',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'debit_base' => 'decimal:2',
        'credit_base' => 'decimal:2',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
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
