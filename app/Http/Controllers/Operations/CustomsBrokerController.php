<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CustomsBroker;
use App\Models\Party;
use App\Models\PartyRole;
use App\Models\Port;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomsBrokerController extends Controller
{
    public function index(Request $request): Response
    {
        $brokers = CustomsBroker::when($request->search, function ($query, $search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('license_number', 'like', "%{$search}%")
                ->orWhere('operating_ports', 'like', "%{$search}%")
                ->orWhere('tax_number', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%");
        })
            ->latest()
            ->get();

        $ports = Port::where('is_active', true)->get();

        return Inertia::render('Operations/CustomsBrokers/Index', [
            'brokers' => $brokers,
            'ports' => $ports,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:customs_brokers,code|unique:parties,code',
            'name' => 'required|string',
            'trade_name' => 'nullable|string',
            'license_number' => 'nullable|string',
            'operating_ports' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'commercial_registry' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $customsAccount = Account::where('code', '21300')->first();

        // 1. Create corresponding Party record for sub-ledger & billing compatibility
        $party = Party::create([
            'code' => $validated['code'],
            'legal_name' => $validated['name'],
            'trade_name' => $validated['trade_name'] ?? null,
            'tax_number' => $validated['tax_number'] ?? null,
            'commercial_registry' => $validated['commercial_registry'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'account_id' => $customsAccount?->id,
            'is_active' => true,
        ]);

        PartyRole::create([
            'party_id' => $party->id,
            'role' => 'CUSTOMS_BROKER',
        ]);

        // 2. Create Customs Broker in dedicated table
        CustomsBroker::create(array_merge($validated, [
            'party_id' => $party->id,
            'is_active' => true,
        ]));

        return redirect()->back()->with('success', 'تم تسجيل المخلص الجمركي في جدول المخلصين بنجاح.');
    }

    public function update(Request $request, CustomsBroker $customsBroker): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:customs_brokers,code,'.$customsBroker->id,
            'name' => 'required|string',
            'trade_name' => 'nullable|string',
            'license_number' => 'nullable|string',
            'operating_ports' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'commercial_registry' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $customsBroker->update($validated);

        if ($customsBroker->party) {
            $customsBroker->party->update([
                'legal_name' => $validated['name'],
                'trade_name' => $validated['trade_name'] ?? null,
                'tax_number' => $validated['tax_number'] ?? null,
                'commercial_registry' => $validated['commercial_registry'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'تم تعديل بيانات المخلص الجمركي بنجاح.');
    }

    public function destroy(CustomsBroker $customsBroker): RedirectResponse
    {
        $customsBroker->delete();

        return redirect()->back()->with('success', 'تم حذف المخلص الجمركي بنجاح.');
    }
}
