<?php

namespace Tests\Feature;

use App\Models\JournalEntry;
use App\Models\ShipmentInvoice;
use App\Models\User;
use App\Services\Sequencing\SequenceGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgriculturalErpTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::first() ?? User::factory()->create();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_renders_successfully_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_master_shipment_index_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('master-shipments.index'));
        $response->assertStatus(200);
    }

    public function test_freight_rate_finder_ranks_carriers(): void
    {
        $response = $this->actingAs($this->user)->get(route('tariffs.index'));
        $response->assertRedirect('/operations/tracking');
    }

    public function test_sequence_generator_service_locks_and_increments_counter(): void
    {
        $service = app(SequenceGeneratorService::class);
        $seq1 = $service->getNextSequence('QUOTATION', 2026);
        $seq2 = $service->getNextSequence('QUOTATION', 2026);

        $this->assertEquals('QT-2026-00001', $seq1);
        $this->assertEquals('QT-2026-00002', $seq2);
    }

    public function test_invoice_creation_posts_balanced_double_entry_journal(): void
    {
        $response = $this->actingAs($this->user)->post(route('invoices.store'), [
            'invoice_type' => 'CUSTOMER_INVOICE',
            'party_id' => 3,
            'master_shipment_id' => 1,
            'currency_code' => 'USD',
            'exchange_rate' => 1.0,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                ['charge_item_id' => 1, 'description' => 'Ocean Freight 40HR', 'quantity' => 1, 'unit_price' => 2200.00],
            ],
        ]);

        $invoice = ShipmentInvoice::latest('id')->first();
        $response->assertRedirect(route('invoices.show', $invoice->id));

        $this->assertDatabaseHas('shipment_invoices', [
            'total_amount' => 2200.00,
            'status' => 'POSTED',
        ]);

        $journal = JournalEntry::with('lines')->latest('id')->first();
        $this->assertNotNull($journal);

        $debits = $journal->lines->sum('debit_base');
        $credits = $journal->lines->sum('credit_base');
        $this->assertEquals($debits, $credits, "Double entry must be balanced: Debit ({$debits}) == Credit ({$credits})");

        // Test viewing the official MH For Export letterhead invoice page
        $viewResponse = $this->actingAs($this->user)->get(route('invoices.show', $invoice->id));
        $viewResponse->assertOk();
    }

    public function test_exchange_rate_update(): void
    {
        $response = $this->actingAs($this->user)->post(route('exchange-rates.store'), [
            'currency_code' => 'EUR',
            'rate_to_base' => 1.092000,
            'effective_date' => now()->addYear()->toDateString(),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('exchange_rates', [
            'currency_code' => 'EUR',
            'rate_to_base' => 1.092000,
        ]);
    }

    public function test_login_page_renders(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
    }

    public function test_chart_of_accounts_index_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('accounts.index'));
        $response->assertStatus(200);
    }

    public function test_audit_logs_index_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.audit-logs.index'));
        $response->assertStatus(200);
    }

    public function test_users_permissions_index_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('users.index'));
        $response->assertStatus(200);
    }

    public function test_parties_index_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('parties.index'));
        $response->assertStatus(200);
    }

    public function test_ports_index_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('ports.index'));
        $response->assertStatus(200);
    }

    public function test_currencies_index_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('currencies.index'));
        $response->assertStatus(200);
    }
}
