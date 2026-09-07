<?php

namespace App\Http\Controllers;

use App\Models\MasterContainer;
use App\Models\MasterShipment;
use App\Models\ShipmentDocument;
use App\Models\ShipmentInvoice;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $activeShipmentsCount = MasterShipment::whereNotIn('status', ['CLOSED_FINANCIALLY', 'DELIVERED'])->count();
        $activeContainersCount = MasterContainer::whereNull('returned_empty_at')->count();

        $pendingDocsCount = ShipmentDocument::where('status', 'PENDING_REVIEW')->count();

        $customerInvoicesTotal = ShipmentInvoice::where('invoice_type', 'CUSTOMER_INVOICE')
            ->where('status', 'POSTED')
            ->sum('total_amount');

        $vendorBillsTotal = ShipmentInvoice::where('invoice_type', 'VENDOR_BILL')
            ->where('status', 'POSTED')
            ->sum('total_amount');

        $netProfitTotal = $customerInvoicesTotal - $vendorBillsTotal;

        $recentShipments = MasterShipment::with(['shippingLine', 'pol', 'pod', 'containers'])
            ->latest()
            ->take(6)
            ->get();

        return Inertia::render('Dashboard/Index', [
            'metrics' => [
                'active_shipments' => $activeShipmentsCount,
                'active_containers' => $activeContainersCount,
                'pending_documents' => $pendingDocsCount,
                'total_revenue' => round($customerInvoicesTotal, 2),
                'total_cost' => round($vendorBillsTotal, 2),
                'net_profit' => round($netProfitTotal, 2),
            ],
            'recent_shipments' => $recentShipments,
        ]);
    }
}
