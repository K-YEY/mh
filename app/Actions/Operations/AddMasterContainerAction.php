<?php

namespace App\Actions\Operations;

use App\Models\MasterContainer;
use App\Models\MasterShipment;

class AddMasterContainerAction
{
    public function execute(MasterShipment $shipment, array $data): MasterContainer
    {
        return $shipment->containers()->create(array_merge($data, [
            'current_status' => 'ASSIGNED',
        ]));
    }
}
