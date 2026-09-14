<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'container_number',
        'master_container_id',
        'shipping_line_id',
        'milestone_code',
        'milestone_name',
        'milestone_name_ar',
        'location_name',
        'vessel_name',
        'voyage_number',
        'occurred_at',
        'is_completed',
        'source',
        'raw_payload',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'is_completed' => 'boolean',
        'raw_payload' => 'array',
    ];

    public function masterContainer(): BelongsTo
    {
        return $this->belongsTo(MasterContainer::class, 'master_container_id');
    }

    public function shippingLine(): BelongsTo
    {
        return $this->belongsTo(ShippingLine::class, 'shipping_line_id');
    }
}
