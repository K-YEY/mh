<?php

namespace App\Http\Controllers\Accounting;

use App\Actions\Accounting\PostInvoiceJournalAction;
use App\Http\Controllers\Controller;
use App\Models\ChargeItem;
use App\Models\MasterShipment;
use App\Models\Party;
use App\Models\ShipmentInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $invoices = ShipmentInvoice::with(['party', 'masterShipment', 'journalEntry', 'items.chargeItem'])
            ->when($request->type, fn ($q, $type) => $q->where('invoice_type', $type))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Accounting/Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['type']),
        ]);
    }

    public function create(): Response
    {
        $parties = Party::where('is_active', true)->get();
        $shipments = MasterShipment::latest()->take(20)->get();
        $chargeItems = ChargeItem::all();

        return Inertia::render('Accounting/Invoices/Create', [
            'parties' => $parties,
            'shipments' => $shipments,
            'charge_items' => $chargeItems,
        ]);
    }

    public function store(Request $request, PostInvoiceJournalAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_type' => 'required|in:CUSTOMER_INVOICE,VENDOR_BILL',
            'party_id' => 'required|exists:parties,id',
            'master_shipment_id' => 'nullable|exists:master_shipments,id',
            'currency_code' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.charge_item_id' => 'required|exists:charge_items,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.unit_price' => 'required|numeric|min:0.01',
        ]);

        $lineItems = $validated['items'];
        unset($validated['items']);

        $invoice = $action->execute($validated, $lineItems);

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'تم إصدار الفاتورة وترحيل القيد بنجاح.');
    }

    public function show(ShipmentInvoice $invoice): Response
    {
        $invoice->load([
            'party',
            'masterShipment.shippingLine',
            'masterShipment.customsBroker',
            'masterShipment.trucker',
            'masterShipment.pol',
            'masterShipment.pod',
            'masterShipment.containers',
            'houseShipment.party',
            'items.chargeItem',
            'journalEntry.lines.account',
        ]);

        return Inertia::render('Accounting/Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }
}
