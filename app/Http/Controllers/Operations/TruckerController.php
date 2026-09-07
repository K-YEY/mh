<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\PartyRole;
use App\Models\Trucker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TruckerController extends Controller
{
    public function index(Request $request): Response
    {
        $truckers = Trucker::when($request->search, function ($query, $search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('truck_types', 'like', "%{$search}%")
                ->orWhere('coverage_areas', 'like', "%{$search}%")
                ->orWhere('dispatch_contact', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        })
            ->latest()
            ->get();

        return Inertia::render('Operations/Truckers/Index', [
            'truckers' => $truckers,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:truckers,code|unique:parties,code',
            'name' => 'required|string',
            'trade_name' => 'nullable|string',
            'fleet_size' => 'nullable|integer|min:1',
            'truck_types' => 'nullable|string',
            'coverage_areas' => 'nullable|string',
            'license_number' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'commercial_registry' => 'nullable|string',
            'dispatch_contact' => 'nullable|string',
            'dispatch_phone' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

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
            'is_active' => true,
        ]);

        PartyRole::create([
            'party_id' => $party->id,
            'role' => 'TRUCKER',
        ]);

        // 2. Create Trucker in dedicated table
        Trucker::create(array_merge($validated, [
            'party_id' => $party->id,
            'is_active' => true,
        ]));

        return redirect()->back()->with('success', 'تم تسجيل شركة النقل البري في جدول شركات النقل بنجاح.');
    }

    public function update(Request $request, Trucker $trucker): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:truckers,code,'.$trucker->id,
            'name' => 'required|string',
            'trade_name' => 'nullable|string',
            'fleet_size' => 'nullable|integer|min:1',
            'truck_types' => 'nullable|string',
            'coverage_areas' => 'nullable|string',
            'license_number' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'commercial_registry' => 'nullable|string',
            'dispatch_contact' => 'nullable|string',
            'dispatch_phone' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $trucker->update($validated);

        if ($trucker->party) {
            $trucker->party->update([
                'legal_name' => $validated['name'],
                'trade_name' => $validated['trade_name'] ?? null,
                'tax_number' => $validated['tax_number'] ?? null,
                'commercial_registry' => $validated['commercial_registry'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'تم تعديل بيانات شركة النقل البري بنجاح.');
    }

    public function destroy(Trucker $trucker): RedirectResponse
    {
        $trucker->delete();

        return redirect()->back()->with('success', 'تم حذف شركة النقل البري بنجاح.');
    }
}
