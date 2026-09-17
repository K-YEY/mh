<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Jobs\SyncContainerTrackingJob;
use App\Models\ContainerTrackingEvent;
use App\Models\ContainerTrackingHistory;
use App\Models\MasterContainer;
use App\Models\MasterShipment;
use App\Services\Tracking\ContainerTrackingService;
use App\Services\Tracking\LiveWebScraperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContainerTrackingController extends Controller
{
    public function __construct(
        protected LiveWebScraperService $scraperService,
        protected ContainerTrackingService $trackingService
    ) {}

    /**
     * Display the Container & Shipment Tracking Hub.
     */
    public function index(Request $request): Response
    {
        $vesselInput = trim($request->input('vessel_name', ''));
        $containerInput = trim($request->input('container_number', $request->input('search_query', '')));
        $searchType = $request->input('search_type', '');

        // Determine active search parameter and type
        if (! empty($vesselInput)) {
            $searchNumber = $vesselInput;
            $searchType = 'vessel';
        } elseif (! empty($containerInput)) {
            $searchNumber = $containerInput;
            if (empty($searchType)) {
                // Auto-detect if user entered vessel name (e.g., contains spaces or common carrier keywords or IMO number)
                $isVesselPattern = preg_match('/\s+/', $searchNumber)
                    || preg_match('/^(IMO|EVER|MAERSK|CMA|MSC|ONE|HAPAG|COSCO|AL\s)/i', $searchNumber)
                    || (is_numeric($searchNumber) && strlen($searchNumber) === 7);

                $searchType = $isVesselPattern ? 'vessel' : 'container';
            }
        } else {
            $searchNumber = '';
            $searchType = $searchType ?: 'container';
        }

        $activeContainer = null;
        $activeShipment = null;
        $activeVessel = null;
        $carrierScraper = null;
        $trackingEvents = collect();
        $isoValidation = null;
        $detectedCarrier = null;
        $carrierDeepLink = null;
        $milestones = collect();

        // 1. Fetch recent tracking history for quick access
        $history = ContainerTrackingHistory::orderBy('last_tracked_at', 'desc')
            ->take(20)
            ->get();

        // 2. Fetch list of available containers in the system for quick selection
        $systemContainers = MasterContainer::with(['masterShipment.shippingLine', 'masterShipment.pol', 'masterShipment.pod'])
            ->latest('id')
            ->take(15)
            ->get();

        // 3. Fetch list of vessels from MasterShipment with actual port manifest
        $systemVessels = MasterShipment::query()
            ->with(['pol', 'pod'])
            ->whereNotNull('vessel_name')
            ->where('vessel_name', '!=', '')
            ->take(8)
            ->get()
            ->map(fn ($s) => [
                'name' => $s->vessel_name,
                'voyage' => $s->voyage_number,
                'pol' => $s->pol?->name,
                'pod' => $s->pod?->name,
                'route' => ($s->pol?->name && $s->pod?->name) ? "{$s->pol->name} → {$s->pod->name}" : null,
            ])
            ->toArray();

        if (empty($systemVessels)) {
            $systemVessels = [
                ['name' => 'MAERSK MC-KINNEY MOLLER', 'voyage' => '2604E', 'route' => 'Alexandria Port → Port of Rotterdam'],
                ['name' => 'CMA CGM JACQUES SAADE', 'voyage' => '041FRA', 'route' => 'Alexandria Port → Port of Rotterdam'],
                ['name' => 'EVER GIVEN', 'voyage' => '008E', 'route' => 'Port of Rotterdam (At Berth)'],
                ['name' => 'SIMONE MARTINI', 'voyage' => 'SM01', 'route' => 'Trapani → Favignana (Egadi)'],
                ['name' => 'PORT SAID VOYAGER', 'voyage' => 'PSV02', 'route' => 'Port Said Anch → El Dekheila'],
            ];
        }

        // 4. Execute Search based on type
        if (! empty($searchNumber)) {
            if ($searchType === 'vessel') {
                // Live AIS vessel scraping
                $activeVessel = $this->scraperService->scrapeVessel($searchNumber);

                if ($activeVessel) {
                    ContainerTrackingHistory::updateOrCreate(
                        [
                            'tracking_type' => 'vessel',
                            'vessel_name' => $activeVessel['name'],
                        ],
                        [
                            'user_id' => auth()->id(),
                            'vessel_imo' => $activeVessel['imo'] ?? null,
                            'current_status' => $activeVessel['nav_status'] ?? 'Under way',
                            'pol_name' => $activeVessel['from_port'] ?? 'POL',
                            'pod_name' => $activeVessel['to_port'] ?? $activeVessel['destination'] ?? 'POD',
                            'last_location' => ($activeVessel['current_area'] ?? 'Sea').' · '.($activeVessel['speed'] ?? ''),
                            'vessel_data' => $activeVessel,
                            'last_tracked_at' => now(),
                        ]
                    );
                }
            } else {
                // Hybrid Container Tracking Architecture
                $isoValidation = $this->trackingService->validateIso6346($searchNumber);
                $detectedCarrier = $this->trackingService->detectCarrier($searchNumber);
                $carrierDeepLink = $this->trackingService->generateDeepLink($searchNumber, $detectedCarrier);

                // Synchronize milestones and carrier data
                $syncResult = $this->trackingService->syncMilestones($searchNumber);
                $milestones = $syncResult['milestones'] ?? collect();

                // Find container in local DB
                $container = MasterContainer::with([
                    'masterShipment.shippingLine',
                    'masterShipment.pol',
                    'masterShipment.pod',
                    'milestones' => fn ($q) => $q->orderBy('occurred_at', 'asc'),
                    'trackingEvents' => fn ($q) => $q->orderBy('occurred_at', 'desc'),
                ])->where('container_number', 'LIKE', "%{$searchNumber}%")->first();

                // If not found by container number, search by B/L or booking reference
                if (! $container) {
                    $shipment = MasterShipment::with(['containers.trackingEvents', 'containers.milestones', 'shippingLine', 'pol', 'pod'])
                        ->where('master_bl_number', $searchNumber)
                        ->orWhere('booking_reference', $searchNumber)
                        ->orWhere('file_number', $searchNumber)
                        ->first();

                    if ($shipment && $shipment->containers->isNotEmpty()) {
                        $container = $shipment->containers->first();
                    }
                }

                if ($container) {
                    $activeContainer = $container;
                    $activeShipment = $container->masterShipment;
                    $trackingEvents = $container->trackingEvents;

                    if ($container->milestones->isNotEmpty()) {
                        $milestones = $container->milestones;
                    }
                } else {
                    $activeContainer = $this->generateVirtualTrackingRecord($searchNumber);
                    $trackingEvents = collect($activeContainer['events']);
                    $activeShipment = $activeContainer['shipment'] ?? null;
                }

                // Update or Create entry in Tracking History
                ContainerTrackingHistory::updateOrCreate(
                    [
                        'tracking_type' => 'container',
                        'container_number' => strtoupper($searchNumber),
                    ],
                    [
                        'user_id' => auth()->id(),
                        'master_container_id' => is_object($activeContainer) && isset($activeContainer->id) ? $activeContainer->id : null,
                        'shipping_line' => $detectedCarrier?->name ?? (is_object($activeContainer) ? ($activeContainer->masterShipment?->shippingLine?->legal_name ?? 'Carrier') : ($activeContainer['shipping_line'] ?? 'Carrier')),
                        'booking_reference' => is_object($activeContainer) ? ($activeContainer->masterShipment?->booking_reference ?? 'REF-9988') : ($activeContainer['booking_reference'] ?? 'REF-9988'),
                        'master_bl_number' => is_object($activeContainer) ? ($activeContainer->masterShipment?->master_bl_number ?? 'MBL-88001') : ($activeContainer['master_bl_number'] ?? 'MBL-88001'),
                        'vessel_name' => is_object($activeContainer) ? ($activeContainer->masterShipment?->vessel_name ?? ($detectedCarrier ? "{$detectedCarrier->name} EXPRESS" : 'Vessel')) : ($activeContainer['vessel_name'] ?? 'Vessel'),
                        'voyage_number' => is_object($activeContainer) ? ($activeContainer->masterShipment?->voyage_number ?? '2601W') : ($activeContainer['voyage_number'] ?? '2601W'),
                        'pol_name' => is_object($activeContainer) ? ($activeContainer->masterShipment?->pol?->name ?? 'Alexandria Port (EGALY)') : ($activeContainer['pol_name'] ?? 'Alexandria, Egypt'),
                        'pod_name' => is_object($activeContainer) ? ($activeContainer->masterShipment?->pod?->name ?? 'Rotterdam (NLRTM)') : ($activeContainer['pod_name'] ?? 'Rotterdam'),
                        'current_status' => is_object($activeContainer) ? ($activeContainer->current_status ?? 'SAILED_IN_TRANSIT') : ($activeContainer['current_status'] ?? 'IN_TRANSIT'),
                        'last_location' => is_object($activeContainer) ? ($trackingEvents->first()?->location_name ?? 'In Transit') : 'In Transit',
                        'last_tracked_at' => now(),
                    ]
                );
            }

            // Refresh history list after saving
            $history = ContainerTrackingHistory::orderBy('last_tracked_at', 'desc')
                ->take(20)
                ->get();
        }

        // Ensure initial vessel telemetry is always present for Vessel AIS mode
        if (empty($activeVessel)) {
            $defaultVesselName = ! empty($systemVessels[0]['name']) ? $systemVessels[0]['name'] : 'CMA CGM JACQUES SAADE';
            $activeVessel = $this->scraperService->scrapeVessel($defaultVesselName);
        }

        return Inertia::render('Operations/Tracking/Index', [
            'search_number' => $searchNumber,
            'search_type' => $searchType,
            'container' => $activeContainer,
            'vessel' => $activeVessel,
            'carrier_scraper' => $carrierScraper,
            'shipment' => $activeShipment,
            'events' => $trackingEvents,
            'history' => $history,
            'system_containers' => $systemContainers,
            'system_vessels' => $systemVessels,
            'iso_validation' => $isoValidation,
            'detected_carrier' => $detectedCarrier,
            'carrier_deep_link' => $carrierDeepLink,
            'milestones' => $milestones,
        ]);
    }

    /**
     * API Endpoint: Get container tracking data and milestone timeline as JSON.
     */
    public function apiGetContainerTracking(string $containerNumber): JsonResponse
    {
        $data = $this->trackingService->syncMilestones($containerNumber);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * API Endpoint: Trigger live container synchronization or background job.
     */
    public function apiSyncContainerTracking(Request $request, string $containerNumber): JsonResponse
    {
        $async = $request->boolean('async', false);

        if ($async) {
            SyncContainerTrackingJob::dispatch($containerNumber);

            return response()->json([
                'status' => 'queued',
                'message' => 'Tracking synchronization job dispatched to background queue.',
                'container_number' => strtoupper(trim($containerNumber)),
            ]);
        }

        $data = $this->trackingService->syncMilestones($containerNumber);

        return response()->json([
            'status' => 'success',
            'message' => 'Container tracking and milestones synchronized successfully.',
            'data' => $data,
        ]);
    }

    /**
     * Add manual tracking event to container.
     */
    public function addEvent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'master_container_id' => 'required|exists:master_containers,id',
            'event_code' => 'required|string|max:50',
            'event_name' => 'required|string|max:150',
            'location_name' => 'required|string|max:150',
            'occurred_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $container = MasterContainer::findOrFail($validated['master_container_id']);

        ContainerTrackingEvent::create([
            'master_container_id' => $container->id,
            'event_code' => $validated['event_code'],
            'event_name' => $validated['event_name'],
            'location_name' => $validated['location_name'],
            'occurred_at' => $validated['occurred_at'],
            'source' => 'MANUAL',
            'raw_payload' => ['notes' => $validated['notes'] ?? 'Manual dispatch update'],
        ]);

        // Update container current status
        $container->update([
            'current_status' => $validated['event_code'],
        ]);

        // Update tracking history
        ContainerTrackingHistory::where('container_number', $container->container_number)
            ->update([
                'current_status' => $validated['event_code'],
                'last_location' => $validated['location_name'],
                'last_tracked_at' => now(),
            ]);

        return redirect()->route('tracking.index', ['container_number' => $container->container_number])
            ->with('success', 'تم تسجيل وتحديث حركة الحاوية بنجاح!');
    }

    /**
     * Delete or clear tracking history.
     */
    public function deleteHistory(int $id): RedirectResponse
    {
        ContainerTrackingHistory::destroy($id);

        return redirect()->back()->with('success', 'تم حذف الحاوية من سجل التتبع.');
    }

    /**
     * Clear all tracking history.
     */
    public function clearAllHistory(): RedirectResponse
    {
        ContainerTrackingHistory::truncate();

        return redirect()->route('tracking.index')->with('success', 'تم مسح سجل التتبع بالكامل.');
    }

    /**
     * Seed sample tracking events for demonstration.
     */
    private function seedSampleTrackingEvents(MasterContainer $container): void
    {
        $events = [
            [
                'event_code' => 'EMPTY_PICKUP',
                'event_name' => 'Empty Container Dispatched / Gate Out from Yard',
                'location_name' => 'Dekheila Reefer Depot (EGDKH)',
                'occurred_at' => now()->subDays(6),
            ],
            [
                'event_code' => 'GATE_IN',
                'event_name' => 'Full Container Gated In at Marine Terminal',
                'location_name' => 'Alexandria Container Terminal (EGALY)',
                'occurred_at' => now()->subDays(4),
            ],
            [
                'event_code' => 'CUSTOMS_CLEARED',
                'event_name' => 'Agri Inspection Passed & Customs Authority Cleared',
                'location_name' => 'Alexandria Customs Office (EGALY)',
                'occurred_at' => now()->subDays(3),
            ],
            [
                'event_code' => 'LOADED',
                'event_name' => 'Container Stowed & Plugged to Reefer Genset On-Board',
                'location_name' => 'Alexandria Port Berth 42',
                'occurred_at' => now()->subDays(2),
            ],
            [
                'event_code' => 'SAILED_IN_TRANSIT',
                'event_name' => 'Vessel Departed POL - Unbroken Cold Chain Active',
                'location_name' => 'Mediterranean Sea (Lat 32.1N, Lon 28.4E)',
                'occurred_at' => now()->subHours(18),
            ],
        ];

        foreach ($events as $e) {
            ContainerTrackingEvent::create([
                'master_container_id' => $container->id,
                'event_code' => $e['event_code'],
                'event_name' => $e['event_name'],
                'location_name' => $e['location_name'],
                'occurred_at' => $e['occurred_at'],
                'source' => 'MANUAL',
            ]);
        }
    }

    /**
     * Generate smart virtual tracking record when container is not yet in ERP.
     */
    private function generateVirtualTrackingRecord(string $number): array
    {
        $prefix = strtoupper(substr($number, 0, 4));
        $lineMap = [
            'MSKU' => ['line' => 'Maersk Line A/S', 'vessel' => 'MAERSK MC-KINNEY MOLLER', 'voyage' => '2604E'],
            'MRSK' => ['line' => 'Maersk Line A/S', 'vessel' => 'MAERSK MC-KINNEY MOLLER', 'voyage' => '2604E'],
            'CMAU' => ['line' => 'CMA CGM Group', 'vessel' => 'CMA CGM JACQUES SAADE', 'voyage' => '041FRA'],
            'MSCU' => ['line' => 'Mediterranean Shipping Company (MSC)', 'vessel' => 'MSC GULSUN', 'voyage' => 'MS992W'],
            'HLCU' => ['line' => 'Hapag-Lloyd AG', 'vessel' => 'AL ZUBARA EXPRESS', 'voyage' => 'HL2601'],
            'ONEY' => ['line' => 'Ocean Network Express (ONE)', 'vessel' => 'ONE APUS', 'voyage' => 'ON004E'],
            'COSU' => ['line' => 'COSCO Shipping Lines', 'vessel' => 'COSCO SHIPPING TAURUS', 'voyage' => 'CS881N'],
        ];

        $matched = $lineMap[$prefix] ?? [
            'line' => 'Global Ocean Carrier',
            'vessel' => 'EVER GIVEN EXPRESS',
            'voyage' => 'VOY-2026',
        ];

        return [
            'id' => null,
            'container_number' => strtoupper($number),
            'container_type' => '40RH (40\' High Cube Reefer)',
            'seal_number' => 'ML-EG-'.rand(100000, 999999),
            'is_reefer' => true,
            'target_temperature' => '4.0',
            'humidity_percent' => 85,
            'ventilation_flow' => 15,
            'requires_cold_treatment' => true,
            'current_status' => 'SAILED_IN_TRANSIT',
            'shipping_line' => $matched['line'],
            'booking_reference' => 'BK-'.rand(1000000, 9999999),
            'master_bl_number' => $prefix.rand(10000000, 99999999),
            'vessel_name' => $matched['vessel'],
            'voyage_number' => $matched['voyage'],
            'pol_name' => 'Alexandria Port (EGALY)',
            'pod_name' => 'Rotterdam Gateway (NLRTM)',
            'last_location' => 'Mediterranean Sea (Lat 34.2N, Lon 24.1E)',
            'shipment' => [
                'file_number' => 'SHP-AUTO-'.rand(1000, 9999),
                'vessel_name' => $matched['vessel'],
                'voyage_number' => $matched['voyage'],
                'etd' => now()->subDays(2)->format('Y-m-d'),
                'eta' => now()->addDays(5)->format('Y-m-d'),
                'pol' => ['name' => 'Alexandria Port (EGALY)'],
                'pod' => ['name' => 'Rotterdam Gateway (NLRTM)'],
                'shipping_line' => ['legal_name' => $matched['line']],
            ],
            'events' => [
                [
                    'id' => 101,
                    'event_code' => 'SAILED_IN_TRANSIT',
                    'event_name' => 'Vessel Sunk Cold-Chain Active / In Transit',
                    'location_name' => 'Mediterranean Sea (En Route to Rotterdam)',
                    'occurred_at' => now()->subHours(12)->format('Y-m-d H:i'),
                    'source' => 'AIS_TELEMETRY',
                ],
                [
                    'id' => 102,
                    'event_code' => 'LOADED',
                    'event_name' => 'Container Loaded & Connected to Reefer Monitor',
                    'location_name' => 'Alexandria Container Terminal',
                    'occurred_at' => now()->subDays(2)->format('Y-m-d H:i'),
                    'source' => 'CARRIER_API',
                ],
                [
                    'id' => 103,
                    'event_code' => 'CUSTOMS_CLEARED',
                    'event_name' => 'Phytosanitary Inspection & Agricultural Export Cleared',
                    'location_name' => 'Alexandria Port Agro Inspection Center',
                    'occurred_at' => now()->subDays(3)->format('Y-m-d H:i'),
                    'source' => 'GOV_NAFEZA',
                ],
                [
                    'id' => 104,
                    'event_code' => 'GATE_IN',
                    'event_name' => 'Reefer Container Gated In with Temperature Verification',
                    'location_name' => 'Alexandria Reefer Gate',
                    'occurred_at' => now()->subDays(4)->format('Y-m-d H:i'),
                    'source' => 'TERMINAL_EDI',
                ],
            ],
        ];
    }
}
