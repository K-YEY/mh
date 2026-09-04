<?php

namespace App\Services\Tracking\Drivers;

use App\Contracts\TrackingDriverInterface;
use App\DTOs\TrackingPayloadDTO;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FindTeuDriver implements TrackingDriverInterface
{
    protected string $apiToken;

    protected string $environment;

    protected bool $isEnabled;

    public function __construct()
    {
        $settings = SystemSetting::getGroup('integration_findteu');
        $this->apiToken = $settings['api_token'] ?? '';
        $this->environment = $settings['environment'] ?? 'sandbox';
        $this->isEnabled = filter_var($settings['is_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    protected function getBaseUrl(): string
    {
        return $this->environment === 'live'
            ? 'https://api.findteu.com/v1'
            : 'https://sandbox.findteu.com/v1';
    }

    public function fetchTracking(string $containerNumber, ?string $shippingLineCode = null): TrackingPayloadDTO
    {
        if (! $this->isEnabled || empty($this->apiToken)) {
            return (new ManualDriver)->fetchTracking($containerNumber, $shippingLineCode);
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->withHeaders([
                    'X-FindTEU-Token' => $this->apiToken,
                    'Accept' => 'application/json',
                ])
                ->get($this->getBaseUrl().'/track/'.$containerNumber, array_filter([
                    'carrier' => $shippingLineCode,
                ]));

            if ($response->successful()) {
                $data = $response->json();

                return $this->normalizeResponse($containerNumber, $data);
            }
        } catch (\Throwable $e) {
            Log::error('FindTEU fetch tracking failed: '.$e->getMessage(), [
                'container' => $containerNumber,
            ]);
        }

        return (new ManualDriver)->fetchTracking($containerNumber, $shippingLineCode);
    }

    public function registerTracking(string $containerNumber, ?string $shippingLineCode = null, ?string $bookingNumber = null): bool
    {
        if (! $this->isEnabled || empty($this->apiToken)) {
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->withHeaders([
                    'X-FindTEU-Token' => $this->apiToken,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->getBaseUrl().'/track', [
                    'container_number' => $containerNumber,
                    'carrier_code' => $shippingLineCode,
                    'booking_number' => $bookingNumber,
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('FindTEU register tracking failed: '.$e->getMessage());

            return false;
        }
    }

    public function handleWebhook(array $payload): void
    {
        Log::info('FindTEU Webhook Received', $payload);
    }

    protected function normalizeResponse(string $containerNumber, array $data): TrackingPayloadDTO
    {
        $events = [];
        $rawEvents = $data['events'] ?? $data['milestones'] ?? [];

        foreach ($rawEvents as $event) {
            $events[] = [
                'event_code' => $event['event_code'] ?? 'STATUS',
                'event_name' => $event['event_name'] ?? $event['description'] ?? 'Container Activity',
                'location' => $event['location'] ?? null,
                'timestamp' => $event['occurred_at'] ?? $event['date'] ?? null,
                'is_actual' => filter_var($event['is_actual'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'raw_json' => $event,
            ];
        }

        return new TrackingPayloadDTO(
            container_number: $data['container_number'] ?? $containerNumber,
            current_status: strtoupper($data['status'] ?? 'IN_TRANSIT'),
            pol_name: $data['pol_name'] ?? null,
            pol_code: $data['pol_unlocode'] ?? null,
            pol_actual_departure: $data['actual_departure'] ?? null,
            pod_name: $data['pod_name'] ?? null,
            pod_code: $data['pod_unlocode'] ?? null,
            pod_estimated_arrival: $data['estimated_arrival'] ?? null,
            vessel_name: $data['vessel'] ?? null,
            voyage_number: $data['voyage'] ?? null,
            shipping_line: $data['carrier'] ?? null,
            events_array: $events,
            raw_response: $data
        );
    }
}
