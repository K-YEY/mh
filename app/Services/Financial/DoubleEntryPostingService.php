<?php

namespace App\Services\Financial;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PaymentVoucher;
use App\Models\ShipmentInvoice;
use App\Services\Audit\AuditLoggerService;
use App\Services\Sequencing\SequenceGeneratorService;
use Illuminate\Support\Facades\DB;

class DoubleEntryPostingService
{
    public function __construct(
        protected SequenceGeneratorService $sequenceGenerator
    ) {}

    /**
     * Post a Journal Entry from a Customer Invoice or Vendor Bill
     */
    public function postInvoiceJournal(ShipmentInvoice $invoice): JournalEntry
    {
        return DB::transaction(function () use ($invoice) {
            $invoice->load(['items.chargeItem', 'party']);

            $isCustomerInvoice = $invoice->invoice_type === 'CUSTOMER_INVOICE';
            $narration = $isCustomerInvoice
                ? "Customer Sales Invoice #{$invoice->invoice_number} for {$invoice->party->legal_name}"
                : "Vendor Cost Bill #{$invoice->invoice_number} from {$invoice->party->legal_name}";

            $year = (int) date('Y', strtotime($invoice->issue_date));
            $entryNo = $this->sequenceGenerator->getNextSequence('JOURNAL_ENTRY', $year);

            $entry = JournalEntry::create([
                'entry_number' => $entryNo,
                'entry_date' => $invoice->issue_date,
                'source_type' => $isCustomerInvoice ? 'OPERATIONS_INVOICE' : 'VENDOR_BILL',
                'source_id' => $invoice->id,
                'narration' => $narration,
                'is_posted' => true,
                'posted_by_user_id' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            $arAccountId = Account::where('code', '11200')->value('id') ?? 2;
            $apAccountId = Account::where('code', '21100')->value('id') ?? 3;

            if ($isCustomerInvoice) {
                // AR Account (11200) Debit
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $arAccountId, // Accounts Receivable
                    'party_id' => $invoice->party_id,
                    'description' => "AR - Invoice {$invoice->invoice_number}",
                    'currency_code' => $invoice->currency_code,
                    'exchange_rate_to_base' => $invoice->exchange_rate,
                    'debit' => $invoice->total_amount,
                    'credit' => 0.00,
                ]);

                // Revenue Account per line Credit
                foreach ($invoice->items as $item) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'account_id' => $item->chargeItem->revenue_account_id,
                        'party_id' => $invoice->party_id,
                        'description' => $item->description ?? $item->chargeItem->name_en,
                        'currency_code' => $invoice->currency_code,
                        'exchange_rate_to_base' => $invoice->exchange_rate,
                        'debit' => 0.00,
                        'credit' => $item->line_total,
                    ]);
                }
            } else {
                // Expense Account per line Debit
                foreach ($invoice->items as $item) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'account_id' => $item->chargeItem->expense_account_id,
                        'party_id' => $invoice->party_id,
                        'description' => $item->description ?? $item->chargeItem->name_en,
                        'currency_code' => $invoice->currency_code,
                        'exchange_rate_to_base' => $invoice->exchange_rate,
                        'debit' => $item->line_total,
                        'credit' => 0.00,
                    ]);
                }

                // AP Account (21100) Credit
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $apAccountId, // Accounts Payable
                    'party_id' => $invoice->party_id,
                    'description' => "AP - Bill {$invoice->invoice_number}",
                    'currency_code' => $invoice->currency_code,
                    'exchange_rate_to_base' => $invoice->exchange_rate,
                    'debit' => 0.00,
                    'credit' => $invoice->total_amount,
                ]);
            }

            $invoice->update([
                'status' => 'POSTED',
                'journal_entry_id' => $entry->id,
            ]);

            AuditLoggerService::log($entry, 'post', "Posted Journal Entry {$entry->entry_number} for Invoice {$invoice->invoice_number}");

            return $entry;
        });
    }

    /**
     * Post a Journal Entry from a Payment or Receipt Voucher
     */
    public function postVoucherJournal(PaymentVoucher $voucher): JournalEntry
    {
        return DB::transaction(function () use ($voucher) {
            $voucher->load('party');

            $isReceipt = $voucher->voucher_type === 'RECEIPT';
            $narration = $isReceipt
                ? "Receipt Voucher #{$voucher->voucher_number} from {$voucher->party->legal_name}"
                : "Payment Voucher #{$voucher->voucher_number} to {$voucher->party->legal_name}";

            $year = (int) date('Y', strtotime($voucher->payment_date));
            $entryNo = $this->sequenceGenerator->getNextSequence('JOURNAL_ENTRY', $year);

            $entry = JournalEntry::create([
                'entry_number' => $entryNo,
                'entry_date' => $voucher->payment_date,
                'source_type' => $isReceipt ? 'RECEIPT_VOUCHER' : 'PAYMENT_VOUCHER',
                'source_id' => $voucher->id,
                'narration' => $narration,
                'is_posted' => true,
                'posted_by_user_id' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            $arAccountId = Account::where('code', '11200')->value('id') ?? 2;
            $apAccountId = Account::where('code', '21100')->value('id') ?? 3;

            if ($isReceipt) {
                // Bank/Cash Account Debit
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $voucher->account_id,
                    'party_id' => $voucher->party_id,
                    'description' => "Cash/Bank Receipt #{$voucher->voucher_number}",
                    'currency_code' => $voucher->currency_code,
                    'exchange_rate_to_base' => $voucher->exchange_rate,
                    'debit' => $voucher->amount,
                    'credit' => 0.00,
                ]);

                // AR Account Credit
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $arAccountId, // AR
                    'party_id' => $voucher->party_id,
                    'description' => 'AR Payment received',
                    'currency_code' => $voucher->currency_code,
                    'exchange_rate_to_base' => $voucher->exchange_rate,
                    'debit' => 0.00,
                    'credit' => $voucher->amount,
                ]);
            } else {
                // AP Account Debit
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $apAccountId, // AP
                    'party_id' => $voucher->party_id,
                    'description' => 'AP Vendor payment made',
                    'currency_code' => $voucher->currency_code,
                    'exchange_rate_to_base' => $voucher->exchange_rate,
                    'debit' => $voucher->amount,
                    'credit' => 0.00,
                ]);

                // Cash/Bank Account Credit
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $voucher->account_id,
                    'party_id' => $voucher->party_id,
                    'description' => "Cash/Bank Payment #{$voucher->voucher_number}",
                    'currency_code' => $voucher->currency_code,
                    'exchange_rate_to_base' => $voucher->exchange_rate,
                    'debit' => 0.00,
                    'credit' => $voucher->amount,
                ]);
            }

            $voucher->update(['journal_entry_id' => $entry->id]);

            AuditLoggerService::log($entry, 'post', "Posted Journal Entry {$entry->entry_number} for Voucher {$voucher->voucher_number}");

            return $entry;
        });
    }
}
