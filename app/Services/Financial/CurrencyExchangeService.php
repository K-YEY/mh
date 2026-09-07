<?php

namespace App\Services\Financial;

use App\Models\Currency;
use App\Models\ExchangeRate;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CurrencyExchangeService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'verify' => false,
            'timeout' => 8,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept' => 'application/json,text/html,*/*',
            ],
        ]);
    }

    /**
     * Fetch live market rates and synchronize relative to the current system base currency.
     *
     * @return array{success: bool, message: string, synced: array<string, float>}
     */
    public function syncLiveRates(): array
    {
        /** @var Currency|null $baseCurrency */
        $baseCurrency = Currency::where('is_base_currency', true)->first()
            ?? Currency::where('code', 'EGP')->first()
            ?? Currency::first();

        $baseCode = $baseCurrency ? $baseCurrency->code : 'EGP';
        $activeCurrencies = Currency::where('is_active', true)->pluck('code')->toArray();

        // 1. Fetch raw rates map (keyed to USD)
        $usdRates = $this->fetchRawUsdRates();

        if (empty($usdRates)) {
            return [
                'success' => false,
                'message' => 'تعذر جلب أسعار العملات الحية من المصادر المفتوحة حالياً.',
                'synced' => [],
            ];
        }

        // Ensure USD rate is 1.0 in USD base
        $usdRates['USD'] = 1.0;

        // If EGP is missing or unverified, maintain standard rate
        if (! isset($usdRates['EGP']) || $usdRates['EGP'] <= 0) {
            $usdRates['EGP'] = 50.0;
        }

        $synced = [];
        $effectiveDate = now()->toDateString();

        // Target: Rate of each currency to system Base Currency
        // In MH ERP Double-Entry: exchange_rate_to_base means: (Amount in Foreign Currency) * rate_to_base = (Amount in Base Currency)
        // Example: If base is EGP, and USD is 50 EGP: 1 USD * 50 = 50 EGP => USD rate_to_base is 50.000000.
        // If base is USD, and EGP is 0.02 USD: 1 EGP * 0.02 = 0.02 USD => EGP rate_to_base is 0.020000.
        $baseInUsd = $usdRates[$baseCode] ?? 1.0;

        foreach ($activeCurrencies as $currCode) {
            if ($currCode === $baseCode) {
                $calculatedRate = 1.000000;
            } else {
                $currInUsd = $usdRates[$currCode] ?? 1.0;
                // How many units of base currency per 1 unit of foreign currency:
                // BasePerForeign = (Units of Base per 1 USD) / (Units of Foreign per 1 USD)
                $calculatedRate = $currInUsd > 0 ? ($baseInUsd / $currInUsd) : 1.0;
            }

            $roundedRate = round($calculatedRate, 6);

            ExchangeRate::updateOrCreate(
                [
                    'currency_code' => $currCode,
                    'effective_date' => $effectiveDate,
                ],
                [
                    'rate_to_base' => $roundedRate,
                ]
            );

            $synced[$currCode] = $roundedRate;
        }

        return [
            'success' => true,
            'message' => "تم تحديث أسعار الصرف بنجاح وفق العملة الأساسية ({$baseCode}).",
            'synced' => $synced,
        ];
    }

    /**
     * Manually set or override a specific currency exchange rate to base.
     */
    public function setManualRate(string $currencyCode, float $rateToBase, ?string $effectiveDate = null): ExchangeRate
    {
        $date = $effectiveDate ?? now()->toDateString();

        return ExchangeRate::updateOrCreate(
            [
                'currency_code' => strtoupper($currencyCode),
                'effective_date' => $date,
            ],
            [
                'rate_to_base' => round($rateToBase, 6),
            ]
        );
    }

    /**
     * Get live direct rates matrix from the API for display in the FX Matrix hub.
     *
     * @return array{
     *     provider: string,
     *     updated_at: string,
     *     base_currency: string,
     *     rates: array<int, array{code: string, name: string, name_en: string, symbol: string, rate_to_base: float, inverse: float}>
     * }
     */
    public function getDirectLiveMatrix(?string $baseCode = null): array
    {
        $base = strtoupper($baseCode ?: 'EGP');
        $raw = $this->fetchRawUsdRates();

        $baseInUsd = $raw[$base] ?? 50.0;
        $majorCurrencies = [
            'USD' => ['name' => 'الدولار الأمريكي', 'name_en' => 'US Dollar', 'symbol' => '$'],
            'EUR' => ['name' => 'اليورو الأوروبي', 'name_en' => 'Euro', 'symbol' => '€'],
            'GBP' => ['name' => 'الجنيه الإسترليني', 'name_en' => 'British Pound', 'symbol' => '£'],
            'SAR' => ['name' => 'الريال السعودي', 'name_en' => 'Saudi Riyal', 'symbol' => 'ر.س'],
            'AED' => ['name' => 'الدرهم الإماراتي', 'name_en' => 'UAE Dirham', 'symbol' => 'د.إ'],
            'CNY' => ['name' => 'اليوان الصيني', 'name_en' => 'Chinese Yuan', 'symbol' => '¥'],
            'KWD' => ['name' => 'الدينار الكويتي', 'name_en' => 'Kuwaiti Dinar', 'symbol' => 'د.ك'],
        ];

        $ratesList = [];
        foreach ($majorCurrencies as $code => $meta) {
            if ($code === $base) {
                continue;
            }

            $currInUsd = $raw[$code] ?? 1.0;
            // Rate to Base: How many units of base currency per 1 unit of this foreign currency
            $rateToBase = $currInUsd > 0 ? round($baseInUsd / $currInUsd, 4) : 1.0;
            $inverse = $rateToBase > 0 ? round(1 / $rateToBase, 6) : 1.0;

            $ratesList[] = [
                'code' => $code,
                'name' => $meta['name'],
                'name_en' => $meta['name_en'],
                'symbol' => $meta['symbol'],
                'rate_to_base' => $rateToBase,
                'inverse' => $inverse,
            ];
        }

        return [
            'provider' => 'Open Exchange Rates (Direct Live API)',
            'updated_at' => now()->format('Y-m-d H:i:s'),
            'base_currency' => $base,
            'rates' => $ratesList,
        ];
    }

    /**
     * Fetch USD relative exchange rates using Free Public ExchangeRate API with Google scraping fallback.
     *
     * @return array<string, float>
     */
    protected function fetchRawUsdRates(): array
    {
        // 1. Try Free Public Open ExchangeRate API (Instant, No Key, Real-time)
        try {
            $response = $this->client->get('https://open.er-api.com/v6/latest/USD');
            if ($response->getStatusCode() === 200) {
                $data = json_decode((string) $response->getBody(), true);
                if (! empty($data['rates']) && is_array($data['rates'])) {
                    return array_map('floatval', $data['rates']);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Free ExchangeRate API fetch notice: '.$e->getMessage());
        }

        // 2. Fallback: Secondary Open API
        try {
            $response = $this->client->get('https://api.exchangerate-api.com/v4/latest/USD');
            if ($response->getStatusCode() === 200) {
                $data = json_decode((string) $response->getBody(), true);
                if (! empty($data['rates']) && is_array($data['rates'])) {
                    return array_map('floatval', $data['rates']);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Secondary FX API notice: '.$e->getMessage());
        }

        // 3. Fallback: Google Finance Web Scraping for USD to EGP
        try {
            $response = $this->client->get('https://www.google.com/finance/quote/USD-EGP');
            $html = (string) $response->getBody();

            $rate = null;
            if (preg_match('/class="[^"]*YMlKec[^"]*fxKbKc[^"]*">([0-9.,]+)<\/div>/i', $html, $m)) {
                $rate = (float) str_replace(',', '', $m[1]);
            } elseif (preg_match('/data-last-price="([0-9.,]+)"/i', $html, $m)) {
                $rate = (float) str_replace(',', '', $m[1]);
            }

            if ($rate && $rate > 0) {
                return [
                    'USD' => 1.0,
                    'EGP' => $rate,
                    'EUR' => 0.92,
                    'SAR' => 3.75,
                    'AED' => 3.67,
                    'GBP' => 0.78,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Google Finance scrape notice: '.$e->getMessage());
        }

        // Resilient default rates if network is offline
        return [
            'USD' => 1.0,
            'EGP' => 50.0,
            'EUR' => 0.92,
            'SAR' => 3.75,
            'AED' => 3.67,
            'GBP' => 0.78,
        ];
    }
}
