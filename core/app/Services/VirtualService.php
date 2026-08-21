<?php

namespace App\Services;

use App\Models\NumberApi;
use App\Models\NumberCountry;
use App\Models\NumberSetting;
use App\Models\NumberService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class VirtualService
{
    /**
     * SSL verify option: use CA bundle path if file exists, else disable verify (avoids cURL 77 when cacert.pem is missing).
     */
    private function verifyOption(): bool|string
    {
        $path = config('http.ca_bundle');

        return ($path && is_file($path)) ? $path : false;
    }

    private function client(int $timeout = 30)
    {
        return Http::withOptions(['verify' => $this->verifyOption()])->timeout($timeout);
    }

    private function handlerUrl(NumberApi $api): string
    {
        return rtrim($api->base_url, '/') . '/stubs/handler_api.php';
    }

    public function importCountries(NumberApi $api): array
    {
        try {
            $response = $this->client(30)->get($this->handlerUrl($api), [
                'api_key' => $api->api_key,
                'action' => 'getCountries',
            ]);

            $body = trim($response->body());

            if (!$response->successful() || str_starts_with($body, 'BAD_')) {
                Log::warning('Virtual API countries import failed', [
                    'api_id' => $api->id,
                    'status' => $response->status(),
                    'body' => $body,
                ]);

                return ['success' => false, 'message' => $body ?: 'API request failed.'];
            }

            $data = json_decode($body, true);

            if (!is_array($data) || $data === []) {
                Log::warning('Virtual API countries import invalid response', [
                    'api_id' => $api->id,
                    'body' => $body,
                ]);

                return ['success' => false, 'message' => 'Invalid or empty countries response.'];
            }

            $countries = array_is_list($data) ? $data : array_values($data);

            $imported = 0;
            $skipped = 0;

            foreach ($countries as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $providerId = (string) ($item['id'] ?? '');
                $name = $item['eng'] ?? $item['rus'] ?? '';

                if ($providerId === '' || $name === '') {
                    continue;
                }

                $code = $providerId;

                $exists = NumberCountry::where('api_id', $api->id)
                    ->where('code', $code)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                NumberCountry::create([
                    'name' => $name,
                    'code' => $code,
                    'country_id' => $providerId,
                    'api_id' => $api->id,
                    'status' => 1,
                ]);

                $imported++;
            }

            return [
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
            ];
        } catch (ConnectionException $e) {
            Log::error('Virtual API countries import connection failed', [
                'api_id' => $api->id,
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Could not connect to API provider.'];
        } catch (Throwable $e) {
            Log::error('Virtual API countries import exception', [
                'api_id' => $api->id,
                'message' => $e->getMessage(),
                'exception' => $e::class,
            ]);

            return ['success' => false, 'message' => 'Country import failed. Please check the logs.'];
        }
    }

    public function fetchBalance(NumberApi $api): array
    {
        try {
            $response = $this->client(15)->get($this->handlerUrl($api), [
                'api_key' => $api->api_key,
                'action' => 'getBalance',
            ]);

            $body = trim($response->body());

            if (!$response->successful() || str_starts_with($body, 'BAD_')) {
                Log::warning('Virtual API balance fetch failed', [
                    'api_id' => $api->id,
                    'status' => $response->status(),
                    'body' => $body,
                ]);

                return ['success' => false, 'message' => $body ?: 'API request failed.', 'balance' => null];
            }

            $parts = explode(':', $body, 2);

            if (($parts[0] ?? '') !== 'ACCESS_BALANCE' || !isset($parts[1])) {
                Log::warning('Virtual API balance invalid response', [
                    'api_id' => $api->id,
                    'body' => $body,
                ]);

                return ['success' => false, 'message' => 'Invalid balance response.', 'balance' => null];
            }

            return [
                'success' => true,
                'balance' => trim($parts[1]),
                'message' => null,
            ];
        } catch (ConnectionException $e) {
            Log::error('Virtual API balance connection failed', [
                'api_id' => $api->id,
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Could not connect to API provider.', 'balance' => null];
        } catch (Throwable $e) {
            Log::error('Virtual API balance exception', [
                'api_id' => $api->id,
                'message' => $e->getMessage(),
                'exception' => $e::class,
            ]);

            return ['success' => false, 'message' => 'Balance fetch failed. Please check the logs.', 'balance' => null];
        }
    }

    public function importServices(NumberApi $api, ?int $countryId = null): array
    {
        try {
            $params = [
                'api_key' => $api->api_key,
                'action' => 'getPrices',
            ];

            if ($countryId !== null) {
                $country = NumberCountry::where('api_id', $api->id)->find($countryId);
                if (!$country) {
                    return ['success' => false, 'message' => 'Country not found for this API.'];
                }
                $params['country'] = $country->country_id ?: $country->code;
            }

            $response = $this->client(60)->get($this->handlerUrl($api), $params);
            $body = trim($response->body());

            if (!$response->successful() || str_starts_with($body, 'BAD_')) {
                Log::warning('Virtual API services import failed', [
                    'api_id' => $api->id,
                    'status' => $response->status(),
                    'body' => $body,
                ]);
                return ['success' => false, 'message' => $body ?: 'API request failed.'];
            }

            $data = json_decode($body, true);

            if (!is_array($data) || $data === []) {
                Log::warning('Virtual API services import invalid response', [
                    'api_id' => $api->id,
                    'body' => $body,
                ]);
                return ['success' => false, 'message' => 'Invalid or empty prices response.'];
            }

            $serviceNames = $this->fetchServiceNameMap($api);

            $settings = NumberSetting::current();
            $rate = (float) $settings->currency_rate;
            $increasePct = (float) $settings->price_increase;

            $imported = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($data as $providerCountryKey => $services) {
                if (!is_array($services)) {
                    continue;
                }

                $country = NumberCountry::where('api_id', $api->id)
                    ->where(function ($q) use ($providerCountryKey) {
                        $key = (string) $providerCountryKey;
                        $q->where('code', $key)->orWhere('country_id', $key);
                    })
                    ->first();

                if (!$country) {
                    $skipped += count(array_filter($services, 'is_array'));
                    continue;
                }

                if ($countryId !== null && (int) $country->id !== (int) $countryId) {
                    continue;
                }

                foreach ($services as $serviceCode => $info) {
                    if (!is_array($info) || !isset($info['cost'])) {
                        continue;
                    }

                    $code = (string) $serviceCode;

                    $providerPrice = (float) $info['cost'];
                    $retailPrice = round($providerPrice * $rate * (1 + $increasePct / 100), 2);

                    $existing = NumberService::where('api_id', $api->id)
                        ->where('code', $code)
                        ->where('country_id', $country->id)
                        ->first();

                    if ($existing) {
                        $existing->update([
                            'provider_price' => $providerPrice,
                            'price' => $retailPrice,
                        ]);
                        $updated++;
                        continue;
                    }

                    NumberService::create([
                        'name' => $serviceNames[$code] ?? strtoupper($code),
                        'code' => $code,
                        'country_id' => $country->id,
                        'api_id' => $api->id,
                        'provider_price' => $providerPrice,
                        'price' => $retailPrice,
                        'status' => 1,
                    ]);

                    $imported++;
                }
            }

            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
            ];
        } catch (ConnectionException $e) {
            Log::error('Virtual API services import connection failed', [
                'api_id' => $api->id,
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Could not connect to API provider.'];
        } catch (Throwable $e) {
            Log::error('Virtual API services import exception', [
                'api_id' => $api->id,
                'message' => $e->getMessage(),
                'exception' => $e::class,
            ]);

            return ['success' => false, 'message' => 'Service import failed. Please check the logs.'];
        }
    }

    private function fetchServiceNameMap(NumberApi $api): array
    {
        try {
            $response = $this->client(30)->get($this->handlerUrl($api), [
                'api_key' => $api->api_key,
                'action' => 'getServicesList',
            ]);

            $body = trim($response->body());
            if (!$response->successful() || str_starts_with($body, 'BAD_')) {
                return [];
            }

            $data = json_decode($body, true);
            $list = $data['services'] ?? $data;

            if (!is_array($list)) {
                return [];
            }

            $map = [];

            foreach ($list as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $code = (string) ($item['code'] ?? '');
                $name = (string) ($item['name'] ?? '');
                if ($code !== '' && $name !== '') {
                    $map[$code] = $name;
                }
            }

            return $map;
        } catch (Throwable) {
            return [];
        }
    }

    public function getNumber(NumberApi $api, string $serviceCode, string $providerCountry): array
    {
        try {
            $response = $this->client(30)->get($this->handlerUrl($api), [
                'api_key' => $api->api_key,
                'action' => 'getNumber',
                'service' => $serviceCode,
                'country' => $providerCountry,
            ]);

            $body = trim($response->body());

            if (!$response->successful() || str_starts_with($body, 'BAD_') || str_starts_with($body, 'NO_')) {
                return ['success' => false, 'message' => $body ?: 'Provider error'];
            }

            $parts = explode(':', $body, 3);

            if (($parts[0] ?? '') !== 'ACCESS_NUMBER') {
                return ['success' => false, 'message' => $body];
            }

            return [
                'success' => true,
                'activation_id' => $parts[1] ?? '',
                'phone' => $parts[2] ?? '',
            ];
        } catch (Throwable $e) {
            Log::error('Virtual API getNumber failed', ['api_id' => $api->id, 'message' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Could not connect to provider'];
        }
    }

    public function getStatus(NumberApi $api, string $activationId): array
    {
        try {
            $response = $this->client(15)->get($this->handlerUrl($api), [
                'api_key' => $api->api_key,
                'action' => 'getStatus',
                'id' => $activationId,
            ]);

            $body = trim($response->body());

            return ['success' => true, 'body' => $body];
        } catch (Throwable $e) {
            return ['success' => false, 'body' => ''];
        }
    }

    public function setStatus(NumberApi $api, string $activationId, int $status): array
    {
        try {
            $response = $this->client(15)->get($this->handlerUrl($api), [
                'api_key' => $api->api_key,
                'action' => 'setStatus',
                'id' => $activationId,
                'status' => $status,
            ]);

            return ['success' => true, 'body' => trim($response->body())];
        } catch (Throwable $e) {
            return ['success' => false, 'body' => ''];
        }
    }
}
