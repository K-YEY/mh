<?php

namespace Tests\Feature;

use App\Models\Party;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomsBrokersAndTruckersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'super-admin']);
    }

    public function test_customs_brokers_index_renders(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('customs-brokers.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Operations/CustomsBrokers/Index')
            ->has('brokers')
            ->has('ports')
        );
    }

    public function test_can_create_customs_broker_with_specialized_fields(): void
    {
        $data = [
            'code' => 'CBRK-ALEX-01',
            'name' => 'مكتب الإسكندرية للتخليص الجمركي المعتمد',
            'trade_name' => 'Alex Customs Services',
            'license_number' => 'LIC-7788',
            'operating_ports' => 'ميناء الإسكندرية / ميناء الدخيلة',
            'tax_number' => 'EG-998877',
            'commercial_registry' => 'CR-5544',
            'contact_person' => 'أحمد إبراهيم',
            'contact_phone' => '+20 122 990 1234',
            'phone' => '+20 3 488 9012',
            'email' => 'alex@customs-broker.com',
            'address' => 'ميناء الإسكندرية مبنى الخدمات',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customs-brokers.store'), $data);

        $response->assertRedirect();

        // Assert created in dedicated customs_brokers table
        $this->assertDatabaseHas('customs_brokers', [
            'code' => 'CBRK-ALEX-01',
            'license_number' => 'LIC-7788',
            'contact_person' => 'أحمد إبراهيم',
        ]);

        // Assert synchronized in parties table
        $this->assertDatabaseHas('parties', [
            'code' => 'CBRK-ALEX-01',
            'tax_number' => 'EG-998877',
        ]);

        $party = Party::where('code', 'CBRK-ALEX-01')->first();
        $this->assertTrue($party->hasRole('CUSTOMS_BROKER'));
    }

    public function test_truckers_index_renders(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('truckers.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Operations/Truckers/Index')
            ->has('truckers')
        );
    }

    public function test_can_create_trucker_with_fleet_and_dispatch_details(): void
    {
        $data = [
            'code' => 'TRK-REEFER-01',
            'name' => 'الفرسان للنقل المبرد الدولي',
            'trade_name' => 'Al-Fursan Reefer Haulage',
            'fleet_size' => 25,
            'truck_types' => 'تريلات مبردة 40HR ومولدات جينست',
            'coverage_areas' => 'مزارع وادي النطرون، النوبارية، ميناء الإسكندرية',
            'license_number' => 'MOT-TRK-9901',
            'tax_number' => 'EG-332211',
            'commercial_registry' => 'CR-9908',
            'dispatch_contact' => 'غرفة الحركة - كابتن ياسر',
            'dispatch_phone' => '+20 100 554 4332',
            'phone' => '+20 2 299 8811',
            'email' => 'dispatch@alfursan-haul.com',
            'address' => 'طريق مصر الإسكندرية الصحراوي',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('truckers.store'), $data);

        $response->assertRedirect();

        // Assert created in dedicated truckers table
        $this->assertDatabaseHas('truckers', [
            'code' => 'TRK-REEFER-01',
            'fleet_size' => 25,
            'dispatch_contact' => 'غرفة الحركة - كابتن ياسر',
        ]);

        // Assert synchronized in parties table
        $this->assertDatabaseHas('parties', [
            'code' => 'TRK-REEFER-01',
            'tax_number' => 'EG-332211',
        ]);

        $party = Party::where('code', 'TRK-REEFER-01')->first();
        $this->assertTrue($party->hasRole('TRUCKER'));
    }
}
