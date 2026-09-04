<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseShipmentContainer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'house_shipment_id',
        'master_container_id',
        'allocated_weight_kg',
        'allocated_cbm',
    ];

    protected $casts = [
        'allocated_weight_kg' => 'decimal:2',
        'allocated_cbm' => 'decimal:3',
    ];

    public function houseShipment(): BelongsTo
    {
        return $this->belongsTo(HouseShipment::class, 'house_shipment_id');
    }

    public function masterContainer(): BelongsTo
    {
        return $this->belongsTo(MasterContainer::class, 'master_container_id');
    }
}
