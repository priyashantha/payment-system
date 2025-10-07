<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * Fetch exchange rates (base USD)
     * - Uses cache to avoid repeated API calls
     * - Supports both "rates" and "quotes" API formats
     */
    public static function getRates(): array
    {
        // Cache for 12 hours to avoid over-fetching API
        return Cache::remember('exchange_rates_usd', now()->addHours(12), function () {
            try {
                // Try primary API (keyless, free)
                $response = Http::timeout(10)->get('https://open.er-api.com/v6/latest/USD');

                if ($response->successful()) {
                    return $response->json('rates', []);
                }

                // Fallback to exchangerate.host (requires key)
                $fallback = Http::timeout(10)->get('https://api.exchangerate.host/live', [
                    'access_key' => env('EXCHANGE_RATE_KEY'),
                    'source' => 'USD',
                ]);

                $data = $fallback->json();

                if (isset($data['quotes'])) {
                    $normalized = [];
                    foreach ($data['quotes'] as $pair => $value) {
                        $currency = str_replace('USD', '', $pair);
                        $normalized[$currency] = $value;
                    }
                    return $normalized;
                }

                return $data['rates'] ?? [];
            } catch (\Throwable $e) {
                Log::error('ExchangeRateService: failed to fetch rates', [
                    'error' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }
}
