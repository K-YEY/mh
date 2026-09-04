<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\Tracking\Drivers\FindTeuDriver;
use App\Services\Tracking\Drivers\SeaRatesDriver;
use App\Services\Tracking\Drivers\ShipsGoDriver;
use App\Services\Tracking\Drivers\VizionDriver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IntegrationSettingController extends Controller
{
    /**
     * Display masked integration settings.
     */
    public function index(): Response
    {
        $providers = ['integration_shipsgo', 'integration_searates', 'integration_vizion', 'integration_findteu'];
        $settings = [];

        foreach ($providers as $group) {
            $rawGroup = SystemSetting::getGroup($group);
            $maskedGroup = [];

            foreach ($rawGroup as $key => $value) {
                if (in_array($key, ['api_key', 'api_token', 'webhook_secret', 'customer_id']) && ! empty($value)) {
                    $maskedGroup[$key] = '••••••••'.(strlen($value) > 4 ? substr($value, -4) : '');
                } else {
                    $maskedGroup[$key] = $value;
                }
            }

            $settings[$group] = $maskedGroup;
        }

        $baseUrl = config('app.url', 'http://localhost');

        return Inertia::render('Admin/Settings/Integrations', [
            'settings' => $settings,
            'webhookUrls' => [
                'shipsgo' => rtrim($baseUrl, '/').'/api/v1/webhooks/tracking/shipsgo',
                'searates' => rtrim($baseUrl, '/').'/api/v1/webhooks/tracking/searates',
                'vizion' => rtrim($baseUrl, '/').'/api/v1/webhooks/tracking/vizion',
                'findteu' => rtrim($baseUrl, '/').'/api/v1/webhooks/tracking/findteu',
            ],
        ]);
    }

    /**
     * Update and encrypt provider settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipsgo' => 'nullable|array',
            'searates' => 'nullable|array',
            'vizion' => 'nullable|array',
            'findteu' => 'nullable|array',
        ]);

        $groupMapping = [
            'shipsgo' => 'integration_shipsgo',
            'searates' => 'integration_searates',
            'vizion' => 'integration_vizion',
            'findteu' => 'integration_findteu',
        ];

        foreach ($groupMapping as $formKey => $groupName) {
            if (! isset($validated[$formKey]) || ! is_array($validated[$formKey])) {
                continue;
            }

            foreach ($validated[$formKey] as $key => $val) {
                // If masked bullet value passed, keep existing decrypted value in DB
                if (is_string($val) && str_contains($val, '••••••••')) {
                    continue;
                }

                $isSecret = in_array($key, ['api_key', 'api_token', 'webhook_secret', 'customer_id']);
                SystemSetting::set($groupName, $key, (string) $val, $isSecret);
            }
        }

        return back()->with('success', 'Integration credentials updated successfully.');
    }

    /**
     * Test live API connection for chosen provider.
     */
    public function testConnection(Request $request, string $provider): JsonResponse
    {
        $testContainer = $request->input('container_number') ?? 'MSKU9012345';
        $testCarrier = $request->input('shipping_line_code') ?? 'MAERSK';

        try {
            $driver = match (strtolower($provider)) {
                'shipsgo' => new ShipsGoDriver,
                'searates' => new SeaRatesDriver,
                'vizion' => new VizionDriver,
                'findteu' => new FindTeuDriver,
                default => null,
            };

            if (! $driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unknown or unsupported tracking provider.',
                ], 400);
            }

            $dto = $driver->fetchTracking($testContainer, $testCarrier);

            if ($dto->current_status === 'MANUAL') {
                return response()->json([
                    'success' => false,
                    'message' => "{$provider} integration is either disabled or credentials are invalid.",
                    'details' => $dto->toArray(),
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => "Connection to {$provider} API verified successfully!",
                'details' => $dto->toArray(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
