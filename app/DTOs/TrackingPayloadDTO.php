<?php

namespace App\DTOs;

class TrackingPayloadDTO
{
    /**
     * @param array<int, array{
     *     event_code: string,
     *     event_name: string,
     *     location: string|null,
     *     timestamp: string|null,
     *     is_actual: bool,
     *     raw_json?: mixed
     * }> $events_array
     */
    public function __construct(
        public string $container_number,
        public string $current_status = 'UNKNOWN',
        public ?string $pol_name = null,
        public ?string $pol_code = null,
        public ?string $pol_actual_departure = null,
        public ?string $pod_name = null,
        public ?string $pod_code = null,
        public ?string $pod_estimated_arrival = null,
        public ?string $vessel_name = null,
        public ?string $voyage_number = null,
        public ?string $shipping_line = null,
        public array $events_array = [],
        public ?array $raw_response = null
    ) {}

    /**
     * Convert DTO to associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'container_number' => $this->container_number,
            'current_status' => $this->current_status,
            'pol_name' => $this->pol_name,
            'pol_code' => $this->pol_code,
            'pol_actual_departure' => $this->pol_actual_departure,
            'pod_name' => $this->pod_name,
            'pod_code' => $this->pod_code,
            'pod_estimated_arrival' => $this->pod_estimated_arrival,
            'vessel_name' => $this->vessel_name,
            'voyage_number' => $this->voyage_number,
            'shipping_line' => $this->shipping_line,
            'events_array' => $this->events_array,
            'raw_response' => $this->raw_response,
        ];
    }
}
