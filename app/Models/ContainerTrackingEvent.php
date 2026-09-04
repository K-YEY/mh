<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerTrackingEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'master_container_id',
        'event_code',
        'event_name',
        'location_name',
        'occurred_at',
        'source',
        'raw_payload',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function masterContainer(): BelongsTo
    {
        return $this->belongsTo(MasterContainer::class, 'master_container_id');
    }
}
