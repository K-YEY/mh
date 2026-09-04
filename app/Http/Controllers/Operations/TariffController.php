<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\OceanFreightTariff;
use App\Models\Port;
use App\Services\Operations\TariffEvaluationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TariffController extends Controller
{
    public function index(Request $request, TariffEvaluationService $tariffService): Response
    {
        $polId = $request->input('pol_id');
        $podId = $request->input('pod_id');
        $containerType = $request->input('container_type', '40HR');

        $tariffs = collect();
        if ($polId && $podId) {
            $tariffs = $tariffService->searchLowestRates((int) $polId, (int) $podId, $containerType);
        } else {
            $tariffs = OceanFreightTariff::with(['shippingLine', 'pol', 'pod', 'currency'])
                ->orderBy('buy_rate', 'asc')
                ->take(15)
                ->get();
        }

        $ports = Port::where('is_active', true)->get();

        return Inertia::render('Operations/Tariffs/Index', [
            'tariffs' => $tariffs,
            'ports' => $ports,
            'filters' => [
                'pol_id' => $polId,
                'pod_id' => $podId,
                'container_type' => $containerType,
            ],
        ]);
    }
}
