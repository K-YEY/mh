<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterContainer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'master_shipment_id',
        'container_number',
        'container_type',
        'seal_number',
        'is_reefer',
        'target_temperature',
        'ventilation_flow',
        'humidity_percent',
        'genset_number',
        'data_logger_serial',
        'requires_cold_treatment',
        'ct_probe_1',
        'ct_probe_2',
        'ct_probe_3',
        'tare_weight_kg',
        'max_payload_kg',
        'discharged_at',
        'free_days',
        'returned_empty_at',
        'current_status',
        'carrier_code',
        'carrier_deep_link',
        'iso_valid',
        'eta',
        'etd',
        'last_sync_at',
    ];

    protected $casts = [
        'is_reefer' => 'boolean',
        'requires_cold_treatment' => 'boolean',
        'iso_valid' => 'boolean',
        'target_temperature' => 'decimal:1',
        'ventilation_flow' => 'integer',
        'humidity_percent' => 'integer',
        'tare_weight_kg' => 'decimal:2',
        'max_payload_kg' => 'decimal:2',
        'free_days' => 'integer',
        'discharged_at' => 'datetime',
        'returned_empty_at' => 'datetime',
        'eta' => 'datetime',
        'etd' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    public function masterShipment(): BelongsTo
    {
        return $this->belongsTo(MasterShipment::class, 'master_shipment_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ContainerMilestone::class, 'master_container_id')->orderBy('occurred_at', 'asc');
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(ContainerTrackingEvent::class, 'master_container_id');
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(ContainerDeposit::class, 'master_container_id');
    }
}
