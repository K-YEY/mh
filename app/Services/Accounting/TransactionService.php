<?php

namespace App\Services\Accounting;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\Sequencing\SequenceGeneratorService;
use Exception;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        protected SequenceGeneratorService $sequenceGenerator
    ) {}

    /**
     * Create and post a balanced Double-Entry Journal Entry
     *
     * @param  array  $entryData  ['entry_date', 'source_type', 'source_id', 'narration', 'posted_by_user_id']
     * @param  array  $lines  Array of ['account_id', 'party_id', 'description', 'currency_code', 'exchange_rate_to_base', 'debit', 'credit']
     */
    public function createBalancedJournal(array $entryData, array $lines): JournalEntry
    {
        return DB::transaction(function () use ($entryData, $lines) {
            // Verify Zero-Sum Rule
            $totalDebitBase = 0.00;
            $totalCreditBase = 0.00;

            foreach ($lines as $line) {
                $fx = (float) ($line['exchange_rate_to_base'] ?? 1.0);
                $debit = (float) ($line['debit'] ?? 0.0);
                $credit = (float) ($line['credit'] ?? 0.0);

                $totalDebitBase += round($debit * $fx, 2);
                $totalCreditBase += round($credit * $fx, 2);
            }

            if (abs($totalDebitBase - $totalCreditBase) > 0.01) {
                throw new Exception("Double-Entry Equilibrium Failure: Total Debits (\${$totalDebitBase}) must equal Total Credits (\${$totalCreditBase}).");
            }

            $year = (int) date('Y', strtotime($entryData['entry_date'] ?? 'now'));
            $entryNumber = $this->sequenceGenerator->getNextSequence('JOURNAL_ENTRY', $year);

            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $entryData['entry_date'] ?? date('Y-m-d'),
                'source_type' => $entryData['source_type'] ?? 'MANUAL',
                'source_id' => $entryData['source_id'] ?? null,
                'narration' => $entryData['narration'],
                'is_posted' => true,
                'posted_by_user_id' => $entryData['posted_by_user_id'] ?? auth()->id(),
                'posted_at' => now(),
            ]);

            foreach ($lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'party_id' => $line['party_id'] ?? null,
                    'description' => $line['description'] ?? null,
                    'currency_code' => $line['currency_code'] ?? 'USD',
                    'exchange_rate_to_base' => $line['exchange_rate_to_base'] ?? 1.0,
                    'debit' => $line['debit'] ?? 0.00,
                    'credit' => $line['credit'] ?? 0.00,
                ]);
            }

            return $entry;
        });
    }
}
