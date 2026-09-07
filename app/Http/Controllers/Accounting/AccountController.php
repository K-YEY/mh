<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(): Response
    {
        $accounts = Account::with('parent')
            ->orderBy('code', 'asc')
            ->get();

        return Inertia::render('Accounting/Accounts/Index', [
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:accounts,code',
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'type' => 'required|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'parent_id' => 'nullable|exists:accounts,id',
            'is_active' => 'boolean',
        ]);

        Account::create($validated);

        return redirect()->back()->with('success', 'Account added to Chart of Accounts successfully.');
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:accounts,code,'.$account->id,
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'type' => 'required|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'parent_id' => 'nullable|exists:accounts,id',
            'is_active' => 'nullable|boolean',
        ]);

        $account->update([
            'code' => $validated['code'],
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'type' => $validated['type'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->back()->with('success', 'تم تعديل بيانات الحساب المالي بنجاح.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        if ($account->children()->count() > 0) {
            return redirect()->back()->with('error', 'لا يمكن حذف الحساب لأنه يحتوي على حسابات فرعية.');
        }

        $account->delete();

        return redirect()->back()->with('success', 'تم حذف الحساب بنجاح.');
    }
}
