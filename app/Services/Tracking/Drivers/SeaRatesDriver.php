<?php

namespace App\Services\Tracking\Drivers;

use App\Contracts\TrackingDriverInterface;
use App\DTOs\TrackingPayloadDTO;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SeaRatesDriver implements TrackingDriverInterface
{
    protected string $apiKey;

    protected ?string $customerId;

    protected string $environment;

    protected bool $isEnabled;

    public function __construct()
    {
        $settings = SystemSetting::getGroup('integration_searates');
        $this->apiKey = $settings['api_key'] ?? '';
        $this->customerId = $settings['customer_id'] ?? null;
        $this->environment = $settings['environment'] ?? 'sandbox';
        $this->isEnabled = filter_var($settings['is_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    protected function getBaseUrl(): string
    {
        return $this->environment === 'live'
            ? 'https://api.searates.com/v2/tracking'
            : 'https://sandbox.searates.com/v2/tracking';
    }

    public function fetchTracking(string $containerNumber, ?string $shippingLineCode = null): TrackingPayloadDTO
    {
        if (! $this->isEnabled || empty($this->apiKey)) {
            return (new ManualDriver)->fetchTracking($containerNumber, $shippingLineCode);
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->get($this->getBaseUrl(), array_filter([
                    'api_key' => $this->apiKey,
                    'customer_id' => $this->customerId,
                    'number' => $containerNumber,
                    'sealine' => $shippingLineCode,
                ]));

            if ($response->successful()) {
                $data = $response->json();

                return $this->normalizeResponse($containerNumber, $data);
            }
        } catch (\Throwable $e) {
            Log::error('SeaRates fetch tracking failed: '.$e->getMessage(), [
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
                ->post($this->getBaseUrl().'/subscribe', array_filter([
                    'api_key' => $this->apiKey,
                    'customer_id' => $this->customerId,
                    'number' => $containerNumber,
                    'sealine' => $shippingLineCode,
                    'booking' => $bookingNumber,
                ]));

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('SeaRates register tracking failed: '.$e->getMessage());

            return false;
        }
    }

    public function handleWebhook(array $payload): void
    {
        Log::info('SeaRates Webhook Received', $payload);
    }

    /**
     * Parse SeaRates JSON format into standard TrackingPayloadDTO.
     */
    public function normalizeResponse(string $containerNumber, array $data): TrackingPayloadDTO
    {
        $containerInfo = $data['ContainerInfo'] ?? [];
        $route = $data['Route'] ?? [];
        $pol = $route['POL'] ?? [];
        $pod = $route['POD'] ?? [];
        $milestones = $data['Milestones'] ?? [];

        $formattedEvents = [];
        foreach ($milestones as $ms) {
            $formattedEvents[] = [
                'event_code' => $ms['EventCode'] ?? 'STATUS',
                'event_name' => $ms['EventName'] ?? 'SeaRates Event',
                'location' => $ms['Location'] ?? null,
                'timestamp' => $ms['Date'] ?? null,
                'is_actual' => filter_var($ms['IsActual'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'raw_json' => $ms,
            ];
        }

        return new TrackingPayloadDTO(
            container_number: $containerInfo['ContainerNumber'] ?? $containerNumber,
            current_status: strtoupper(str_replace(' ', '_', $containerInfo['Status'] ?? 'IN_TRANSIT')),
            pol_name: $pol['PortName'] ?? null,
            pol_code: $pol['UNLOCODE'] ?? null,
            pol_actual_departure: $pol['ActualDeparture'] ?? null,
            pod_name: $pod['PortName'] ?? null,
            pod_code: $pod['UNLOCODE'] ?? null,
            pod_estimated_arrival: $pod['EstimatedArrival'] ?? null,
            vessel_name: $containerInfo['VesselName'] ?? null,
            voyage_number: $containerInfo['Voyage'] ?? null,
            shipping_line: $containerInfo['ShippingLine'] ?? null,
            events_array: $formattedEvents,
            raw_response: $data
        );
    }
}
