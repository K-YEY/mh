<?php

namespace App\Http\Controllers\Accounting;

use App\Actions\Accounting\ProcessPaymentVoucherAction;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Party;
use App\Models\PaymentVoucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentVoucherController extends Controller
{
    public function index(): Response
    {
        $vouchers = PaymentVoucher::with(['party', 'account', 'journalEntry'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Accounting/Vouchers/Index', [
            'vouchers' => $vouchers,
        ]);
    }

    public function create(): Response
    {
        $parties = Party::where('is_active', true)->orderBy('name_en')->get();
        $treasuryAccounts = Account::where('code', 'LIKE', '111%')
            ->where('is_active', true)
            ->whereNotNull('parent_id')
            ->orderBy('code')
            ->get();

        if ($treasuryAccounts->isEmpty()) {
            $treasuryAccounts = Account::where('type', 'ASSET')->where('is_active', true)->get();
        }

        return Inertia::render('Accounting/Vouchers/Create', [
            'parties' => $parties,
            'accounts' => $treasuryAccounts,
        ]);
    }

    public function show(PaymentVoucher $voucher): Response
    {
        $voucher->load(['party', 'account', 'journalEntry.lines.account']);

        return Inertia::render('Accounting/Vouchers/Show', [
            'voucher' => $voucher,
        ]);
    }

    public function store(Request $request, ProcessPaymentVoucherAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'voucher_type' => 'required|in:RECEIPT,PAYMENT',
            'party_id' => 'required|exists:parties,id',
            'payment_method' => 'required|in:CASH,BANK_TRANSFER,CHEQUE',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'currency_code' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string',
        ]);

        $voucher = $action->execute($validated);

        return redirect()->route('vouchers.show', $voucher->id)
            ->with('success', 'تم حفظ وترحيل السند المحاسبي بنجاح وتوليد القيد الآلي.');
    }
}
