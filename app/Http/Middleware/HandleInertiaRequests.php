<?php

namespace App\Http\Middleware;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $baseCurrency = Currency::where('is_base_currency', true)->first()
            ?? Currency::where('code', 'EGP')->first()
            ?? Currency::first();

        $baseCode = $baseCurrency ? $baseCurrency->code : 'EGP';
        $activeCurrencies = Currency::where('is_active', true)->get();

        // Retrieve latest exchange rate per currency
        $latestRates = ExchangeRate::query()
            ->orderBy('effective_date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->unique('currency_code')
            ->pluck('rate_to_base', 'currency_code')
            ->toArray();

        $ratesMap = [];
        foreach ($activeCurrencies as $curr) {
            if ($curr->code === $baseCode) {
                $ratesMap[$curr->code] = 1.0;
            } else {
                $ratesMap[$curr->code] = isset($latestRates[$curr->code]) ? (float) $latestRates[$curr->code] : 1.0;
            }
        }
        $ratesMap[$baseCode] = 1.0;

        // Current display currency chosen by the user in session
        $selectedCode = $request->session()->get('display_currency');
        $currentCurrencyModel = null;
        if ($selectedCode) {
            $currentCurrencyModel = $activeCurrencies->firstWhere('code', $selectedCode);
        }
        if (! $currentCurrencyModel) {
            $currentCurrencyModel = $baseCurrency ?? $activeCurrencies->first();
        }

        $currentCurrencyData = $currentCurrencyModel ? [
            'code' => $currentCurrencyModel->code,
            'name' => $currentCurrencyModel->name,
            'symbol' => $currentCurrencyModel->symbol,
            'decimal_places' => (int) $currentCurrencyModel->decimal_places,
            'is_base_currency' => (bool) $currentCurrencyModel->is_base_currency,
            'rate_to_base' => (float) ($ratesMap[$currentCurrencyModel->code] ?? 1.0),
        ] : [
            'code' => 'EGP',
            'name' => 'Egyptian Pound',
            'symbol' => 'E£',
            'decimal_places' => 2,
            'is_base_currency' => true,
            'rate_to_base' => 1.0,
        ];

        $activeCurrenciesData = $activeCurrencies->map(function ($c) use ($ratesMap) {
            return [
                'code' => $c->code,
                'name' => $c->name,
                'symbol' => $c->symbol,
                'decimal_places' => (int) $c->decimal_places,
                'is_base_currency' => (bool) $c->is_base_currency,
                'rate_to_base' => (float) ($ratesMap[$c->code] ?? 1.0),
            ];
        })->values()->all();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'super-admin',
                    'roles' => [$user->role ?? 'super-admin'],
                    'permissions' => ['*'],
                ] : null,
            ],
            'base_currency' => $baseCurrency ? [
                'code' => $baseCurrency->code,
                'name' => $baseCurrency->name,
                'symbol' => $baseCurrency->symbol,
                'decimal_places' => $baseCurrency->decimal_places,
                'rate_to_base' => 1.0,
            ] : [
                'code' => 'EGP',
                'name' => 'Egyptian Pound',
                'symbol' => 'E£',
                'decimal_places' => 2,
                'rate_to_base' => 1.0,
            ],
            'current_currency' => $currentCurrencyData,
            'active_currencies' => $activeCurrenciesData,
            'rates_map' => $ratesMap,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
