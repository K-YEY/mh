<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ChargeItem;
use App\Models\ContainerTrackingEvent;
use App\Models\Currency;
use App\Models\DocumentType;
use App\Models\ExchangeRate;
use App\Models\HouseShipment;
use App\Models\MasterContainer;
use App\Models\MasterShipment;
use App\Models\OceanFreightTariff;
use App\Models\Party;
use App\Models\PartyRole;
use App\Models\ShipmentInvoice;
use App\Models\ShipmentInvoiceItem;
use App\Models\User;
use App\Services\Financial\DoubleEntryPostingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Initial Admin Users
        $user = User::firstOrCreate(['email' => 'admin@agrierp.com'], [
            'name' => 'M&H System Admin',
            'password' => Hash::make('password'),
            'role' => 'super-admin',
        ]);

        User::firstOrCreate(['email' => 'admin@mh.com'], [
            'name' => 'M&H System Admin',
            'password' => Hash::make('password'),
            'role' => 'super-admin',
        ]);

        // 2. Currencies (Base: EGP)
        Currency::updateOrCreate(['code' => 'EGP'], ['name' => 'Egyptian Pound', 'symbol' => 'E£', 'decimal_places' => 2, 'is_base_currency' => true, 'is_active' => true]);
        Currency::updateOrCreate(['code' => 'USD'], ['name' => 'US Dollar', 'symbol' => '$', 'decimal_places' => 2, 'is_base_currency' => false, 'is_active' => true]);
        Currency::updateOrCreate(['code' => 'EUR'], ['name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2, 'is_base_currency' => false, 'is_active' => true]);

        ExchangeRate::updateOrCreate(['currency_code' => 'EGP', 'effective_date' => now()->toDateString()], ['rate_to_base' => 1.000000]);
        ExchangeRate::updateOrCreate(['currency_code' => 'USD', 'effective_date' => now()->toDateString()], ['rate_to_base' => 50.000000]);
        ExchangeRate::updateOrCreate(['currency_code' => 'EUR', 'effective_date' => now()->toDateString()], ['rate_to_base' => 54.500000]);

        // 3. Global Ports & Shipping Lines Seeders
        $this->call([
            WorldPortSeeder::class,
            ShippingLineSeeder::class,
        ]);

        // 4. Parties & Roles

        $shipper = Party::firstOrCreate(['code' => 'EXP-DELTA'], ['legal_name' => 'Delta Agri Export Farms', 'tax_number' => 'EG-309182']);
        PartyRole::firstOrCreate(['party_id' => $shipper->id, 'role' => 'CUSTOMER']);

        $consignee = Party::firstOrCreate(['code' => 'IMP-EUROFRESH'], ['legal_name' => 'EuroFresh Produce B.V.', 'tax_number' => 'NL-8819201']);
        PartyRole::firstOrCreate(['party_id' => $consignee->id, 'role' => 'CUSTOMER']);

        $packhouse = Party::firstOrCreate(['code' => 'PACK-ALFA'], ['legal_name' => 'Alfa Cold-Pack Station', 'tax_number' => 'EG-119283']);
        PartyRole::firstOrCreate(['party_id' => $packhouse->id, 'role' => 'PACKHOUSE']);

        $brokerAlex = Party::firstOrCreate(['code' => 'BROKER-ALEX'], [
            'legal_name' => 'مكتب الأهرام للتخليص الجمركي - ميناء الإسكندرية',
            'trade_name' => 'Al-Ahram Customs Brokerage',
            'tax_number' => 'EG-440912',
            'commercial_registry' => 'CR-88210',
            'email' => 'customs@alahram-eg.com',
            'phone' => '+20 122 345 6789',
            'address' => 'الإسكندرية - ميناء الدخيلة بوابة 1',
        ]);
        PartyRole::firstOrCreate(['party_id' => $brokerAlex->id, 'role' => 'CUSTOMS_BROKER']);

        $truckerNile = Party::firstOrCreate(['code' => 'TRK-REEFER-EGY'], [
            'legal_name' => 'النيل لأسطول نقل الحاويات المبردة (Reefer Haulage)',
            'trade_name' => 'Nile Reefer Transport',
            'tax_number' => 'EG-998811',
            'commercial_registry' => 'CR-11902',
            'email' => 'fleet@nilereefer.com',
            'phone' => '+20 101 234 5678',
            'address' => 'طريق الإسكندرية الصحراوي - مجمع النقل المبرد',
        ]);
        PartyRole::firstOrCreate(['party_id' => $truckerNile->id, 'role' => 'TRUCKER']);

        // 5. Ocean Tariffs
        OceanFreightTariff::firstOrCreate([
            'shipping_line_id' => $maersk->id,
            'pol_id' => $egaly->id,
            'pod_id' => $nlrtm->id,
            'container_type' => '40HR',
        ], [
            'currency_code' => 'USD',
            'buy_rate' => 2200.00,
            'transit_time_days' => 9,
            'valid_from' => now()->subDays(10),
            'valid_to' => now()->addDays(60),
            'free_days_demurrage' => 14,
            'notes' => 'Direct Reefer Service to Rotterdam',
        ]);

        OceanFreightTariff::firstOrCreate([
            'shipping_line_id' => $hapag->id,
            'pol_id' => $egaly->id,
            'pod_id' => $nlrtm->id,
            'container_type' => '40HR',
        ], [
            'currency_code' => 'USD',
            'buy_rate' => 2050.00,
            'transit_time_days' => 11,
            'valid_from' => now()->subDays(10),
            'valid_to' => now()->addDays(60),
            'free_days_demurrage' => 12,
            'notes' => 'Transshipment via Tangier',
        ]);

        // 6. Document Types
        DocumentType::firstOrCreate(['code' => 'PHYTO'], ['name_ar' => 'شهادة صحة نباتية', 'name_en' => 'Phytosanitary Certificate', 'requires_expiry_date' => true, 'requires_reference_no' => true]);
        DocumentType::firstOrCreate(['code' => 'PEST_LAB'], ['name_ar' => 'تقرير فحص متبقيات المبيدات', 'name_en' => 'Pesticide Residue Analysis Report', 'requires_expiry_date' => false, 'requires_reference_no' => true]);
        DocumentType::firstOrCreate(['code' => 'COO_EUR1'], ['name_ar' => 'شهادة المنشأ اليورو 1', 'name_en' => 'Certificate of Origin EURO 1', 'requires_expiry_date' => false, 'requires_reference_no' => true]);
        DocumentType::firstOrCreate(['code' => 'COO'], ['name_ar' => 'شهادة المنشأ', 'name_en' => 'Certificate of Origin', 'requires_expiry_date' => false, 'requires_reference_no' => true]);

        // 7. Master Chart of Accounts
        $this->call(ChartOfAccountsSeeder::class);

        $cRev = Account::where('code', '41100')->first();
        $cExp = Account::where('code', '51100')->first();

        // 8. Charge Items
        $ciFreight = ChargeItem::firstOrCreate(['code' => 'OFRT'], ['name_ar' => 'نولون شحن بحري مبرد', 'name_en' => 'Ocean Freight Reefer', 'revenue_account_id' => $cRev->id, 'expense_account_id' => $cExp->id]);
        $ciTHC = ChargeItem::firstOrCreate(['code' => 'THC'], ['name_ar' => 'رسوم تفريغ وتداول بالحاوية', 'name_en' => 'Terminal Handling Charge (THC)', 'revenue_account_id' => $cRev->id, 'expense_account_id' => $cExp->id]);

        // 9. Master Shipment & Reefer Container
        $master = MasterShipment::firstOrCreate(['file_number' => 'EXP-AGRI-2026-0001'], [
            'direction' => 'EXPORT',
            'shipment_mode' => 'FCL',
            'shipping_line_id' => $maersk->id,
            'customs_broker_id' => $brokerAlex->id,
            'trucker_id' => $truckerNile->id,
            'coo_type' => 'EUR.1',
            'compliance_status' => 'PENDING_DOCS',
            'booking_reference' => 'MSK-9901823',
            'master_bl_number' => 'MAEU90823120',
            'vessel_name' => 'MAERSK MC-KINNEY MOLLER',
            'voyage_number' => '2603-E',
            'pol_id' => $egaly->id,
            'pod_id' => $nlrtm->id,
            'etd' => now()->addDays(2),
            'eta' => now()->addDays(11),
            'status' => 'SAILED_IN_TRANSIT',
            'created_by_user_id' => $user->id,
        ]);

        $container = MasterContainer::firstOrCreate(['container_number' => 'MSKU8801923'], [
            'master_shipment_id' => $master->id,
            'container_type' => '40HR',
            'seal_number' => 'SL-99012',
            'is_reefer' => true,
            'target_temperature' => 3.0,
            'ventilation_flow' => 15,
            'humidity_percent' => 85,
            'genset_number' => 'GS-1102',
            'requires_cold_treatment' => true,
            'ct_probe_1' => '+1.1°C',
            'ct_probe_2' => '+1.2°C',
            'ct_probe_3' => '+1.1°C',
            'tare_weight_kg' => 4500.00,
            'max_payload_kg' => 28000.00,
            'free_days' => 14,
            'current_status' => 'SAILED_IN_TRANSIT',
        ]);

        ContainerTrackingEvent::firstOrCreate([
            'master_container_id' => $container->id,
            'event_code' => 'GATE_IN',
        ], [
            'event_name' => 'Container Gated In at Alexandria Reefer Yard',
            'location_name' => 'Alexandria Port (EGALY)',
            'occurred_at' => now()->subDays(1),
            'source' => 'MANUAL',
        ]);

        // 10. House Shipment
        $house = HouseShipment::firstOrCreate(['house_file_number' => 'EXP-AGRI-2026-0001-H01'], [
            'master_shipment_id' => $master->id,
            'house_bl_number' => 'HBL-2026-90123',
            'shipper_id' => $shipper->id,
            'consignee_id' => $consignee->id,
            'packhouse_id' => $packhouse->id,
            'customs_broker_id' => $brokerAlex->id,
            'trucker_id' => $truckerNile->id,
            'crop_type' => 'Valencia Oranges (برتقال فالنسيا)',
            'variety' => 'Class A Fresh Citrus',
            'lot_number' => 'LOT-EG-2026-09',
            'acid_number' => 'ACID-9901234',
            'customs_declaration_no' => '440192',
            'delivery_order_status' => 'HOLD_FINANCIAL',
            'total_packages' => 2400,
            'package_type' => 'TELESCOPIC_CARTONS',
            'gross_weight_kg' => 25200.00,
            'net_weight_kg' => 24000.00,
            'volume_cbm' => 48.5,
            'status' => 'PLANNED',
        ]);

        // 11. Customer Invoice & Items
        if (! ShipmentInvoice::where('invoice_number', 'INV-2026-00001')->exists()) {
            $invoice = ShipmentInvoice::create([
                'invoice_number' => 'INV-2026-00001',
                'invoice_type' => 'CUSTOMER_INVOICE',
                'party_id' => $shipper->id,
                'master_shipment_id' => $master->id,
                'house_shipment_id' => $house->id,
                'currency_code' => 'USD',
                'exchange_rate' => 1.000000,
                'issue_date' => now()->subDay(),
                'due_date' => now()->addDays(30),
                'subtotal' => 2650.00,
                'tax_total' => 0.00,
                'total_amount' => 2650.00,
                'status' => 'POSTED',
            ]);

            ShipmentInvoiceItem::create([
                'shipment_invoice_id' => $invoice->id,
                'charge_item_id' => $ciFreight->id,
                'description' => 'Ocean Freight 40HR Alexandria to Rotterdam',
                'quantity' => 1,
                'unit_price' => 2400.00,
                'line_total' => 2400.00,
            ]);

            ShipmentInvoiceItem::create([
                'shipment_invoice_id' => $invoice->id,
                'charge_item_id' => $ciTHC->id,
                'description' => 'POL Terminal Handling Charge',
                'quantity' => 1,
                'unit_price' => 250.00,
                'line_total' => 250.00,
            ]);

            // Post double-entry journal if posting service exists
            try {
                $postingService = app(DoubleEntryPostingService::class);
                $postingService->postInvoice($invoice);
            } catch (\Throwable $e) {
                // Ignore if service not registered or already posted
            }
        }
    }
}
