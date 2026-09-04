<?php

namespace App\Http\Controllers\Operations;

use App\Actions\Operations\AddMasterContainerAction;
use App\Actions\Operations\CreateMasterShipmentAction;
use App\Actions\Operations\LogContainerTrackingEventAction;
use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\MasterContainer;
use App\Models\MasterShipment;
use App\Models\Party;
use App\Models\Port;
use App\Models\ShipmentDocument;
use App\Services\Compliance\ShipmentComplianceService;
use App\Services\Operations\CostAllocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MasterShipmentController extends Controller
{
    public function index(Request $request): Response
    {
        $shipments = MasterShipment::with(['shippingLine', 'pol', 'pod', 'containers', 'houseShipments'])
            ->when($request->search, function ($query, $search) {
                $query->where('file_number', 'like', "%{$search}%")
                    ->orWhere('vessel_name', 'like', "%{$search}%")
                    ->orWhere('master_bl_number', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Operations/MasterShipments/Index', [
            'shipments' => $shipments,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        $shippingLines = Party::whereHas('roles', fn ($q) => $q->where('role', 'SHIPPING_LINE'))->get();
        $customsBrokers = Party::whereHas('roles', fn ($q) => $q->where('role', 'CUSTOMS_BROKER'))->get();
        $truckers = Party::whereHas('roles', fn ($q) => $q->where('role', 'TRUCKER'))->get();
        $ports = Port::where('is_active', true)->get();
        $cooDocumentType = DocumentType::where('code', 'COO')->first();

        return Inertia::render('Operations/MasterShipments/Create', [
            'shipping_lines' => $shippingLines,
            'customs_brokers' => $customsBrokers,
            'truckers' => $truckers,
            'ports' => $ports,
            'coo_document_type' => $cooDocumentType,
        ]);
    }

    public function store(
        Request $request,
        CreateMasterShipmentAction $action,
        ShipmentComplianceService $complianceService
    ): RedirectResponse {
        $validated = $request->validate([
            'direction' => 'required|in:EXPORT,IMPORT',
            'shipment_mode' => 'required|in:FCL,LCL',
            'shipping_line_id' => 'required|exists:parties,id',
            'customs_broker_id' => 'nullable|exists:parties,id',
            'trucker_id' => 'nullable|exists:parties,id',
            'booking_reference' => 'nullable|string',
            'master_bl_number' => 'nullable|string|unique:master_shipments,master_bl_number',
            'vessel_name' => 'required|string',
            'voyage_number' => 'required|string',
            'pol_id' => 'required|exists:ports,id',
            'pod_id' => 'required|exists:ports,id',
            'etd' => 'required|date',
            'eta' => 'required|date',
            // Mandatory Certificate of Origin (COO) Data & File/Scan
            'coo_type' => 'required|in:EUR.1,AGADIR,GAFTA,COMESA,GENERAL',
            'coo_reference_number' => 'required|string',
            'chamber_of_commerce' => 'nullable|string',
            'coo_issue_date' => 'nullable|date',
            'coo_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
            'coo_scanned_data' => 'nullable|string',
        ]);

        // Enforce mandatory file upload or camera scan for Certificate of Origin
        if (! $request->hasFile('coo_file') && empty($request->input('coo_scanned_data'))) {
            return redirect()->back()
                ->withErrors(['coo_file' => 'إرفاق شهادة المنشأ (سحب ملف PDF أو مسح ضوئي بالماسح/الكاميرا) إلزامي لإنشاء الشحنة.'])
                ->withInput();
        }

        // 1. Process and save the Certificate of Origin document
        $filePath = 'documents/coo_default.pdf';
        $fileName = 'COO_'.time().'.pdf';
        $fileSize = 102400;
        $mimeType = 'application/pdf';

        if ($request->hasFile('coo_file')) {
            $file = $request->file('coo_file');
            $filePath = $file->store('shipment_documents', 'public');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
        } elseif (! empty($request->input('coo_scanned_data'))) {
            // Process base64 data from camera scanner
            $scanData = $request->input('coo_scanned_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $scanData, $type)) {
                $scanData = substr($scanData, strpos($scanData, ',') + 1);
                $type = strtolower($type[1]);
                $decoded = base64_decode($scanData);
                $fileName = 'COO_Scan_'.date('Ymd_His').'.'.$type;
                $filePath = 'shipment_documents/'.$fileName;
                Storage::disk('public')->put($filePath, $decoded);
                $fileSize = strlen($decoded);
                $mimeType = 'image/'.$type;
            }
        }

        // 2. Create Master Shipment
        $shipmentData = collect($validated)->except([
            'coo_file',
            'coo_scanned_data',
            'coo_reference_number',
            'chamber_of_commerce',
            'coo_issue_date',
        ])->toArray();

        $shipment = $action->execute($shipmentData);

        // 3. Attach verified Certificate of Origin document
        $cooDocType = DocumentType::where('code', 'COO')->first()
            ?? DocumentType::firstOrCreate(['code' => 'COO'], [
                'name_ar' => 'شهادة المنشأ',
                'name_en' => 'Certificate of Origin',
                'requires_expiry_date' => false,
                'requires_reference_no' => true,
                'is_active' => true,
            ]);

        ShipmentDocument::create([
            'document_type_id' => $cooDocType->id,
            'documentable_type' => MasterShipment::class,
            'documentable_id' => $shipment->id,
            'reference_number' => $validated['coo_reference_number'],
            'issuing_authority' => $validated['chamber_of_commerce'] ?? 'الغرفة التجارية المصرية',
            'issue_date' => $validated['coo_issue_date'] ?? now()->format('Y-m-d'),
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size_bytes' => $fileSize,
            'mime_type' => $mimeType,
            'status' => 'VERIFIED',
            'metadata' => [
                'coo_type' => $validated['coo_type'],
                'chamber_of_commerce' => $validated['chamber_of_commerce'] ?? 'الغرفة التجارية',
                'uploaded_during' => 'MASTER_CREATION',
            ],
            'uploaded_by_user_id' => auth()->id() ?? 1,
            'verified_by_user_id' => auth()->id() ?? 1,
            'verified_at' => now(),
        ]);

        // Recalculate and update compliance status
        $complianceService->checkCompliance($shipment);

        return redirect()->route('master-shipments.show', $shipment->id)
            ->with('success', 'تم فتح ملف الشحنة الماستر واعتماد شهادة المنشأ الرسمية بنجاح!');
    }

    public function show(
        MasterShipment $masterShipment,
        CostAllocationService $costService,
        ShipmentComplianceService $complianceService
    ): Response {
        $masterShipment->load([
            'shippingLine',
            'customsBroker',
            'trucker',
            'pol',
            'pod',
            'containers.trackingEvents',
            'houseShipments.shipper',
            'houseShipments.consignee',
            'houseShipments.packhouse',
            'houseShipments.customsBroker',
            'houseShipments.trucker',
            'invoices.items.chargeItem',
            'documents.documentType',
        ]);

        $profitability = $costService->calculateShipmentProfitability($masterShipment);
        $compliance = $complianceService->checkCompliance($masterShipment);

        $shippers = Party::whereHas('roles', fn ($q) => $q->where('role', 'CUSTOMER'))->get();
        $consignees = Party::whereHas('roles', fn ($q) => $q->where('role', 'CUSTOMER'))->get();
        $customsBrokers = Party::whereHas('roles', fn ($q) => $q->whereIn('role', ['CUSTOMS_BROKER', 'CUSTOMER']))->get();
        $truckers = Party::whereHas('roles', fn ($q) => $q->whereIn('role', ['TRUCKER', 'CUSTOMER']))->get();
        $packhouses = Party::whereHas('roles', fn ($q) => $q->whereIn('role', ['PACKHOUSE', 'CUSTOMER']))->get();
        $documentTypes = DocumentType::where('is_active', true)->get();

        return Inertia::render('Operations/MasterShipments/Show', [
            'shipment' => $masterShipment,
            'profitability' => $profitability,
            'compliance' => $compliance,
            'shippers' => $shippers,
            'consignees' => $consignees,
            'customs_brokers' => $customsBrokers,
            'truckers' => $truckers,
            'packhouses' => $packhouses,
            'document_types' => $documentTypes,
        ]);
    }

    public function uploadDocument(
        Request $request,
        MasterShipment $masterShipment,
        ShipmentComplianceService $complianceService
    ): RedirectResponse {
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'reference_number' => 'nullable|string',
            'issuing_authority' => 'nullable|string',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
            'scanned_image' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        $filePath = 'documents/scanned_doc.pdf';
        $fileName = 'Scanned_Doc_'.time().'.pdf';
        $fileSize = 102400;
        $mimeType = 'application/pdf';

        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $filePath = $file->store('shipment_documents', 'public');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
        } elseif (! empty($request->input('scanned_image'))) {
            $scanData = $request->input('scanned_image');
            if (preg_match('/^data:image\/(\w+);base64,/', $scanData, $type)) {
                $scanData = substr($scanData, strpos($scanData, ',') + 1);
                $type = strtolower($type[1]);
                $decoded = base64_decode($scanData);
                $fileName = 'Doc_Scan_'.date('Ymd_His').'.'.$type;
                $filePath = 'shipment_documents/'.$fileName;
                Storage::disk('public')->put($filePath, $decoded);
                $fileSize = strlen($decoded);
                $mimeType = 'image/'.$type;
            }
        }

        ShipmentDocument::create([
            'document_type_id' => $validated['document_type_id'],
            'documentable_type' => MasterShipment::class,
            'documentable_id' => $masterShipment->id,
            'reference_number' => $validated['reference_number'] ?? null,
            'issuing_authority' => $validated['issuing_authority'] ?? null,
            'issue_date' => $validated['issue_date'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size_bytes' => $fileSize,
            'mime_type' => $mimeType,
            'status' => 'VERIFIED',
            'metadata' => $validated['metadata'] ?? null,
            'uploaded_by_user_id' => auth()->id() ?? 1,
            'verified_by_user_id' => auth()->id() ?? 1,
            'verified_at' => now(),
        ]);

        // Sync compliance status
        $complianceService->checkCompliance($masterShipment);

        return redirect()->back()->with('success', 'تم رفع المستند واعتماده وتحديث حالة الامتثال بنجاح.');
    }

    public function addContainer(Request $request, MasterShipment $masterShipment, AddMasterContainerAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'container_number' => 'required|string',
            'container_type' => 'required|in:20RF,40RF,40HR,20GP,40GP,40HC',
            'seal_number' => 'nullable|string',
            'is_reefer' => 'boolean',
            'target_temperature' => 'nullable|numeric',
            'ventilation_flow' => 'nullable|integer',
            'humidity_percent' => 'nullable|integer',
            'genset_number' => 'nullable|string',
            'requires_cold_treatment' => 'boolean',
            'ct_probe_1' => 'nullable|string',
            'ct_probe_2' => 'nullable|string',
            'ct_probe_3' => 'nullable|string',
        ]);

        $action->execute($masterShipment, $validated);

        return redirect()->back()->with('success', 'Container added to master booking successfully.');
    }

    public function addTrackingEvent(Request $request, MasterContainer $container, LogContainerTrackingEventAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'event_code' => 'required|string',
            'event_name' => 'required|string',
            'location_name' => 'nullable|string',
            'occurred_at' => 'required|date',
            'source' => 'nullable|in:MANUAL,FINDTEU,SHIPSGO,VIZION,SEARATES',
        ]);

        $action->execute($container, $validated);

        return redirect()->back()->with('success', 'Container tracking event logged.');
    }
}
