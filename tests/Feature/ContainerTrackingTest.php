<?php

namespace Tests\Feature;

use App\Models\ContainerTrackingHistory;
use App\Models\MasterContainer;
use App\Models\User;
use App\Services\Tracking\ContainerTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContainerTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::first();
    }

    public function test_tracking_page_is_accessible(): void
    {
        $response = $this->actingAs($this->user)->get(route('tracking.index'));
        $response->assertOk();
    }

    public function test_tariffs_route_redirects_to_tracking(): void
    {
        $response = $this->actingAs($this->user)->get('/operations/tariffs');
        $response->assertRedirect('/operations/tracking');
    }

    public function test_tracking_container_creates_history_record(): void
    {
        $container = MasterContainer::first();
        $containerNumber = $container ? $container->container_number : 'MSKU8801923';

        $response = $this->actingAs($this->user)->get(route('tracking.index', [
            'container_number' => $containerNumber,
        ]));

        $response->assertOk();

        $this->assertDatabaseHas('container_tracking_histories', [
            'container_number' => $containerNumber,
        ]);
    }

    public function test_can_add_tracking_event_manually(): void
    {
        $container = MasterContainer::first();
        $this->assertNotNull($container);

        $response = $this->actingAs($this->user)->post(route('tracking.add-event'), [
            'master_container_id' => $container->id,
            'event_code' => 'GATE_IN',
            'event_name' => 'Container Gated In at Alexandria Reefer Depot',
            'location_name' => 'Alexandria Port (EGALY)',
            'occurred_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Temperature verified +4C',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('container_tracking_events', [
            'master_container_id' => $container->id,
            'event_code' => 'GATE_IN',
        ]);

        $this->assertDatabaseHas('master_containers', [
            'id' => $container->id,
            'current_status' => 'GATE_IN',
        ]);
    }

    public function test_can_delete_tracking_history_item(): void
    {
        $history = ContainerTrackingHistory::create([
            'user_id' => $this->user->id,
            'container_number' => 'TEST1234567',
            'shipping_line' => 'Maersk',
            'current_status' => 'IN_TRANSIT',
            'last_tracked_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->delete(route('tracking.delete-history', $history->id));
        $response->assertRedirect();

        $this->assertDatabaseMissing('container_tracking_histories', [
            'id' => $history->id,
        ]);
    }

    public function test_tracking_vessel_scrapes_and_creates_history_record(): void
    {
        $vesselName = 'EVER GIVEN';

        $response = $this->actingAs($this->user)->get(route('tracking.index', [
            'vessel_name' => $vesselName,
            'search_type' => 'vessel',
        ]));

        $response->assertOk();

        $this->assertDatabaseHas('container_tracking_histories', [
            'tracking_type' => 'vessel',
            'vessel_name' => $vesselName,
        ]);
    }

    public function test_can_clear_all_tracking_history(): void
    {
        ContainerTrackingHistory::create([
            'user_id' => $this->user->id,
            'container_number' => 'CLEAR001',
            'last_tracked_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->delete(route('tracking.clear-all-history'));
        $response->assertRedirect(route('tracking.index'));

        $this->assertEquals(0, ContainerTrackingHistory::count());
    }

    public function test_iso_6346_validation_identifies_valid_and_invalid_containers(): void
    {
        $service = app(ContainerTrackingService::class);

        // Valid ISO 6346 container number (CSQU 305438-3)
        $validResult = $service->validateIso6346('CSQU3054383');
        $this->assertTrue($validResult['is_valid']);
        $this->assertEquals('CSQ', $validResult['owner_code']);
        $this->assertEquals('U', $validResult['category']);
        $this->assertEquals('305438', $validResult['serial']);
        $this->assertEquals(3, $validResult['check_digit']);
        $this->assertEquals(3, $validResult['calculated_check_digit']);

        // Invalid check digit (MSCU1234560 vs calculated)
        $invalidResult = $service->validateIso6346('MSCU1234569');
        $this->assertFalse($invalidResult['is_valid']);
        $this->assertNotNull($invalidResult['error']);

        // Malformed container number
        $malformedResult = $service->validateIso6346('INVALID');
        $this->assertFalse($malformedResult['is_valid']);
    }

    public function test_carrier_auto_detection_and_deep_link(): void
    {
        $service = app(ContainerTrackingService::class);

        // MSC prefix
        $mscCarrier = $service->detectCarrier('MSCU1234567');
        $this->assertNotNull($mscCarrier);
        $this->assertEquals('MSC', $mscCarrier->code);

        $deepLink = $service->generateDeepLink('MSCU1234567', $mscCarrier);
        $this->assertStringContainsString('msc.com', $deepLink);
        $this->assertStringContainsString('MSCU1234567', $deepLink);

        // Maersk prefix
        $maerskCarrier = $service->detectCarrier('MAEU9988776');
        $this->assertNotNull($maerskCarrier);
        $this->assertEquals('MAERSK', $maerskCarrier->code);
    }

    public function test_api_container_tracking_returns_json_with_milestones(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('tracking.api.show', 'CSQU3054383'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'data' => [
                'container_number',
                'iso',
                'carrier',
                'deep_link',
                'milestones',
            ],
        ]);

        $this->assertDatabaseHas('container_milestones', [
            'container_number' => 'CSQU3054383',
        ]);
    }
}
