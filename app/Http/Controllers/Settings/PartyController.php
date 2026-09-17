<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Account;
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
        $parties = Party::with(['roles', 'account'])
            ->orderBy('code', 'asc')
            ->get();

        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar', 'name_en', 'type', 'parent_id']);

        return Inertia::render('Settings/Parties/Index', [
            'parties' => $parties,
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:parties,code',
            'legal_name' => 'required|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'commercial_registry' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'role' => 'required|in:CUSTOMER,SHIPPING_LINE,TRUCKER,CUSTOMS_BROKER,OVERSEAS_AGENT,PACKHOUSE,EMPLOYEE',
            'account_id' => 'nullable|exists:accounts,id',
            'auto_create_account' => 'nullable|boolean',
        ]);

        $accountId = $validated['account_id'] ?? null;

        // Auto-generate dedicated sub-account in Chart of Accounts if requested and not manually selected
        if (! $accountId && $request->boolean('auto_create_account')) {
            $accountId = $this->createDedicatedSubAccount($validated['legal_name'], $validated['role']);
        }

        $party = Party::create([
            'code' => $validated['code'],
            'legal_name' => $validated['legal_name'],
            'tax_number' => $validated['tax_number'] ?? null,
            'commercial_registry' => $validated['commercial_registry'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'account_id' => $accountId,
            'is_active' => true,
        ]);

        PartyRole::create([
            'party_id' => $party->id,
            'role' => $validated['role'],
        ]);

        return redirect()->back()->with('success', 'تم تسجيل الجهة وربط حسابها المالي بنجاح.');
    }

    public function update(Request $request, Party $party): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:parties,code,'.$party->id,
            'legal_name' => 'required|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'commercial_registry' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'role' => 'nullable|in:CUSTOMER,SHIPPING_LINE,TRUCKER,CUSTOMS_BROKER,OVERSEAS_AGENT,PACKHOUSE,EMPLOYEE',
            'account_id' => 'nullable|exists:accounts,id',
            'auto_create_account' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $accountId = $validated['account_id'] ?? $party->account_id;

        // If requested to auto-generate a dedicated sub-account now
        if ($request->boolean('auto_create_account') && ! $accountId) {
            $role = $validated['role'] ?? $party->roles->first()?->role ?? 'CUSTOMER';
            $accountId = $this->createDedicatedSubAccount($validated['legal_name'], $role);
        }

        $party->update([
            'code' => $validated['code'],
            'legal_name' => $validated['legal_name'],
            'tax_number' => $validated['tax_number'] ?? null,
            'commercial_registry' => $validated['commercial_registry'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'account_id' => $accountId,
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

        return redirect()->back()->with('success', 'تم تحديث بيانات الجهة وحسابها المالي بنجاح.');
    }

    public function destroy(Party $party): RedirectResponse
    {
        $party->roles()->delete();
        $party->delete();

        return redirect()->back()->with('success', 'تم حذف الجهة بنجاح.');
    }

    /**
     * Create a dedicated subsidiary ledger account under the appropriate parent control account
     */
    protected function createDedicatedSubAccount(string $partyName, string $role): int
    {
        $parentCodeMap = [
            'CUSTOMER' => '11210',       // عملاء التصدير والشحن الخارجي (Assets)
            'SHIPPING_LINE' => '21100',  // حسابات الموردين وخطوط الشحن (Liabilities)
            'CUSTOMS_BROKER' => '21300', // مستحقات مكاتب التخليص الجمركي (Liabilities)
            'TRUCKER' => '21200',        // مستحقات شركات النقل البري والمبرد (Liabilities)
            'OVERSEAS_AGENT' => '21100', // مستحقات الوكلاء والموردين (Liabilities)
            'PACKHOUSE' => '21100',      // مستحقات محطات التعبئة والموردين (Liabilities)
            'EMPLOYEE' => '21100',
        ];

        $parentCode = $parentCodeMap[$role] ?? '21100';
        $parentAccount = Account::where('code', $parentCode)->first()
            ?? Account::where('code', '21000')->first()
            ?? Account::first();

        // Generate next sub-account code e.g. 11210-001, 21100-001
        $existingCount = Account::where('code', 'like', "{$parentCode}-%")->count();
        $nextNumber = str_pad((string) ($existingCount + 1), 3, '0', STR_PAD_LEFT);
        $newCode = "{$parentCode}-{$nextNumber}";

        // Ensure uniqueness
        while (Account::where('code', $newCode)->exists()) {
            $existingCount++;
            $nextNumber = str_pad((string) ($existingCount + 1), 3, '0', STR_PAD_LEFT);
            $newCode = "{$parentCode}-{$nextNumber}";
        }

        $account = Account::create([
            'code' => $newCode,
            'name_ar' => "حساب: {$partyName}",
            'name_en' => "Account: {$partyName}",
            'type' => $parentAccount ? $parentAccount->type : 'LIABILITY',
            'parent_id' => $parentAccount?->id,
            'is_active' => true,
        ]);

        return $account->id;
    }
}
