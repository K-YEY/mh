<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class HouseShipment extends Model
{
    protected $fillable = [
        'master_shipment_id',
        'house_file_number',
        'house_bl_number',
        'shipper_id',
        'consignee_id',
        'notify_party_id',
        'packhouse_id',
        'customs_broker_id',
        'trucker_id',
        'crop_type',
        'variety',
        'lot_number',
        'acid_number',
        'customs_declaration_no',
        'customs_release_date',
        'delivery_order_status',
        'total_packages',
        'package_type',
        'gross_weight_kg',
        'net_weight_kg',
        'volume_cbm',
        'status',
    ];

    protected $casts = [
        'customs_release_date' => 'date',
        'total_packages' => 'integer',
        'gross_weight_kg' => 'decimal:3',
        'net_weight_kg' => 'decimal:3',
        'volume_cbm' => 'decimal:4',
        'revenue_tons' => 'decimal:4',
    ];

    public function masterShipment(): BelongsTo
    {
        return $this->belongsTo(MasterShipment::class, 'master_shipment_id');
    }

    public function shipper(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'shipper_id');
    }

    public function consignee(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'consignee_id');
    }

    public function notifyParty(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'notify_party_id');
    }

    public function packhouse(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'packhouse_id');
    }

    public function customsBroker(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'customs_broker_id');
    }

    public function trucker(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'trucker_id');
    }

    public function houseContainers(): HasMany
    {
        return $this->hasMany(HouseShipmentContainer::class, 'house_shipment_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(ShipmentInvoice::class, 'house_shipment_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(ShipmentDocument::class, 'documentable');
    }
}
