<?php

namespace App\Services\Tracking;

use App\Contracts\TrackingDriverInterface;
use App\DTOs\TrackingPayloadDTO;
use App\Models\ContainerTrackingEvent;
use App\Models\MasterContainer;
use App\Models\SystemSetting;
use App\Services\Tracking\Drivers\FindTeuDriver;
use App\Services\Tracking\Drivers\ManualDriver;
use App\Services\Tracking\Drivers\SeaRatesDriver;
use App\Services\Tracking\Drivers\ShipsGoDriver;
use App\Services\Tracking\Drivers\VizionDriver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Manager;

class TrackingManager extends Manager implements TrackingDriverInterface
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        // Priority order of active integrations
        if (filter_var(SystemSetting::get('integration_shipsgo', 'is_enabled', 'false'), FILTER_VALIDATE_BOOLEAN)) {
            return 'shipsgo';
        }

        if (filter_var(SystemSetting::get('integration_searates', 'is_enabled', 'false'), FILTER_VALIDATE_BOOLEAN)) {
            return 'searates';
        }

        if (filter_var(SystemSetting::get('integration_vizion', 'is_enabled', 'false'), FILTER_VALIDATE_BOOLEAN)) {
            return 'vizion';
        }

        if (filter_var(SystemSetting::get('integration_findteu', 'is_enabled', 'false'), FILTER_VALIDATE_BOOLEAN)) {
            return 'findteu';
        }

        return 'manual';
    }

    public function createShipsgoDriver(): TrackingDriverInterface
    {
        return new ShipsGoDriver;
    }

    public function createSearatesDriver(): TrackingDriverInterface
    {
        return new SeaRatesDriver;
    }

    public function createVizionDriver(): TrackingDriverInterface
    {
        return new VizionDriver;
    }

    public function createFindteuDriver(): TrackingDriverInterface
    {
        return new FindTeuDriver;
    }

    public function createManualDriver(): TrackingDriverInterface
    {
        return new ManualDriver;
    }

    /**
     * Delegate fetchTracking to resolved driver with fallback resilience.
     */
    public function fetchTracking(string $containerNumber, ?string $shippingLineCode = null): TrackingPayloadDTO
    {
        try {
            /** @var TrackingDriverInterface $driver */
            $driver = $this->driver();

            return $driver->fetchTracking($containerNumber, $shippingLineCode);
        } catch (\Throwable $e) {
            Log::error('TrackingManager fetch error: '.$e->getMessage(), [
                'container' => $containerNumber,
            ]);

            return (new ManualDriver)->fetchTracking($containerNumber, $shippingLineCode);
        }
    }

    /**
     * Delegate registerTracking to resolved driver.
     */
    public function registerTracking(string $containerNumber, ?string $shippingLineCode = null, ?string $bookingNumber = null): bool
    {
        try {
            /** @var TrackingDriverInterface $driver */
            $driver = $this->driver();

            return $driver->registerTracking($containerNumber, $shippingLineCode, $bookingNumber);
        } catch (\Throwable $e) {
            Log::error('TrackingManager register error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Handle incoming webhook.
     */
    public function handleWebhook(array $payload): void
    {
        try {
            /** @var TrackingDriverInterface $driver */
            $driver = $this->driver();
            $driver->handleWebhook($payload);
        } catch (\Throwable $e) {
            Log::error('TrackingManager webhook error: '.$e->getMessage());
        }
    }

    /**
     * Sync container tracking with DB models safely.
     */
    public function syncContainer(MasterContainer $container, ?string $provider = null): TrackingPayloadDTO
    {
        $shippingLineCode = $container->masterShipment?->shippingLine?->code ?? null;
        $driverInstance = $provider ? $this->driver($provider) : $this->driver();

        /** @var TrackingDriverInterface $driverInstance */
        $dto = $driverInstance->fetchTracking($container->container_number, $shippingLineCode);

        if ($dto->current_status !== 'UNKNOWN' && $dto->current_status !== 'MANUAL') {
            $container->update([
                'current_status' => $dto->current_status,
            ]);

            if ($container->masterShipment) {
                $shipmentUpdates = [];
                if ($dto->vessel_name) {
                    $shipmentUpdates['vessel_name'] = $dto->vessel_name;
                }
                if ($dto->voyage_number) {
                    $shipmentUpdates['voyage_number'] = $dto->voyage_number;
                }
                if ($dto->pol_actual_departure) {
                    $shipmentUpdates['actual_departure'] = date('Y-m-d', strtotime($dto->pol_actual_departure));
                }
                if ($dto->pod_estimated_arrival) {
                    $shipmentUpdates['eta'] = date('Y-m-d', strtotime($dto->pod_estimated_arrival));
                }

                if (! empty($shipmentUpdates)) {
                    $container->masterShipment->update($shipmentUpdates);
                }
            }
        }

        // Record tracking events
        foreach ($dto->events_array as $event) {
            ContainerTrackingEvent::firstOrCreate(
                [
                    'master_container_id' => $container->id,
                    'event_code' => $event['event_code'],
                    'occurred_at' => $event['timestamp'] ? date('Y-m-d H:i:s', strtotime($event['timestamp'])) : now(),
                ],
                [
                    'event_name' => $event['event_name'],
                    'location_name' => $event['location'] ?? 'N/A',
                    'source' => in_array(strtoupper($provider ?? $this->getDefaultDriver()), ['MANUAL', 'FINDTEU', 'SHIPSGO', 'VIZION', 'SEARATES']) ? strtoupper($provider ?? $this->getDefaultDriver()) : 'MANUAL',
                    'raw_payload' => $event['raw_json'] ?? $event,
                ]
            );
        }

        return $dto;
    }

    /**
     * Sync incoming webhook payload with container and tracking events.
     */
    public function syncWebhookPayload(MasterContainer $container, string $provider, array $payload): TrackingPayloadDTO
    {
        try {
            /** @var TrackingDriverInterface $driver */
            $driver = $this->driver($provider);

            if (method_exists($driver, 'normalizeResponse')) {
                $dto = $driver->normalizeResponse($container->container_number, $payload);
            } else {
                $dto = new TrackingPayloadDTO(
                    container_number: $container->container_number,
                    current_status: strtoupper($payload['status'] ?? 'IN_TRANSIT'),
                    events_array: []
                );
            }
        } catch (\Throwable $e) {
            Log::error('Failed to parse webhook payload: '.$e->getMessage());
            $dto = new TrackingPayloadDTO(container_number: $container->container_number);
        }

        if ($dto->current_status !== 'UNKNOWN' && $dto->current_status !== 'MANUAL') {
            $container->update([
                'current_status' => $dto->current_status,
            ]);

            if ($container->masterShipment) {
                $shipmentUpdates = [];
                if ($dto->vessel_name) {
                    $shipmentUpdates['vessel_name'] = $dto->vessel_name;
                }
                if ($dto->voyage_number) {
                    $shipmentUpdates['voyage_number'] = $dto->voyage_number;
                }
                if ($dto->pol_actual_departure) {
                    $shipmentUpdates['actual_departure'] = date('Y-m-d', strtotime($dto->pol_actual_departure));
                }
                if ($dto->pod_estimated_arrival) {
                    $shipmentUpdates['eta'] = date('Y-m-d', strtotime($dto->pod_estimated_arrival));
                }

                if (! empty($shipmentUpdates)) {
                    $container->masterShipment->update($shipmentUpdates);
                }
            }
        }

        // Record tracking events
        foreach ($dto->events_array as $event) {
            ContainerTrackingEvent::firstOrCreate(
                [
                    'master_container_id' => $container->id,
                    'event_code' => $event['event_code'],
                    'occurred_at' => $event['timestamp'] ? date('Y-m-d H:i:s', strtotime($event['timestamp'])) : now(),
                ],
                [
                    'event_name' => $event['event_name'],
                    'location_name' => $event['location'] ?? 'N/A',
                    'source' => in_array(strtoupper($provider), ['MANUAL', 'FINDTEU', 'SHIPSGO', 'VIZION', 'SEARATES']) ? strtoupper($provider) : 'MANUAL',
                    'raw_payload' => $event['raw_json'] ?? $event,
                ]
            );
        }

        return $dto;
    }
}
