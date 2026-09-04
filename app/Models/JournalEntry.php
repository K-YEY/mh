<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'source_type',
        'source_id',
        'narration',
        'is_posted',
        'posted_by_user_id',
        'posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    public function postedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by_user_id');
    }
}
