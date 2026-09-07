<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\PartyRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartyController extends Controller
{
    public function index(): Response
    {
        $parties = Party::with('roles')
            ->orderBy('code', 'asc')
            ->get();

        return Inertia::render('Settings/Parties/Index', [
            'parties' => $parties,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:parties,code',
            'legal_name' => 'required|string',
            'tax_number' => 'nullable|string',
            'commercial_registry' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'role' => 'required|in:CUSTOMER,SHIPPING_LINE,TRUCKER,CUSTOMS_BROKER,OVERSEAS_AGENT,PACKHOUSE,EMPLOYEE',
        ]);

        $party = Party::create([
            'code' => $validated['code'],
            'legal_name' => $validated['legal_name'],
            'tax_number' => $validated['tax_number'] ?? null,
            'commercial_registry' => $validated['commercial_registry'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => true,
        ]);

        PartyRole::create([
            'party_id' => $party->id,
            'role' => $validated['role'],
        ]);

        return redirect()->back()->with('success', 'Unified Party created successfully.');
    }

    public function update(Request $request, Party $party): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:parties,code,'.$party->id,
            'legal_name' => 'required|string',
            'tax_number' => 'nullable|string',
            'commercial_registry' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'role' => 'nullable|in:CUSTOMER,SHIPPING_LINE,TRUCKER,CUSTOMS_BROKER,OVERSEAS_AGENT,PACKHOUSE,EMPLOYEE',
            'is_active' => 'nullable|boolean',
        ]);

        $party->update([
            'code' => $validated['code'],
            'legal_name' => $validated['legal_name'],
            'tax_number' => $validated['tax_number'] ?? null,
            'commercial_registry' => $validated['commercial_registry'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (! empty($validated['role'])) {
            $existingRole = PartyRole::where('party_id', $party->id)->first();
            if ($existingRole) {
                $existingRole->update(['role' => $validated['role']]);
            } else {
                PartyRole::create([
                    'party_id' => $party->id,
                    'role' => $validated['role'],
                ]);
            }
        }

        return redirect()->back()->with('success', 'تم تحديث بيانات الجهة بنجاح.');
    }

    public function destroy(Party $party): RedirectResponse
    {
        $party->roles()->delete();
        $party->delete();

        return redirect()->back()->with('success', 'تم حذف الجهة بنجاح.');
    }
}
