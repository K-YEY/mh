<?php

namespace App\Http\Middleware;

use App\Models\Currency;
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
            ] : [
                'code' => 'EGP',
                'name' => 'Egyptian Pound',
                'symbol' => 'E£',
                'decimal_places' => 2,
            ],
            'active_currencies' => fn () => Currency::where('is_active', true)->get(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
