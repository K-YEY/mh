<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterContainer;
use App\Services\Tracking\TrackingManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackingWebhookController extends Controller
{
    public function __construct(
        protected TrackingManager $trackingManager
    ) {}

    /**
     * Ingest push tracking webhook events from ocean carriers and third-party APIs.
     */
    public function handle(Request $request, string $provider): JsonResponse
    {
        $payload = $request->all();

        Log::info("Tracking Webhook Received [Provider: {$provider}]", [
            'payload' => $payload,
        ]);

        $containerNumber = $this->extractContainerNumber($payload);

        if (! $containerNumber) {
            return response()->json([
                'status' => 'acknowledged',
                'message' => 'Webhook received but no container number found in payload.',
            ], 200);
        }

        $container = MasterContainer::where('container_number', $containerNumber)->first();

        if (! $container) {
            return response()->json([
                'status' => 'acknowledged',
                'message' => "Container {$containerNumber} not found in system.",
            ], 200);
        }

        try {
            $dto = $this->trackingManager->syncWebhookPayload($container, $provider, $payload);

            return response()->json([
                'status' => 'success',
                'message' => "Container {$containerNumber} tracking updated successfully.",
                'container_status' => $container->fresh()->current_status,
                'events_synced' => count($dto->events_array),
            ], 200);
        } catch (\Throwable $e) {
            Log::error("Failed to process webhook for container {$containerNumber}: ".$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to sync container milestones.',
            ], 500);
        }
    }

    /**
     * Extract container number from various provider payload schemas.
     */
    protected function extractContainerNumber(array $payload): ?string
    {
        // Direct container keys
        if (! empty($payload['container_number'])) {
            return (string) $payload['container_number'];
        }
        if (! empty($payload['container_id'])) {
            return (string) $payload['container_id'];
        }
        if (! empty($payload['ContainerInfo']['ContainerNumber'])) {
            return (string) $payload['ContainerInfo']['ContainerNumber'];
        }
        if (! empty($payload['number'])) {
            return (string) $payload['number'];
        }

        // Nested data object
        if (! empty($payload['data']['container_number'])) {
            return (string) $payload['data']['container_number'];
        }
        if (! empty($payload['data']['container_id'])) {
            return (string) $payload['data']['container_id'];
        }

        return null;
    }
}
