<?php

namespace App\Services\Tracking\Drivers;

use App\Contracts\TrackingDriverInterface;
use App\DTOs\TrackingPayloadDTO;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VizionDriver implements TrackingDriverInterface
{
    protected string $apiKey;

    protected string $environment;

    protected ?string $webhookSecret;

    protected bool $isEnabled;

    public function __construct()
    {
        $settings = SystemSetting::getGroup('integration_vizion');
        $this->apiKey = $settings['api_key'] ?? '';
        $this->environment = $settings['environment'] ?? 'sandbox';
        $this->webhookSecret = $settings['webhook_secret'] ?? null;
        $this->isEnabled = filter_var($settings['is_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    protected function getBaseUrl(): string
    {
        return $this->environment === 'live'
            ? 'https://prod.vizionapi.com/v4'
            : 'https://sandbox.vizionapi.com/v4';
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
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->getBaseUrl().'/references/'.$containerNumber);

            if ($response->successful()) {
                $data = $response->json();

                return $this->normalizeResponse($containerNumber, $data);
            }
        } catch (\Throwable $e) {
            Log::error('Vizion fetch tracking failed: '.$e->getMessage(), [
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
                    'X-API-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->getBaseUrl().'/references', [
                    'container_id' => $containerNumber,
                    'scac' => $shippingLineCode,
                    'booking_number' => $bookingNumber,
                ]);

            return $response->successful() || $response->status() === 409; // 409 means already tracked
        } catch (\Throwable $e) {
            Log::error('Vizion register tracking failed: '.$e->getMessage());

            return false;
        }
    }

    public function handleWebhook(array $payload): void
    {
        Log::info('Vizion Webhook Received', $payload);
    }

    protected function normalizeResponse(string $containerNumber, array $data): TrackingPayloadDTO
    {
        $events = [];
        $rawEvents = $data['milestones'] ?? $data['events'] ?? [];

        foreach ($rawEvents as $event) {
            $events[] = [
                'event_code' => $event['milestone_code'] ?? $event['code'] ?? 'STATUS',
                'event_name' => $event['description'] ?? $event['name'] ?? 'Vizion Milestone',
                'location' => $event['location']['name'] ?? $event['location'] ?? null,
                'timestamp' => $event['actual_time'] ?? $event['estimated_time'] ?? null,
                'is_actual' => isset($event['actual_time']),
                'raw_json' => $event,
            ];
        }

        return new TrackingPayloadDTO(
            container_number: $data['container_id'] ?? $containerNumber,
            current_status: strtoupper($data['status'] ?? 'IN_TRANSIT'),
            pol_name: $data['pol']['name'] ?? null,
            pol_code: $data['pol']['locode'] ?? null,
            pol_actual_departure: $data['pol']['actual_departure'] ?? null,
            pod_name: $data['pod']['name'] ?? null,
            pod_code: $data['pod']['locode'] ?? null,
            pod_estimated_arrival: $data['pod']['estimated_arrival'] ?? null,
            vessel_name: $data['vessel_name'] ?? null,
            voyage_number: $data['voyage'] ?? null,
            shipping_line: $data['scac'] ?? null,
            events_array: $events,
            raw_response: $data
        );
    }
}
