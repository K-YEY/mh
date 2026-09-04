<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MasterShipment extends Model
{
    protected $fillable = [
        'file_number',
        'direction',
        'shipment_mode',
        'shipping_line_id',
        'customs_broker_id',
        'trucker_id',
        'coo_type',
        'compliance_status',
        'booking_reference',
        'master_bl_number',
        'vessel_name',
        'voyage_number',
        'pol_id',
        'pod_id',
        'etd',
        'eta',
        'actual_departure',
        'actual_arrival',
        'status',
        'created_by_user_id',
    ];

    protected $casts = [
        'etd' => 'date',
        'eta' => 'date',
        'actual_departure' => 'date',
        'actual_arrival' => 'date',
    ];

    public function shippingLine(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'shipping_line_id');
    }

    public function customsBroker(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'customs_broker_id');
    }

    public function trucker(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'trucker_id');
    }

    public function pol(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'pol_id');
    }

    public function pod(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'pod_id');
    }

    public function containers(): HasMany
    {
        return $this->hasMany(MasterContainer::class, 'master_shipment_id');
    }

    public function houseShipments(): HasMany
    {
        return $this->hasMany(HouseShipment::class, 'master_shipment_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(ShipmentInvoice::class, 'master_shipment_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(ShipmentDocument::class, 'documentable');
    }
}
