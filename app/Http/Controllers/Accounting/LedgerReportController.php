<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Currency;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Party;
use App\Services\Audit\AuditLoggerService;
use App\Services\Sequencing\SequenceGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LedgerReportController extends Controller
{
    public function journalEntries(): Response
    {
        $entries = JournalEntry::with(['lines.account', 'lines.party', 'lines.currency'])
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return Inertia::render('Accounting/Ledger/JournalEntries', [
            'entries' => $entries,
        ]);
    }

    public function createJournalEntry(): Response
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();
        $parties = Party::where('is_active', true)->orderBy('legal_name')->get();
        $currencies = Currency::where('is_active', true)->get();

        return Inertia::render('Accounting/Ledger/CreateJournal', [
            'accounts' => $accounts,
            'parties' => $parties,
            'currencies' => $currencies,
        ]);
    }

    public function storeJournalEntry(Request $request, SequenceGeneratorService $sequenceGenerator): RedirectResponse
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'narration' => 'required|string|max:500',
            'reference_number' => 'nullable|string|max:100',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.party_id' => 'nullable|exists:parties,id',
            'lines.*.description' => 'nullable|string|max:255',
            'lines.*.currency_code' => 'required|string|size:3',
            'lines.*.exchange_rate_to_base' => 'nullable|numeric|min:0.000001',
            'lines.*.exchange_rate' => 'nullable|numeric|min:0.000001',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ]);

        $totalDebitBase = 0.00;
        $totalCreditBase = 0.00;

        foreach ($validated['lines'] as $line) {
            $debit = (float) $line['debit'];
            $credit = (float) $line['credit'];
            $rate = (float) ($line['exchange_rate_to_base'] ?? $line['exchange_rate'] ?? 1.0);

            if ($debit > 0 && $credit > 0) {
                throw ValidationException::withMessages([
                    'lines' => 'لا يمكن للبند أن يحتوي على قيمة مدين ودائن معاً في نفس السطر.',
                ]);
            }

            if ($debit == 0 && $credit == 0) {
                throw ValidationException::withMessages([
                    'lines' => 'يجب إدخال قيمة في المدين أو الدائن لكل سطر.',
                ]);
            }

            $totalDebitBase += round($debit * $rate, 2);
            $totalCreditBase += round($credit * $rate, 2);
        }

        if (abs($totalDebitBase - $totalCreditBase) > 0.01) {
            throw ValidationException::withMessages([
                'lines' => "القيد غير متوازن: إجمالي المدين ({$totalDebitBase}) لا يساوي إجمالي الدائن ({$totalCreditBase}).",
            ]);
        }

        $entry = DB::transaction(function () use ($validated, $sequenceGenerator) {
            $year = (int) date('Y', strtotime($validated['entry_date']));
            $entryNo = $sequenceGenerator->getNextSequence('JOURNAL_ENTRY', $year);

            $entry = JournalEntry::create([
                'entry_number' => $entryNo,
                'entry_date' => $validated['entry_date'],
                'source_type' => 'MANUAL_JOURNAL',
                'source_id' => null,
                'narration' => $validated['narration'],
                'is_posted' => true,
                'posted_by_user_id' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            foreach ($validated['lines'] as $line) {
                $rate = (float) ($line['exchange_rate_to_base'] ?? $line['exchange_rate'] ?? 1.0);

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'party_id' => $line['party_id'] ?? null,
                    'description' => $line['description'] ?? $validated['narration'],
                    'currency_code' => $line['currency_code'],
                    'exchange_rate_to_base' => $rate,
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                ]);
            }

            AuditLoggerService::log($entry, 'create', "Manual Journal Entry {$entry->entry_number} posted");

            return $entry;
        });

        return redirect()->route('ledger.journals')
            ->with('success', "تم إنشاء وترحيل قيد اليومية {$entry->entry_number} بنجاح.");
    }

    public function subLedgerStatement(Request $request): Response
    {
        $partyId = $request->input('party_id');
        $parties = Party::where('is_active', true)->get();

        $lines = collect();
        $selectedParty = null;
        $totalDebitBase = 0.00;
        $totalCreditBase = 0.00;

        if ($partyId) {
            $selectedParty = Party::find($partyId);
            $lines = JournalEntryLine::with(['journalEntry', 'account', 'currency'])
                ->where('party_id', $partyId)
                ->orderBy('id', 'asc')
                ->get();

            $totalDebitBase = $lines->sum('debit_base');
            $totalCreditBase = $lines->sum('credit_base');
        }

        return Inertia::render('Accounting/Ledger/Statement', [
            'parties' => $parties,
            'selected_party' => $selectedParty,
            'lines' => $lines,
            'totals' => [
                'debit' => round($totalDebitBase, 2),
                'credit' => round($totalCreditBase, 2),
                'balance' => round($totalDebitBase - $totalCreditBase, 2),
            ],
        ]);
    }
}
