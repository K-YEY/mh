<?php

namespace Tests\Feature;

use App\Models\ContainerTrackingHistory;
use App\Models\MasterContainer;
use App\Models\User;
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
}
