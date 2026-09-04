<?php

namespace App\Services\Tracking\Drivers;

use App\Contracts\TrackingDriverInterface;
use App\DTOs\TrackingPayloadDTO;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShipsGoDriver implements TrackingDriverInterface
{
    protected string $apiKey;

    protected string $environment;

    protected ?string $webhookSecret;

    protected bool $isEnabled;

    public function __construct()
    {
        $settings = SystemSetting::getGroup('integration_shipsgo');
        $this->apiKey = $settings['api_key'] ?? '';
        $this->environment = $settings['environment'] ?? 'sandbox';
        $this->webhookSecret = $settings['webhook_secret'] ?? null;
        $this->isEnabled = filter_var($settings['is_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    protected function getBaseUrl(): string
    {
        return $this->environment === 'live'
            ? 'https://shipsgo.com/api/container/v1'
            : 'https://sandbox.shipsgo.com/api/container/v1';
    }

    public function fetchTracking(string $containerNumber, ?string $shippingLineCode = null): TrackingPayloadDTO
    {
        if (! $this->isEnabled || empty($this->apiKey)) {
            return (new ManualDriver)->fetchTracking($containerNumber, $shippingLineCode);
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->getBaseUrl().'/get', [
                    'container_number' => $containerNumber,
                    'shipping_line' => $shippingLineCode,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return $this->normalizeResponse($containerNumber, $data);
            }
        } catch (\Throwable $e) {
            Log::error('ShipsGo fetch tracking failed: '.$e->getMessage(), [
                'container' => $containerNumber,
            ]);
        }

        return (new ManualDriver)->fetchTracking($containerNumber, $shippingLineCode);
    }

    public function registerTracking(string $containerNumber, ?string $shippingLineCode = null, ?string $bookingNumber = null): bool
    {
        if (! $this->isEnabled || empty($this->apiKey)) {
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->getBaseUrl().'/add', [
                    'container_number' => $containerNumber,
                    'shipping_line' => $shippingLineCode,
                    'booking_number' => $bookingNumber,
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('ShipsGo register tracking failed: '.$e->getMessage());

            return false;
        }
    }

    public function handleWebhook(array $payload): void
    {
        Log::info('ShipsGo Webhook Received', $payload);
    }

    /**
     * Parse raw ShipsGo payload into TrackingPayloadDTO.
     */
    protected function normalizeResponse(string $containerNumber, array $data): TrackingPayloadDTO
    {
        $milestones = [];
        $events = $data['events'] ?? $data['milestones'] ?? [];

        foreach ($events as $event) {
            $milestones[] = [
                'event_code' => $event['code'] ?? 'EVENT',
                'event_name' => $event['name'] ?? $event['status'] ?? 'Container Movement',
                'location' => $event['location'] ?? $event['port'] ?? null,
                'timestamp' => $event['date'] ?? $event['timestamp'] ?? null,
                'is_actual' => filter_var($event['is_actual'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'raw_json' => $event,
            ];
        }

        return new TrackingPayloadDTO(
            container_number: $data['container_number'] ?? $containerNumber,
            current_status: strtoupper($data['status'] ?? $data['current_status'] ?? 'IN_TRANSIT'),
            pol_name: $data['pol']['name'] ?? $data['pol_name'] ?? null,
            pol_code: $data['pol']['code'] ?? $data['pol_unlocode'] ?? null,
            pol_actual_departure: $data['pol']['departure_date'] ?? $data['pol_actual_departure'] ?? null,
            pod_name: $data['pod']['name'] ?? $data['pod_name'] ?? null,
            pod_code: $data['pod']['code'] ?? $data['pod_unlocode'] ?? null,
            pod_estimated_arrival: $data['pod']['arrival_date'] ?? $data['pod_estimated_arrival'] ?? null,
            vessel_name: $data['vessel_name'] ?? null,
            voyage_number: $data['voyage'] ?? null,
            shipping_line: $data['shipping_line'] ?? null,
            events_array: $milestones,
            raw_response: $data
        );
    }
}
