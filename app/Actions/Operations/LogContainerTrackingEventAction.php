<?php

namespace App\Actions\Operations;

use App\Models\ContainerTrackingEvent;
use App\Models\MasterContainer;

class LogContainerTrackingEventAction
{
    public function execute(MasterContainer $container, array $data): ContainerTrackingEvent
    {
        $event = $container->trackingEvents()->create(array_merge($data, [
            'occurred_at' => $data['occurred_at'] ?? now(),
            'source' => $data['source'] ?? 'MANUAL',
        ]));

        if (isset($data['update_container_status'])) {
            $container->update(['current_status' => $data['event_code']]);
        }

        return $event;
    }
}
