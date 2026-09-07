<?php

namespace App\Http\Controllers\Accounting;

use App\Actions\Accounting\UpdateExchangeRateAction;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Services\Financial\CurrencyExchangeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExchangeRateController extends Controller
{
    public function index(CurrencyExchangeService $exchangeService): Response
    {
        $currencies = Currency::all();
        $exchangeRates = ExchangeRate::latest('effective_date')->paginate(15);
        $baseCurrency = Currency::where('is_base_currency', true)->first()
            ?? Currency::where('code', 'EGP')->first();

        $liveApi = $exchangeService->getDirectLiveMatrix($baseCurrency->code ?? 'EGP');

        return Inertia::render('Accounting/ExchangeRates/Index', [
            'currencies' => $currencies,
            'rates' => $exchangeRates,
            'base_currency' => $baseCurrency,
            'live_api' => $liveApi,
        ]);
    }

    public function store(Request $request, UpdateExchangeRateAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'currency_code' => 'required|exists:currencies,code',
            'rate_to_base' => 'required|numeric|min:0.000001',
            'effective_date' => 'required|date',
        ]);

        $action->execute($validated);

        return redirect()->back()->with('success', 'Exchange rate updated successfully.');
    }

    /**
     * Trigger live foreign exchange rates fetch from Google / Free Open API.
     */
    public function syncLive(CurrencyExchangeService $service): RedirectResponse
    {
        $result = $service->syncLiveRates();

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function update(Request $request, ExchangeRate $exchangeRate): RedirectResponse
    {
        $validated = $request->validate([
            'rate_to_base' => 'required|numeric|min:0.000001',
            'effective_date' => 'required|date',
        ]);

        $exchangeRate->update($validated);

        return redirect()->back()->with('success', 'تم تعديل سعر الصرف بنجاح.');
    }

    public function destroy(ExchangeRate $exchangeRate): RedirectResponse
    {
        $exchangeRate->delete();

        return redirect()->back()->with('success', 'Exchange rate deleted successfully.');
    }
}
