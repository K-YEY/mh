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
}
