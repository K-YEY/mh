<?php

namespace App\Services\Operations;

use App\Models\OceanFreightTariff;
use Illuminate\Database\Eloquent\Collection;

class TariffEvaluationService
{
    /**
     * Search and rank ocean freight tariffs by buy rate & transit time
     */
    public function searchLowestRates(int $polId, int $podId, string $containerType): Collection
    {
        return OceanFreightTariff::with(['shippingLine', 'pol', 'pod', 'currency'])
            ->where('pol_id', $polId)
            ->where('pod_id', $podId)
            ->where('container_type', $containerType)
            ->where('valid_to', '>=', now())
            ->orderBy('buy_rate', 'asc')
            ->get();
    }
}
