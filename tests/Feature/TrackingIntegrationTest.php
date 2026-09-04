<?php

namespace Tests\Feature;

use App\Models\MasterContainer;
use App\Models\MasterShipment;
use App\Models\Party;
use App\Models\Port;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Tracking\Drivers\SeaRatesDriver;
use App\Services\Tracking\TrackingManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_settings_encryption_and_decryption(): void
    {
        SystemSetting::set('integration_shipsgo', 'api_key', 'secret_key_12345', isSecret: true);

        $this->assertDatabaseHas('system_settings', [
            'group' => 'integration_shipsgo',
            'key' => 'api_key',
        ]);

        $decryptedValue = SystemSetting::get('integration_shipsgo', 'api_key');
        $this->assertEquals('secret_key_12345', $decryptedValue);
    }

    public function test_searates_driver_normalizes_raw_json_sample(): void
    {
        $rawPayload = [
            'ContainerInfo' => [
                'ContainerNumber' => 'MSKU9012345',
                'ShippingLine' => 'MAERSK',
                'Status' => 'In Transit',
                'VesselName' => 'MAERSK MC-KINNEY MOLLER',
                'Voyage' => '2401E',
            ],
            'Route' => [
                'POL' => [
                    'PortName' => 'Alexandria',
                    'UNLOCODE' => 'EGALY',
                    'ActualDeparture' => '2026-08-10T14:30:00Z',
                ],
                'POD' => [
                    'PortName' => 'Rotterdam',
                    'UNLOCODE' => 'NLRTM',
                    'EstimatedArrival' => '2026-08-25T08:00:00Z',
                ],
            ],
            'Milestones' => [
                [
                    'EventCode' => 'GTIN',
                    'EventName' => 'Gate In Full',
                    'Date' => '2026-08-08T10:00:00Z',
                    'Location' => 'Alexandria Terminal',
                    'IsActual' => true,
                ],
                [
                    'EventCode' => 'DEPT',
                    'EventName' => 'Vessel Departure',
                    'Date' => '2026-08-10T14:30:00Z',
                    'Location' => 'Alexandria Port',
                    'IsActual' => true,
                ],
            ],
        ];

        $driver = new SeaRatesDriver;
        $dto = $driver->normalizeResponse('MSKU9012345', $rawPayload);

        $this->assertEquals('MSKU9012345', $dto->container_number);
        $this->assertEquals('IN_TRANSIT', $dto->current_status);
        $this->assertEquals('MAERSK MC-KINNEY MOLLER', $dto->vessel_name);
        $this->assertEquals('2401E', $dto->voyage_number);
        $this->assertEquals('Alexandria', $dto->pol_name);
        $this->assertEquals('Rotterdam', $dto->pod_name);
        $this->assertCount(2, $dto->events_array);
        $this->assertEquals('GTIN', $dto->events_array[0]['event_code']);
    }

    public function test_tracking_manager_fallback_to_manual_driver(): void
    {
        $manager = app(TrackingManager::class);

        $this->assertEquals('manual', $manager->getDefaultDriver());

        $dto = $manager->fetchTracking('MSKU1234567');
        $this->assertEquals('MANUAL', $dto->current_status);
    }

    public function test_admin_can_update_integration_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.settings.integrations.update'), [
            'shipsgo' => [
                'is_enabled' => 'true',
                'api_key' => 'new_shipsgo_key_99',
                'webhook_secret' => 'wh_sec_77',
                'environment' => 'live',
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals('new_shipsgo_key_99', SystemSetting::get('integration_shipsgo', 'api_key'));
        $this->assertEquals('true', SystemSetting::get('integration_shipsgo', 'is_enabled'));
    }

    public function test_webhook_ingestion_syncs_container_events(): void
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();

        $shippingLine = Party::create([
            'code' => 'MSK',
            'legal_name' => 'Maersk Line',
        ]);

        $pol = Port::create([
            'code' => 'EGALY',
            'name' => 'Alexandria Port',
            'country_code' => 'EG',
        ]);

        $pod = Port::create([
            'code' => 'NLRTM',
            'name' => 'Rotterdam Port',
            'country_code' => 'NL',
        ]);

        $shipment = MasterShipment::create([
            'file_number' => 'MS-TEST-001',
            'direction' => 'EXPORT',
            'shipment_mode' => 'FCL',
            'status' => 'BOOKING_CONFIRMED',
            'shipping_line_id' => $shippingLine->id,
            'vessel_name' => 'MAERSK MC-KINNEY',
            'voyage_number' => '2401E',
            'pol_id' => $pol->id,
            'pod_id' => $pod->id,
            'etd' => now()->toDateString(),
            'eta' => now()->addDays(14)->toDateString(),
            'created_by_user_id' => $user->id,
        ]);

        $container = MasterContainer::create([
            'master_shipment_id' => $shipment->id,
            'container_number' => 'MSKU9012345',
            'container_type' => '40HR',
            'current_status' => 'PLANNED',
        ]);

        $payload = [
            'ContainerInfo' => [
                'ContainerNumber' => 'MSKU9012345',
                'Status' => 'In Transit',
                'VesselName' => 'MAERSK DISCOVERY',
            ],
            'Milestones' => [
                [
                    'EventCode' => 'GTIN',
                    'EventName' => 'Gate In Full',
                    'Date' => '2026-08-08T10:00:00Z',
                    'Location' => 'Alexandria Terminal',
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/webhooks/tracking/searates', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('master_containers', [
            'id' => $container->id,
            'current_status' => 'IN_TRANSIT',
        ]);

        $this->assertDatabaseHas('container_tracking_events', [
            'master_container_id' => $container->id,
            'event_code' => 'GTIN',
        ]);
    }
}
