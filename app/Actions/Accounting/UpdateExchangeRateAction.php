<?php

namespace App\Actions\Accounting;

use App\Models\ExchangeRate;

class UpdateExchangeRateAction
{
    public function execute(array $data): ExchangeRate
    {
        return ExchangeRate::updateOrCreate(
            [
                'currency_code' => $data['currency_code'],
                'effective_date' => $data['effective_date'] ?? now()->toDateString(),
            ],
            [
                'rate_to_base' => $data['rate_to_base'],
            ]
        );
    }
}
