<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Services\Financial\CurrencyExchangeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CurrencyController extends Controller
{
    public function index(): Response
    {
        $currencies = Currency::orderBy('is_base_currency', 'desc')->get();

        return Inertia::render('Settings/Currencies/Index', [
            'currencies' => $currencies,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string',
            'symbol' => 'required|string',
            'decimal_places' => 'required|integer|min:0|max:4',
            'is_base_currency' => 'boolean',
        ]);

        Currency::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'symbol' => $validated['symbol'],
            'decimal_places' => $validated['decimal_places'],
            'is_base_currency' => false,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Currency added to system configuration successfully.');
    }

    /**
     * Set a chosen currency as the system base currency.
     */
    public function setBase(string $code, CurrencyExchangeService $exchangeService): RedirectResponse
    {
        $targetCurrency = Currency::where('code', strtoupper($code))->firstOrFail();

        Currency::query()->update(['is_base_currency' => false]);
        $targetCurrency->update(['is_base_currency' => true]);

        // Automatically sync FX rates relative to newly set base currency
        $exchangeService->syncLiveRates();

        return redirect()->back()->with('success', "تم تعيين العملة الأساسية للنظام بنجاح إلى: {$targetCurrency->name} ({$targetCurrency->code}).");
    }

    public function update(Request $request, Currency $currency): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'decimal_places' => 'required|integer|min:0|max:4',
            'is_active' => 'nullable|boolean',
        ]);

        $currency->update([
            'name' => $validated['name'],
            'symbol' => $validated['symbol'],
            'decimal_places' => $validated['decimal_places'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->back()->with('success', 'تم تعديل بيانات العملة بنجاح.');
    }

    public function destroy(Currency $currency): RedirectResponse
    {
        if ($currency->is_base_currency) {
            return redirect()->back()->with('error', 'لا يمكن حذف العملة الأساسية للنظام.');
        }

        $currency->delete();

        return redirect()->back()->with('success', 'تم حذف العملة بنجاح.');
    }

    /**
     * Switch the active display currency for the user session.
     */
    public function switchCurrency(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'currency' => 'required|string|exists:currencies,code',
        ]);

        $code = strtoupper($validated['currency']);
        $request->session()->put('display_currency', $code);

        return redirect()->back()->with('success', "تم تبديل عملة النظام المعروضة إلى ({$code}).");
    }
}
