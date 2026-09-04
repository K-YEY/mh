<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerTrackingHistory extends Model
{
    protected $fillable = [
        'user_id',
        'master_container_id',
        'tracking_type',
        'container_number',
        'shipping_line',
        'booking_reference',
        'master_bl_number',
        'vessel_name',
        'vessel_imo',
        'voyage_number',
        'pol_name',
        'pod_name',
        'current_status',
        'last_location',
        'vessel_data',
        'last_tracked_at',
    ];

    protected $casts = [
        'last_tracked_at' => 'datetime',
        'vessel_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function masterContainer(): BelongsTo
    {
        return $this->belongsTo(MasterContainer::class, 'master_container_id');
    }
}
