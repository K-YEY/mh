<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trucker extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'trade_name',
        'fleet_size',
        'truck_types',
        'coverage_areas',
        'license_number',
        'tax_number',
        'commercial_registry',
        'dispatch_contact',
        'dispatch_phone',
        'phone',
        'email',
        'address',
        'notes',
        'is_active',
        'party_id',
    ];

    protected $casts = [
        'fleet_size' => 'integer',
        'is_active' => 'boolean',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function masterShipments(): HasMany
    {
        return $this->hasMany(MasterShipment::class, 'trucker_id', 'party_id');
    }

    public function houseShipments(): HasMany
    {
        return $this->hasMany(HouseShipment::class, 'trucker_id', 'party_id');
    }
}
