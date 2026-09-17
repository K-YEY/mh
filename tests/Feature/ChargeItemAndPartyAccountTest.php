<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ChargeItem;
use App\Models\Party;
use App\Models\ShipmentInvoice;
use App\Models\ShipmentInvoiceItem;
use App\Models\User;
use App\Services\Financial\DoubleEntryPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChargeItemAndPartyAccountTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::first() ?? User::factory()->create();
    }

    public function test_charge_items_page_renders_with_accounts(): void
    {
        $response = $this->actingAs($this->user)->get(route('charge-items.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Accounting/ChargeItems/Index')
            ->has('charge_items')
            ->has('revenue_accounts')
            ->has('expense_accounts')
        );
    }

    public function test_can_create_and_update_charge_item(): void
    {
        $rev = Account::where('code', '41200')->first();
        $exp = Account::where('code', '51400')->first();

        $response = $this->actingAs($this->user)->post(route('charge-items.store'), [
            'code' => 'TEST_STEP',
            'name_ar' => 'خطوة تجريبية',
            'name_en' => 'Test Step',
            'revenue_account_id' => $rev->id,
            'expense_account_id' => $exp->id,
        ]);

        $response->assertRedirect();
        $item = ChargeItem::where('code', 'TEST_STEP')->first();
        $this->assertNotNull($item);
        $this->assertEquals($rev->id, $item->revenue_account_id);
        $this->assertEquals($exp->id, $item->expense_account_id);

        // Update
        $revNew = Account::where('code', '41100')->first();
        $updateResponse = $this->actingAs($this->user)->put(route('charge-items.update', $item->id), [
            'code' => 'TEST_STEP',
            'name_ar' => 'خطوة تجريبية محدثة',
            'name_en' => 'Updated Step',
            'revenue_account_id' => $revNew->id,
            'expense_account_id' => $exp->id,
        ]);

        $updateResponse->assertRedirect();
        $this->assertEquals($revNew->id, $item->fresh()->revenue_account_id);
    }

    public function test_party_creation_auto_creates_dedicated_sub_account(): void
    {
        $response = $this->actingAs($this->user)->post(route('parties.store'), [
            'code' => 'CLI-AUTO',
            'legal_name' => 'شركة التصدير السريع المحدودة',
            'role' => 'CUSTOMER',
            'auto_create_account' => true,
        ]);

        $response->assertRedirect();
        $party = Party::where('code', 'CLI-AUTO')->first();
        $this->assertNotNull($party);
        $this->assertNotNull($party->account_id);

        $account = $party->account;
        $this->assertNotNull($account);
        $this->assertStringStartsWith('11210-', $account->code);
        $this->assertEquals('ASSET', $account->type);
    }

    public function test_shipping_line_auto_creates_dedicated_payable_account(): void
    {
        $response = $this->actingAs($this->user)->post(route('parties.store'), [
            'code' => 'LINE-CMA',
            'legal_name' => 'CMA CGM Shipping Agency',
            'role' => 'SHIPPING_LINE',
            'auto_create_account' => true,
        ]);

        $response->assertRedirect();
        $party = Party::where('code', 'LINE-CMA')->first();
        $this->assertNotNull($party);
        $this->assertNotNull($party->account_id);

        $account = $party->account;
        $this->assertStringStartsWith('21100-', $account->code);
        $this->assertEquals('LIABILITY', $account->type);
    }

    public function test_invoice_posting_uses_party_dedicated_account(): void
    {
        $party = Party::create([
            'code' => 'CLI-EXP-1',
            'legal_name' => 'Al-Safwa Exporting',
            'is_active' => true,
        ]);

        $parent = Account::where('code', '11210')->first();
        $dedicatedAcc = Account::create([
            'code' => '11210-999',
            'name_ar' => 'حساب: شركة الصفوة للتصدير',
            'name_en' => 'Al-Safwa Account',
            'type' => 'ASSET',
            'parent_id' => $parent?->id,
            'is_active' => true,
        ]);
        $party->update(['account_id' => $dedicatedAcc->id]);

        $inv = ShipmentInvoice::create([
            'invoice_number' => 'INV-TEST-001',
            'invoice_type' => 'CUSTOMER_INVOICE',
            'party_id' => $party->id,
            'currency_code' => 'USD',
            'exchange_rate' => 50.00,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1000.00,
            'tax_total' => 0.00,
            'total_amount' => 1000.00,
            'status' => 'DRAFT',
        ]);

        $ci = ChargeItem::where('code', 'OFRT')->first();
        ShipmentInvoiceItem::create([
            'shipment_invoice_id' => $inv->id,
            'charge_item_id' => $ci->id,
            'description' => 'Ocean Freight',
            'quantity' => 1,
            'unit_price' => 1000.00,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'line_total' => 1000.00,
        ]);

        $poster = app(DoubleEntryPostingService::class);
        $entry = $poster->postInvoiceJournal($inv);

        $this->assertNotNull($entry);
        $debitLine = $entry->lines()->where('debit', '>', 0)->first();
        $this->assertEquals($dedicatedAcc->id, $debitLine->account_id);
    }
}
