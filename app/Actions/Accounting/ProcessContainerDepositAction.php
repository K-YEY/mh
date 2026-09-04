<?php

namespace App\Actions\Accounting;

use App\Models\ContainerDeposit;

class ProcessContainerDepositAction
{
    public function execute(array $data): ContainerDeposit
    {
        return ContainerDeposit::create(array_merge($data, [
            'status' => 'HELD',
            'paid_date' => now()->toDateString(),
        ]));
    }
}
