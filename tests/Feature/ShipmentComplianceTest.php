<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\HouseShipment;
use App\Models\MasterShipment;
use App\Models\Party;
use App\Models\PartyRole;
use App\Models\Port;
use App\Models\ShipmentDocument;
use App\Models\User;
use App\Services\Compliance\ShipmentComplianceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ShipmentComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Party $shippingLine;

    protected Party $customsBroker;

    protected Party $trucker;

    protected Party $shipper;

    protected Party $consignee;

    protected Port $pol;

    protected Port $pod;

    protected DocumentType $cooDocType;

    protected DocumentType $phytoDocType;

    protected DocumentType $commInvDocType;

    protected DocumentType $packingListDocType;

    protected DocumentType $bolDocType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'super-admin']);

        $this->shippingLine = Party::create(['code' => 'LINE-TEST', 'legal_name' => 'Maersk Test']);
        PartyRole::create(['party_id' => $this->shippingLine->id, 'role' => 'SHIPPING_LINE']);

        $this->customsBroker = Party::create(['code' => 'BROKER-TEST', 'legal_name' => 'Test Customs Broker']);
        PartyRole::create(['party_id' => $this->customsBroker->id, 'role' => 'CUSTOMS_BROKER']);

        $this->trucker = Party::create(['code' => 'TRK-TEST', 'legal_name' => 'Test Trucking Reefer']);
        PartyRole::create(['party_id' => $this->trucker->id, 'role' => 'TRUCKER']);

        $this->shipper = Party::create(['code' => 'SHIPPER-TEST', 'legal_name' => 'Delta Farms']);
        PartyRole::create(['party_id' => $this->shipper->id, 'role' => 'CUSTOMER']);

        $this->consignee = Party::create(['code' => 'CONS-TEST', 'legal_name' => 'EuroFresh Imports']);
        PartyRole::create(['party_id' => $this->consignee->id, 'role' => 'CUSTOMER']);

        $this->pol = Port::create(['code' => 'EGALY', 'name' => 'Alexandria Port', 'country_code' => 'EG']);
        $this->pod = Port::create(['code' => 'NLRTM', 'name' => 'Rotterdam Port', 'country_code' => 'NL']);

        $this->cooDocType = DocumentType::firstOrCreate(['code' => 'COO'], [
            'name_ar' => 'شهادة المنشأ',
            'name_en' => 'Certificate of Origin',
            'requires_expiry_date' => false,
            'requires_reference_no' => true,
            'is_active' => true,
        ]);

        $this->phytoDocType = DocumentType::firstOrCreate(['code' => 'PHYTO'], [
            'name_ar' => 'شهادة الصحة النباتية',
            'name_en' => 'Phytosanitary Certificate',
            'requires_expiry_date' => true,
            'requires_reference_no' => true,
            'is_active' => true,
        ]);

        $this->commInvDocType = DocumentType::firstOrCreate(['code' => 'COMM_INV'], [
            'name_ar' => 'الفاتورة التجارية النهائية',
            'name_en' => 'Commercial Invoice',
            'requires_expiry_date' => false,
            'requires_reference_no' => true,
            'is_active' => true,
        ]);

        $this->packingListDocType = DocumentType::firstOrCreate(['code' => 'PACKING_LIST'], [
            'name_ar' => 'بيان العبوة',
            'name_en' => 'Packing List',
            'requires_expiry_date' => false,
            'requires_reference_no' => true,
            'is_active' => true,
        ]);

        $this->bolDocType = DocumentType::firstOrCreate(['code' => 'BOL'], [
            'name_ar' => 'بوليصة الشحن البحرية',
            'name_en' => 'Bill of Lading',
            'requires_expiry_date' => false,
            'requires_reference_no' => true,
            'is_active' => true,
        ]);
    }

    public function test_create_master_shipment_page_supplies_customs_brokers_and_truckers(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('master-shipments.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Operations/MasterShipments/Create')
            ->has('shipping_lines')
            ->has('customs_brokers')
            ->has('truckers')
            ->has('ports')
        );
    }

    public function test_cannot_create_master_shipment_without_mandatory_certificate_of_origin(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('master-shipments.store'), [
                'direction' => 'EXPORT',
                'shipment_mode' => 'FCL',
                'shipping_line_id' => $this->shippingLine->id,
                'vessel_name' => 'MSC MEDITERRANEAN',
                'voyage_number' => 'V-9901',
                'pol_id' => $this->pol->id,
                'pod_id' => $this->pod->id,
                'etd' => '2026-10-01',
                'eta' => '2026-10-10',
                // COO reference provided but NO file and NO scan provided
                'coo_type' => 'EUR.1',
                'coo_reference_number' => 'EG-COO-1234',
            ]);

        $response->assertSessionHasErrors('coo_file');
        $this->assertDatabaseCount('master_shipments', 0);
    }

    public function test_successful_master_shipment_creation_with_coo_file_attachment(): void
    {
        $file = UploadedFile::fake()->create('COO_EUR1_Document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->post(route('master-shipments.store'), [
                'direction' => 'EXPORT',
                'shipment_mode' => 'FCL',
                'shipping_line_id' => $this->shippingLine->id,
                'customs_broker_id' => $this->customsBroker->id,
                'trucker_id' => $this->trucker->id,
                'booking_reference' => 'BK-99018',
                'vessel_name' => 'MSC MEDITERRANEAN',
                'voyage_number' => 'V-9901',
                'pol_id' => $this->pol->id,
                'pod_id' => $this->pod->id,
                'etd' => '2026-10-01',
                'eta' => '2026-10-10',
                // Mandatory COO
                'coo_type' => 'EUR.1',
                'coo_reference_number' => 'EG-COO-EUR1-8890',
                'chamber_of_commerce' => 'غرفة تجارة الإسكندرية',
                'coo_issue_date' => '2026-09-04',
                'coo_file' => $file,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('master_shipments', 1);

        $shipment = MasterShipment::first();
        $this->assertEquals($this->customsBroker->id, $shipment->customs_broker_id);
        $this->assertEquals($this->trucker->id, $shipment->trucker_id);

        // Verify that Certificate of Origin document was created and attached
        $this->assertDatabaseHas('shipment_documents', [
            'documentable_id' => $shipment->id,
            'document_type_id' => $this->cooDocType->id,
            'reference_number' => 'EG-COO-EUR1-8890',
            'status' => 'VERIFIED',
        ]);
    }

    public function test_compliance_service_enforces_mandatory_documents_and_delivery_order_release(): void
    {
        $shipment = MasterShipment::create([
            'file_number' => 'EXP-MBL-TEST-001',
            'direction' => 'EXPORT',
            'shipment_mode' => 'FCL',
            'shipping_line_id' => $this->shippingLine->id,
            'vessel_name' => 'MAERSK ALABAMA',
            'voyage_number' => '2604',
            'pol_id' => $this->pol->id,
            'pod_id' => $this->pod->id,
            'etd' => '2026-10-01',
            'eta' => '2026-10-10',
            'status' => 'BOOKING_CONFIRMED',
            'created_by_user_id' => $this->user->id,
        ]);

        $house = HouseShipment::create([
            'master_shipment_id' => $shipment->id,
            'house_file_number' => 'EXP-HBL-TEST-001',
            'shipper_id' => $this->shipper->id,
            'consignee_id' => $this->consignee->id,
            'crop_type' => 'Citrus Oranges',
            'gross_weight_kg' => 24000.0,
            'volume_cbm' => 58.0,
            'total_packages' => 1200,
            'package_type' => 'CARTONS',
            'delivery_order_status' => 'HOLD_FINANCIAL',
        ]);

        $service = app(ShipmentComplianceService::class);

        // Initially no documents -> Not compliant, cannot release DO
        $check = $service->checkCompliance($shipment);
        $this->assertFalse($check['is_ready_for_release']);
        $this->assertGreaterThan(0, count($check['missing_mandatory']));

        // Attempting to release DO should fail
        $releaseCheck = $service->canReleaseDeliveryOrder($house);
        $this->assertFalse($releaseCheck['allowed']);

        // Now attach all mandatory documents (COO, PHYTO, MRL_TEST, COMM_INV, PACKING_LIST, BOL)
        $mrlDocType = DocumentType::firstOrCreate(['code' => 'MRL_TEST'], [
            'name_ar' => 'تقرير فحص متبقيات المبيدات',
            'name_en' => 'Pesticide Residue Report',
            'requires_expiry_date' => false,
            'requires_reference_no' => true,
            'is_active' => true,
        ]);
        $docTypes = [$this->cooDocType, $this->phytoDocType, $mrlDocType, $this->commInvDocType, $this->packingListDocType, $this->bolDocType];
        foreach ($docTypes as $dt) {
            ShipmentDocument::create([
                'document_type_id' => $dt->id,
                'documentable_type' => MasterShipment::class,
                'documentable_id' => $shipment->id,
                'reference_number' => 'REF-'.$dt->code,
                'file_path' => 'documents/test.pdf',
                'file_name' => 'test.pdf',
                'file_size_bytes' => 1024,
                'mime_type' => 'application/pdf',
                'status' => 'VERIFIED',
                'uploaded_by_user_id' => $this->user->id,
                'verified_by_user_id' => $this->user->id,
                'verified_at' => now(),
            ]);
        }

        // Now recheck compliance
        $checkAfter = $service->checkCompliance($shipment);
        $this->assertTrue($checkAfter['is_ready_for_release']);
        $this->assertEquals(100, $checkAfter['completion_percent']);

        // Now delivery order release check should be allowed
        $releaseCheckAfter = $service->canReleaseDeliveryOrder($house);
        $this->assertTrue($releaseCheckAfter['allowed']);
    }
}
