<?php

namespace App\Services\Tracking\Drivers;

use App\Contracts\TrackingDriverInterface;
use App\DTOs\TrackingPayloadDTO;

class ManualDriver implements TrackingDriverInterface
{
    public function fetchTracking(string $containerNumber, ?string $shippingLineCode = null): TrackingPayloadDTO
    {
        return new TrackingPayloadDTO(
            container_number: $containerNumber,
            current_status: 'MANUAL',
            shipping_line: $shippingLineCode,
            events_array: [],
            raw_response: ['mode' => 'manual', 'message' => 'Manual tracking driver active or external API disabled.']
        );
    }

    public function registerTracking(string $containerNumber, ?string $shippingLineCode = null, ?string $bookingNumber = null): bool
    {
        return true;
    }

    public function handleWebhook(array $payload): void
    {
        // Manual driver no-op
    }
}
