<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\MasterContainer;
use App\Models\MasterShipment;
use App\Models\Party;
use App\Models\ShipmentInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuickSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'shipments' => [],
                'invoices' => [],
                'containers' => [],
                'parties' => [],
                'accounts' => [],
            ]);
        }

        $term = "%{$query}%";

        // 1. Search Master Shipments
        $shipments = MasterShipment::query()
            ->where('file_number', 'like', $term)
            ->orWhere('master_bl_number', 'like', $term)
            ->orWhere('vessel_name', 'like', $term)
            ->take(5)
            ->get(['id', 'file_number', 'master_bl_number', 'vessel_name', 'status'])
            ->map(function ($shipment) {
                return [
                    'id' => $shipment->id,
                    'title' => $shipment->file_number,
                    'subtitle' => ($shipment->master_bl_number ? "MBL: {$shipment->master_bl_number} • " : '').($shipment->vessel_name ?: 'Vessel TBA'),
                    'badge' => $shipment->status,
                    'href' => route('master-shipments.show', $shipment->id),
                    'category' => 'shipments',
                ];
            });

        // 2. Search Reefer Containers
        $containers = MasterContainer::query()
            ->where('container_number', 'like', $term)
            ->take(5)
            ->with('masterShipment:id,file_number')
            ->get(['id', 'master_shipment_id', 'container_number', 'container_type', 'seal_number'])
            ->map(function ($container) {
                return [
                    'id' => $container->id,
                    'title' => $container->container_number,
                    'subtitle' => ($container->masterShipment ? "File: {$container->masterShipment->file_number} • " : '').($container->container_type ?: 'Reefer'),
                    'badge' => $container->container_type ?: '40RH',
                    'href' => $container->master_shipment_id ? route('master-shipments.show', $container->master_shipment_id) : route('tracking.index'),
                    'category' => 'containers',
                ];
            });

        // 3. Search Invoices & Vendor Bills
        $invoices = ShipmentInvoice::query()
            ->where('invoice_number', 'like', $term)
            ->take(5)
            ->get(['id', 'invoice_number', 'invoice_type', 'total_amount', 'currency_code', 'status'])
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'title' => $invoice->invoice_number,
                    'subtitle' => ($invoice->invoice_type === 'CUSTOMER_INVOICE' ? 'Customer Invoice' : 'Vendor Bill')." • {$invoice->currency_code} ".number_format($invoice->total_amount, 2),
                    'badge' => $invoice->status,
                    'href' => route('invoices.show', $invoice->id),
                    'category' => 'invoices',
                ];
            });

        // 4. Search Parties
        $parties = Party::query()
            ->where('legal_name', 'like', $term)
            ->orWhere('commercial_name', 'like', $term)
            ->orWhere('tax_id', 'like', $term)
            ->take(5)
            ->get(['id', 'legal_name', 'commercial_name', 'tax_id'])
            ->map(function ($party) {
                return [
                    'id' => $party->id,
                    'title' => $party->commercial_name ?: $party->legal_name,
                    'subtitle' => $party->tax_id ? "Tax ID: {$party->tax_id}" : 'Stakeholder Party',
                    'badge' => 'Party',
                    'href' => route('parties.index'),
                    'category' => 'parties',
                ];
            });

        // 5. Search Chart of Accounts
        $accounts = Account::query()
            ->where('code', 'like', $term)
            ->orWhere('name', 'like', $term)
            ->take(5)
            ->get(['id', 'code', 'name', 'account_type'])
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'title' => "{$account->code} - {$account->name}",
                    'subtitle' => $account->account_type ?: 'General Ledger Account',
                    'badge' => 'COA',
                    'href' => route('accounts.index'),
                    'category' => 'accounts',
                ];
            });

        return response()->json([
            'shipments' => $shipments,
            'containers' => $containers,
            'invoices' => $invoices,
            'parties' => $parties,
            'accounts' => $accounts,
        ]);
    }
}
