<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Party extends Model
{
    protected $fillable = [
        'code',
        'legal_name',
        'trade_name',
        'tax_number',
        'commercial_registry',
        'email',
        'phone',
        'address',
        'account_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function roles(): HasMany
    {
        return $this->hasMany(PartyRole::class, 'party_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('role', strtoupper($role))->exists();
    }

    /**
     * Get the effective GL Account ID for this party.
     * Returns dedicated account_id if assigned, or falls back to standard role-based control account.
     */
    public function getEffectiveAccountId(string $side = 'AR'): int
    {
        if ($this->account_id) {
            return $this->account_id;
        }

        if ($side === 'AR') {
            // Accounts Receivable (Clients & Shippers)
            return Account::where('code', '11210')->value('id')
                ?? Account::where('code', '11200')->value('id')
                ?? 2;
        }

        // Accounts Payable (Vendors, Carriers, Brokers, Truckers)
        if ($this->hasRole('CUSTOMS_BROKER')) {
            return Account::where('code', '21300')->value('id')
                ?? Account::where('code', '21100')->value('id')
                ?? 3;
        }

        if ($this->hasRole('TRUCKER')) {
            return Account::where('code', '21200')->value('id')
                ?? Account::where('code', '21100')->value('id')
                ?? 3;
        }

        return Account::where('code', '21100')->value('id') ?? 3;
    }
}
