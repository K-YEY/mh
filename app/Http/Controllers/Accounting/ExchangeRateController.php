<?php

namespace App\Http\Controllers\Accounting;

use App\Actions\Accounting\UpdateExchangeRateAction;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExchangeRateController extends Controller
{
    public function index(): Response
    {
        $currencies = Currency::all();
        $exchangeRates = ExchangeRate::latest('effective_date')->paginate(15);

        return Inertia::render('Accounting/ExchangeRates/Index', [
            'currencies' => $currencies,
            'rates' => $exchangeRates,
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
}
