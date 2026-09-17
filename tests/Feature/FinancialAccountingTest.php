<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Party;
use App\Models\PaymentVoucher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialAccountingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::first() ?? User::factory()->create();
    }

    public function test_chart_of_accounts_is_properly_seeded_with_hierarchical_accounts(): void
    {
        $this->assertGreaterThanOrEqual(40, Account::count());

        // Verify key Egyptian Ocean Freight & Banking accounts exist
        $this->assertNotNull(Account::where('code', '11110')->first(), 'Cash Safe EGP must exist');
        $this->assertNotNull(Account::where('code', '11130')->first(), 'Banque Misr EGP must exist');
        $this->assertNotNull(Account::where('code', '11140')->first(), 'Banque Misr USD must exist');
        $this->assertNotNull(Account::where('code', '11200')->first(), 'Trade AR must exist');
        $this->assertNotNull(Account::where('code', '21100')->first(), 'Ocean Lines AP must exist');
        $this->assertNotNull(Account::where('code', '21200')->first(), 'Truckers AP must exist');
        $this->assertNotNull(Account::where('code', '21300')->first(), 'Customs Brokers AP must exist');
        $this->assertNotNull(Account::where('code', '41100')->first(), 'Ocean Freight Revenue must exist');
    }

    public function test_manual_journal_entry_create_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('ledger.journals.create'));
        $response->assertStatus(200);
    }

    public function test_manual_journal_entry_store_rejects_unbalanced_entry(): void
    {
        $safeAccount = Account::where('code', '11110')->first();
        $bankAccount = Account::where('code', '11130')->first();

        $response = $this->actingAs($this->user)->post(route('ledger.journals.store'), [
            'entry_date' => now()->toDateString(),
            'narration' => 'Unbalanced test entry',
            'lines' => [
                [
                    'account_id' => $safeAccount->id,
                    'party_id' => null,
                    'description' => 'Safe debit',
                    'currency_code' => 'USD',
                    'exchange_rate' => 1.0,
                    'debit' => 1000,
                    'credit' => 0,
                ],
                [
                    'account_id' => $bankAccount->id,
                    'party_id' => null,
                    'description' => 'Bank credit (unbalanced)',
                    'currency_code' => 'USD',
                    'exchange_rate' => 1.0,
                    'debit' => 0,
                    'credit' => 800, // Deliberate mismatch of 200
                ],
            ],
        ]);

        $response->assertSessionHasErrors('lines');
        $this->assertDatabaseMissing('journal_entries', [
            'narration' => 'Unbalanced test entry',
        ]);
    }

    public function test_manual_journal_entry_store_creates_balanced_entry_and_lines(): void
    {
        $safeAccount = Account::where('code', '11110')->first();
        $bankAccount = Account::where('code', '11130')->first();

        $response = $this->actingAs($this->user)->post(route('ledger.journals.store'), [
            'entry_date' => now()->toDateString(),
            'narration' => 'Cash transfer to Banque Misr',
            'reference_number' => 'REF-TR-001',
            'lines' => [
                [
                    'account_id' => $bankAccount->id,
                    'party_id' => null,
                    'description' => 'Deposit to Banque Misr',
                    'currency_code' => 'USD',
                    'exchange_rate' => 1.0,
                    'debit' => 2500.00,
                    'credit' => 0,
                ],
                [
                    'account_id' => $safeAccount->id,
                    'party_id' => null,
                    'description' => 'Withdrawal from Main Safe',
                    'currency_code' => 'USD',
                    'exchange_rate' => 1.0,
                    'debit' => 0,
                    'credit' => 2500.00,
                ],
            ],
        ]);

        $response->assertRedirect(route('ledger.journals'));

        $this->assertDatabaseHas('journal_entries', [
            'narration' => 'Cash transfer to Banque Misr',
            'source_type' => 'MANUAL',
        ]);

        $entry = JournalEntry::where('narration', 'Cash transfer to Banque Misr')->first();
        $this->assertCount(2, $entry->lines);
        $this->assertEquals(2500.00, $entry->lines()->sum('debit'));
        $this->assertEquals(2500.00, $entry->lines()->sum('credit'));
    }

    public function test_trial_balance_report_renders_and_verifies_balance(): void
    {
        $response = $this->actingAs($this->user)->get(route('accounting.reports.trial-balance'));
        $response->assertStatus(200);

        $response->assertInertia(function ($page) {
            $page->component('Accounting/Reports/TrialBalance')
                ->has('report')
                ->has('totals')
                ->where('totals.is_balanced', true);
        });
    }

    public function test_income_statement_report_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('accounting.reports.income-statement'));
        $response->assertStatus(200);

        $response->assertInertia(function ($page) {
            $page->component('Accounting/Reports/IncomeStatement')
                ->has('revenues')
                ->has('direct_costs')
                ->has('gross_profit')
                ->has('expenses')
                ->has('net_profit');
        });
    }

    public function test_general_ledger_report_renders_successfully(): void
    {
        $account = Account::first();
        $response = $this->actingAs($this->user)->get(route('accounting.reports.general-ledger', [
            'account_id' => $account->id,
        ]));
        $response->assertStatus(200);

        $response->assertInertia(function ($page) {
            $page->component('Accounting/Reports/GeneralLedger')
                ->has('accounts')
                ->has('selectedAccount')
                ->has('openingBalance')
                ->has('lines');
        });
    }

    public function test_payment_voucher_show_renders_with_official_details(): void
    {
        $party = Party::first();
        $account = Account::where('code', '11130')->first() ?? Account::first();

        $voucher = PaymentVoucher::create([
            'voucher_number' => 'RV-2026-TEST',
            'voucher_type' => 'RECEIPT',
            'party_id' => $party->id,
            'payment_method' => 'BANK_TRANSFER',
            'account_id' => $account->id,
            'amount' => 5000.00,
            'currency_code' => 'USD',
            'exchange_rate' => 1.0,
            'payment_date' => now()->toDateString(),
            'reference_number' => 'BM-WIRE-7788',
            'created_by_user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('vouchers.show', $voucher->id));
        $response->assertStatus(200);

        $response->assertInertia(function ($page) use ($voucher) {
            $page->component('Accounting/Vouchers/Show')
                ->where('voucher.voucher_number', $voucher->voucher_number)
                ->where('voucher.amount', '5000.00');
        });
    }

    public function test_user_can_switch_system_display_currency(): void
    {
        $response = $this->actingAs($this->user)->post(route('currencies.switch'), [
            'currency' => 'USD',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('display_currency', 'USD');

        // Verify that subsequent inertia request has current_currency as USD
        $pageResponse = $this->actingAs($this->user)->get(route('dashboard'));
        $pageResponse->assertStatus(200);
        $pageResponse->assertInertia(function ($page) {
            $page->where('current_currency.code', 'USD');
        });
    }
}
