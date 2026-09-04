<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function roles(): HasMany
    {
        return $this->hasMany(PartyRole::class, 'party_id');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('role', strtoupper($role))->exists();
    }
}
