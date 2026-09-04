<?php

namespace App\Actions\Accounting;

use App\Models\ExchangeRate;

class UpdateExchangeRateAction
{
    public function execute(array $data): ExchangeRate
    {
        return ExchangeRate::create([
            'currency_code' => $data['currency_code'],
            'rate_to_base' => $data['rate_to_base'],
            'effective_date' => $data['effective_date'] ?? now(),
        ]);
    }
}
