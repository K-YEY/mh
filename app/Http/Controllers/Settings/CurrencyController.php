<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Currency;
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
     * Set the given currency code as the system base currency.
     */
    public function setBase(string $code): RedirectResponse
    {
        $currency = Currency::findOrFail($code);

        \DB::transaction(function () use ($currency) {
            Currency::where('is_base_currency', true)->update(['is_base_currency' => false]);
            $currency->update(['is_base_currency' => true]);
        });

        return redirect()->back()->with('success', 'Base currency updated successfully.');
    }
}
