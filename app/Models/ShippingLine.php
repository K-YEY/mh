<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'scac',
        'name',
        'name_ar',
        'bic_prefixes',
        'tracking_url_template',
        'website_url',
        'logo_url',
        'is_active',
    ];

    protected $casts = [
        'bic_prefixes' => 'array',
        'is_active' => 'boolean',
    ];

    public function milestones(): HasMany
    {
        return $this->hasMany(ContainerMilestone::class, 'shipping_line_id');
    }

    /**
     * Generate the official direct tracking URL for a container number.
     */
    public function getTrackingUrl(string $containerNumber): string
    {
        $cleanNumber = strtoupper(trim($containerNumber));

        return str_replace('{container}', $cleanNumber, $this->tracking_url_template);
    }
}
