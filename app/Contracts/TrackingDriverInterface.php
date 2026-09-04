<?php

namespace App\Contracts;

use App\DTOs\TrackingPayloadDTO;

interface TrackingDriverInterface
{
    /**
     * Fetch container tracking data from the external provider.
     */
    public function fetchTracking(string $containerNumber, ?string $shippingLineCode = null): TrackingPayloadDTO;

    /**
     * Register container for auto-tracking / push notifications with the external provider.
     */
    public function registerTracking(string $containerNumber, ?string $shippingLineCode = null, ?string $bookingNumber = null): bool;

    /**
     * Handle incoming webhook push payloads from the provider.
     */
    public function handleWebhook(array $payload): void;
}
