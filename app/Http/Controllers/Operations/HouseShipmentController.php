<?php

namespace App\Http\Controllers\Operations;

use App\Actions\Operations\CreateHouseShipmentAction;
use App\Http\Controllers\Controller;
use App\Models\HouseShipment;
use App\Services\Compliance\ShipmentComplianceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HouseShipmentController extends Controller
{
    public function store(Request $request, CreateHouseShipmentAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'master_shipment_id' => 'required|exists:master_shipments,id',
            'shipper_id' => 'required|exists:parties,id',
            'consignee_id' => 'required|exists:parties,id',
            'packhouse_id' => 'nullable|exists:parties,id',
            'customs_broker_id' => 'nullable|exists:parties,id',
            'trucker_id' => 'nullable|exists:parties,id',
            'crop_type' => 'required|string',
            'variety' => 'nullable|string',
            'lot_number' => 'nullable|string',
            'acid_number' => 'nullable|string',
            'customs_declaration_no' => 'nullable|string',
            'total_packages' => 'required|integer|min:1',
            'package_type' => 'required|string',
            'gross_weight_kg' => 'required|numeric|min:0.1',
            'net_weight_kg' => 'nullable|numeric',
            'volume_cbm' => 'required|numeric|min:0.01',
        ]);

        $action->execute($validated);

        return redirect()->back()->with('success', 'House Shipment (HBL) created with automatic W/M calculation.');
    }

    public function releaseDeliveryOrder(
        HouseShipment $houseShipment,
        ShipmentComplianceService $complianceService
    ): RedirectResponse {
        $check = $complianceService->canReleaseDeliveryOrder($houseShipment);

        if (! $check['allowed']) {
            return redirect()->back()->with('error', $check['reason']);
        }

        $houseShipment->update([
            'delivery_order_status' => 'RELEASED',
            'status' => 'CLEARED',
        ]);

        return redirect()->back()->with('success', 'تم الإفراج عن إذن التسليم (D/O) للعميل بنجاح بعد استيفاء شروط الامتثال والمستندات الإلزامية.');
    }
}
