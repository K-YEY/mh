<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancialReportController extends Controller
{
    /**
     * Trial Balance Report (ميزان المراجعة بالمجاميع والأرصدة)
     */
    public function trialBalance(Request $request): Response
    {
        $fromDate = $request->input('from_date', date('Y-01-01'));
        $toDate = $request->input('to_date', date('Y-m-d'));

        $accounts = Account::where('is_active', true)
            ->orderBy('code', 'asc')
            ->get();

        $trialData = [];
        $totalOpeningDebit = 0.00;
        $totalOpeningCredit = 0.00;
        $totalPeriodDebit = 0.00;
        $totalPeriodCredit = 0.00;
        $totalEndingDebit = 0.00;
        $totalEndingCredit = 0.00;

        foreach ($accounts as $acc) {
            // Opening balance before fromDate
            $openingDebit = (float) JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', fn ($q) => $q->where('entry_date', '<', $fromDate))
                ->sum('debit_base');

            $openingCredit = (float) JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', fn ($q) => $q->where('entry_date', '<', $fromDate))
                ->sum('credit_base');

            // Period movements
            $periodDebit = (float) JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$fromDate, $toDate]))
                ->sum('debit_base');

            $periodCredit = (float) JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$fromDate, $toDate]))
                ->sum('credit_base');

            $netTotalDebit = $openingDebit + $periodDebit;
            $netTotalCredit = $openingCredit + $periodCredit;

            $endingDebit = 0.00;
            $endingCredit = 0.00;

            if (in_array($acc->type, ['ASSET', 'EXPENSE'])) {
                $balance = $netTotalDebit - $netTotalCredit;
                if ($balance >= 0) {
                    $endingDebit = $balance;
                } else {
                    $endingCredit = abs($balance);
                }
            } else {
                $balance = $netTotalCredit - $netTotalDebit;
                if ($balance >= 0) {
                    $endingCredit = $balance;
                } else {
                    $endingDebit = abs($balance);
                }
            }

            // Only show accounts that have activity or balance, or are top level
            if ($periodDebit > 0 || $periodCredit > 0 || $openingDebit > 0 || $openingCredit > 0 || $acc->parent_id === null) {
                $trialData[] = [
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name_ar' => $acc->name_ar,
                    'name_en' => $acc->name_en,
                    'type' => $acc->type,
                    'parent_id' => $acc->parent_id,
                    'opening_debit' => round($openingDebit, 2),
                    'opening_credit' => round($openingCredit, 2),
                    'period_debit' => round($periodDebit, 2),
                    'period_credit' => round($periodCredit, 2),
                    'ending_debit' => round($endingDebit, 2),
                    'ending_credit' => round($endingCredit, 2),
                ];

                $totalOpeningDebit += $openingDebit;
                $totalOpeningCredit += $openingCredit;
                $totalPeriodDebit += $periodDebit;
                $totalPeriodCredit += $periodCredit;
                $totalEndingDebit += $endingDebit;
                $totalEndingCredit += $endingCredit;
            }
        }

        return Inertia::render('Accounting/Reports/TrialBalance', [
            'report' => $trialData,
            'accounts_data' => $trialData,
            'filters' => [
                'from_date' => $fromDate,
                'as_of_date' => $toDate,
                'to_date' => $toDate,
            ],
            'totals' => [
                'opening_debit' => round($totalOpeningDebit, 2),
                'opening_credit' => round($totalOpeningCredit, 2),
                'period_debit' => round($totalPeriodDebit, 2),
                'period_credit' => round($totalPeriodCredit, 2),
                'ending_debit' => round($totalEndingDebit, 2),
                'ending_credit' => round($totalEndingCredit, 2),
                'is_balanced' => abs($totalPeriodDebit - $totalPeriodCredit) < 0.01,
                'difference' => round(abs($totalPeriodDebit - $totalPeriodCredit), 2),
            ],
        ]);
    }

    /**
     * Income Statement / Profit & Loss (قائمة الدخل والأرباح والخسائر)
     */
    public function incomeStatement(Request $request): Response
    {
        $fromDate = $request->input('from_date', date('Y-01-01'));
        $toDate = $request->input('to_date', date('Y-m-d'));

        // 1. Revenues (Type = REVENUE)
        $revenueAccounts = Account::where('type', 'REVENUE')
            ->whereNotNull('parent_id')
            ->orderBy('code')
            ->get();

        $revenues = [];
        $totalRevenue = 0.00;

        foreach ($revenueAccounts as $acc) {
            $credit = (float) JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$fromDate, $toDate]))
                ->sum('credit_base');

            $debit = (float) JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$fromDate, $toDate]))
                ->sum('debit_base');

            $netAmount = $credit - $debit;

            if ($netAmount != 0) {
                $revenues[] = [
                    'code' => $acc->code,
                    'name_ar' => $acc->name_ar,
                    'name_en' => $acc->name_en,
                    'amount' => round($netAmount, 2),
                ];
                $totalRevenue += $netAmount;
            }
        }

        // 2. Direct Freight Operational Costs (51xxx)
        $directCostAccounts = Account::where('type', 'EXPENSE')
            ->where(function ($q) {
                $q->where('code', 'LIKE', '51%');
            })
            ->whereNotNull('parent_id')
            ->orderBy('code')
            ->get();

        $directCosts = [];
        $totalDirectCosts = 0.00;

        foreach ($directCostAccounts as $acc) {
            $debit = (float) JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$fromDate, $toDate]))
                ->sum('debit_base');

            $credit = (float) JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$fromDate, $toDate]))
                ->sum('credit_base');

            $netAmount = $debit - $credit;

            if ($netAmount != 0) {
                $directCosts[] = [
                    'code' => $acc->code,
                    'name_ar' => $acc->name_ar,
                    'name_en' => $acc->name_en,
                    'amount' => round($netAmount, 2),
                ];
                $totalDirectCosts += $netAmount;
            }
        }

        // Gross Profit
        $grossProfit = $totalRevenue - $totalDirectCosts;

        // 3. General & Administrative Expenses (52xxx)
        $adminAccounts = Account::where('type', 'EXPENSE')
            ->where(function ($q) {
                $q->where('code', 'LIKE', '52%');
            })
            ->whereNotNull('parent_id')
            ->orderBy('code')
            ->get();

        $adminExpenses = [];
        $totalAdminExpenses = 0.00;

        foreach ($adminAccounts as $acc) {
            $debit = (float) JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$fromDate, $toDate]))
                ->sum('debit_base');

            $credit = (float) JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$fromDate, $toDate]))
                ->sum('credit_base');

            $netAmount = $debit - $credit;

            if ($netAmount != 0) {
                $adminExpenses[] = [
                    'code' => $acc->code,
                    'name_ar' => $acc->name_ar,
                    'name_en' => $acc->name_en,
                    'amount' => round($netAmount, 2),
                ];
                $totalAdminExpenses += $netAmount;
            }
        }

        $netIncome = $grossProfit - $totalAdminExpenses;

        return Inertia::render('Accounting/Reports/IncomeStatement', [
            'revenues' => [
                'items' => $revenues,
                'total' => round($totalRevenue, 2),
            ],
            'direct_costs' => [
                'items' => $directCosts,
                'total' => round($totalDirectCosts, 2),
            ],
            'gross_profit' => round($grossProfit, 2),
            'expenses' => [
                'items' => $adminExpenses,
                'total' => round($totalAdminExpenses, 2),
            ],
            'total_revenue' => round($totalRevenue, 2),
            'total_direct_costs' => round($totalDirectCosts, 2),
            'admin_expenses' => $adminExpenses,
            'total_admin_expenses' => round($totalAdminExpenses, 2),
            'net_profit' => round($netIncome, 2),
            'net_income' => round($netIncome, 2),
            'period' => [
                'start_date' => $fromDate,
                'end_date' => $toDate,
            ],
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
        ]);
    }

    /**
     * General Ledger Statement by Account (كشف حساب دفتر الأستاذ العام)
     */
    public function generalLedger(Request $request): Response
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        $defaultAccount = Account::where('code', '11140')->first() // USD Bank
            ?? Account::where('code', '11100')->first()
            ?? $accounts->first();

        $accountId = $request->input('account_id', $defaultAccount?->id);
        $fromDate = $request->input('from_date', date('Y-01-01'));
        $toDate = $request->input('to_date', date('Y-m-d'));

        $selectedAccount = Account::find($accountId);

        $lines = [];
        $openingBalance = 0.00;
        $totalDebit = 0.00;
        $totalCredit = 0.00;

        if ($selectedAccount) {
            // Opening balance before fromDate
            $opDebit = (float) JournalEntryLine::where('account_id', $selectedAccount->id)
                ->whereHas('journalEntry', fn ($q) => $q->where('entry_date', '<', $fromDate))
                ->sum('debit_base');

            $opCredit = (float) JournalEntryLine::where('account_id', $selectedAccount->id)
                ->whereHas('journalEntry', fn ($q) => $q->where('entry_date', '<', $fromDate))
                ->sum('credit_base');

            $isDebitNature = in_array($selectedAccount->type, ['ASSET', 'EXPENSE']);
            $openingBalance = $isDebitNature ? ($opDebit - $opCredit) : ($opCredit - $opDebit);

            $rawLines = JournalEntryLine::with(['journalEntry', 'party', 'currency'])
                ->where('account_id', $selectedAccount->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$fromDate, $toDate]))
                ->orderBy('id', 'asc')
                ->get();

            $runningBalance = $openingBalance;

            foreach ($rawLines as $l) {
                $debitBase = (float) $l->debit_base;
                $creditBase = (float) $l->credit_base;

                if ($isDebitNature) {
                    $runningBalance += ($debitBase - $creditBase);
                } else {
                    $runningBalance += ($creditBase - $debitBase);
                }

                $totalDebit += $debitBase;
                $totalCredit += $creditBase;

                $lines[] = [
                    'id' => $l->id,
                    'entry_date' => $l->journalEntry->entry_date,
                    'entry_number' => $l->journalEntry->entry_number,
                    'narration' => $l->journalEntry->narration,
                    'line_description' => $l->description,
                    'description' => $l->description,
                    'party_name' => $l->party?->legal_name,
                    'currency_code' => $l->currency_code,
                    'debit' => (float) $l->debit,
                    'credit' => (float) $l->credit,
                    'debit_base' => round($debitBase, 2),
                    'credit_base' => round($creditBase, 2),
                    'running_balance' => round($runningBalance, 2),
                ];
            }
        }

        $closingBalance = isset($runningBalance) ? round($runningBalance, 2) : round($openingBalance, 2);

        return Inertia::render('Accounting/Reports/GeneralLedger', [
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'selected_account' => $selectedAccount,
            'lines' => $lines,
            'openingBalance' => round($openingBalance, 2),
            'opening_balance' => round($openingBalance, 2),
            'closingBalance' => $closingBalance,
            'totals' => [
                'debit' => round($totalDebit, 2),
                'credit' => round($totalCredit, 2),
                'closing_balance' => $closingBalance,
            ],
            'filters' => [
                'account_id' => (int) $accountId,
                'start_date' => $fromDate,
                'end_date' => $toDate,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
        ]);
    }
}
