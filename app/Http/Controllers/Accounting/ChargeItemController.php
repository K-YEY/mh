<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ChargeItem;
use App\Models\ShipmentInvoiceItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChargeItemController extends Controller
{
    public function index(Request $request): Response
    {
        $items = ChargeItem::with(['revenueAccount', 'expenseAccount'])
            ->orderBy('code')
            ->get();

        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar', 'name_en', 'type', 'parent_id']);

        $revenueAccounts = $accounts->where('type', 'REVENUE')->values();
        $expenseAccounts = $accounts->where('type', 'EXPENSE')->values();

        return Inertia::render('Accounting/ChargeItems/Index', [
            'charge_items' => $items,
            'revenue_accounts' => $revenueAccounts,
            'expense_accounts' => $expenseAccounts,
            'all_accounts' => $accounts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:charge_items,code',
            'name_ar' => 'required|string|max:150',
            'name_en' => 'required|string|max:150',
            'revenue_account_id' => 'required|exists:accounts,id',
            'expense_account_id' => 'required|exists:accounts,id',
        ]);

        ChargeItem::create($validated);

        return redirect()->back()->with('success', 'تمت إضافة خطوة / عنصر التكلفة والإيراد التشغيلي بنجاح.');
    }

    public function update(Request $request, ChargeItem $chargeItem): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:charge_items,code,'.$chargeItem->id,
            'name_ar' => 'required|string|max:150',
            'name_en' => 'required|string|max:150',
            'revenue_account_id' => 'required|exists:accounts,id',
            'expense_account_id' => 'required|exists:accounts,id',
        ]);

        $chargeItem->update($validated);

        return redirect()->back()->with('success', 'تم تحديث حسابات وبيانات الخطوة التشغيلية بنجاح.');
    }

    public function destroy(ChargeItem $chargeItem): RedirectResponse
    {
        $isUsed = ShipmentInvoiceItem::where('charge_item_id', $chargeItem->id)->exists();

        if ($isUsed) {
            return redirect()->back()->with('error', 'لا يمكن حذف هذه الخطوة التشغيلية لوجود فواتير ومطالبات مرتبطة بها.');
        }

        $chargeItem->delete();

        return redirect()->back()->with('success', 'تم حذف الخطوة التشغيلية بنجاح.');
    }
}
