<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomsBroker extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'trade_name',
        'license_number',
        'operating_ports',
        'tax_number',
        'commercial_registry',
        'contact_person',
        'contact_phone',
        'phone',
        'email',
        'address',
        'notes',
        'is_active',
        'party_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function masterShipments(): HasMany
    {
        return $this->hasMany(MasterShipment::class, 'customs_broker_id', 'party_id');
    }

    public function houseShipments(): HasMany
    {
        return $this->hasMany(HouseShipment::class, 'customs_broker_id', 'party_id');
    }
}
