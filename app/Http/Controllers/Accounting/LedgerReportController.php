<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Party;
use Illuminate\Http\Request;
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
