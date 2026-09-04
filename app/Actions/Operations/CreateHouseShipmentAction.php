<?php

namespace App\Actions\Operations;

use App\Models\HouseShipment;
use App\Models\MasterShipment;

class CreateHouseShipmentAction
{
    public function execute(array $data): HouseShipment
    {
        $master = MasterShipment::findOrFail($data['master_shipment_id']);
        $count = HouseShipment::where('master_shipment_id', $master->id)->count() + 1;
        $houseFileNo = $master->file_number.'-H'.str_pad($count, 2, '0', STR_PAD_LEFT);

        return HouseShipment::create(array_merge($data, [
            'house_file_number' => $houseFileNo,
            'house_bl_number' => 'HBL-'.date('Y').'-'.rand(10000, 99999),
            'delivery_order_status' => 'HOLD_FINANCIAL',
            'status' => 'PLANNED',
        ]));
    }
}
