<?php

namespace App\Jobs;

use App\Services\Tracking\ContainerTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncContainerTrackingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $containerNumber,
        public ?string $provider = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ContainerTrackingService $trackingService): void
    {
        try {
            $result = $trackingService->syncMilestones($this->containerNumber, $this->provider);

            Log::info("Container tracking synchronized: {$this->containerNumber}", [
                'carrier' => $result['carrier']['name'] ?? 'Unknown',
                'milestones_count' => count($result['milestones'] ?? []),
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to sync container tracking: {$this->containerNumber}", [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
