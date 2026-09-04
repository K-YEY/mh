<?php

namespace App\Actions\Accounting;

use App\Models\JournalEntry;
use App\Models\PaymentVoucher;
use App\Services\Audit\AuditLoggerService;
use App\Services\Financial\DoubleEntryPostingService;
use App\Services\Sequencing\SequenceGeneratorService;

class ProcessPaymentVoucherAction
{
    public function __construct(
        protected DoubleEntryPostingService $postingService,
        protected SequenceGeneratorService $sequenceGenerator
    ) {}

    public function execute(array $data): PaymentVoucher
    {
        $year = (int) date('Y', strtotime($data['payment_date'] ?? 'now'));
        $docType = $data['voucher_type'] === 'RECEIPT' ? 'RECEIPT_VOUCHER' : 'PAYMENT_VOUCHER';
        $vNo = $this->sequenceGenerator->getNextSequence($docType, $year);

        $voucher = PaymentVoucher::create(array_merge($data, [
            'voucher_number' => $vNo,
            'created_by_user_id' => auth()->id() ?? 1,
        ]));

        AuditLoggerService::log($voucher, 'create', "Created Treasury Voucher {$voucher->voucher_number}");

        $this->postingService->postVoucherJournal($voucher);

        return $voucher;
    }
}
