<?php

namespace App\Actions\Accounting;

use App\Models\ShipmentInvoice;
use App\Services\Audit\AuditLoggerService;
use App\Services\Financial\DoubleEntryPostingService;
use App\Services\Sequencing\SequenceGeneratorService;

class PostInvoiceJournalAction
{
    public function __construct(
        protected DoubleEntryPostingService $postingService,
        protected SequenceGeneratorService $sequenceGenerator
    ) {}

    public function execute(array $invoiceData, array $lineItems): ShipmentInvoice
    {
        $subtotal = 0.00;
        foreach ($lineItems as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $year = (int) date('Y', strtotime($invoiceData['issue_date'] ?? 'now'));
        $docType = $invoiceData['invoice_type'] === 'CUSTOMER_INVOICE' ? 'CUSTOMER_INVOICE' : 'VENDOR_BILL';
        $invoiceNo = $this->sequenceGenerator->getNextSequence($docType, $year);

        $invoice = ShipmentInvoice::create(array_merge($invoiceData, [
            'invoice_number' => $invoiceNo,
            'subtotal' => $subtotal,
            'tax_total' => 0.00,
            'total_amount' => $subtotal,
            'status' => 'DRAFT',
        ]));

        foreach ($lineItems as $item) {
            $invoice->items()->create([
                'charge_item_id' => $item['charge_item_id'],
                'description' => $item['description'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        AuditLoggerService::log($invoice, 'create', "Generated {$invoice->invoice_type} #{$invoice->invoice_number}");

        $this->postingService->postInvoiceJournal($invoice);

        return $invoice;
    }
}
