<?php

namespace App\Services;

use App\Models\RentingApi;
use App\Models\RentingCountry;
use App\Models\RentingOperator;
use App\Models\RentingService;
use App\Models\RentingServiceOperator;
use App\Models\RentingSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RentalService
{
    private function verifyOption(): bool|string
    {
        $path = config('http.ca_bundle');

        return ($path && is_file($path)) ? $path : false;
    }

    private function client(int $timeout = 30)
    {
        return Http::withOptions(['verify' => $this->verifyOption()])->timeout($timeout);
    }

    private function rentUrl(RentingApi $api): string
    {
        return rtrim($api->base_url, '/') . '/api/rent.php';
    }

    private function handlerUrl(RentingApi $api): string
    {
        return rtrim($api->base_url, '/') . '/stubs/handler_api.php';
    }

    private function request(RentingApi $api, string $method, array $params = [], int $timeout = 30): array
    {
        try {
            $params['method'] = $method;
            $params['apikey'] = $api->api_key;

            $response = $this->client($timeout)->get($this->rentUrl($api), $params);
            $body = trim($response->body());

            if (!$response->successful()) {
                Log::warning('Rental API request failed', [
                    'api_id' => $api->id,
                    'method' => $method,
                    'status' => $response->status(),
                    'body' => $body,
                ]);

                return ['success' => false, 'message' => $body ?: 'API request failed.', 'data' => null];
            }

            $json = json_decode($body, true);

            if (!is_array($json)) {
                Log::warning('Rental API invalid JSON', [
                    'api_id' => $api->id,
                    'method' => $method,
                    'body' => $body,
                ]);

                return ['success' => false, 'message' => 'Invalid API response.', 'data' => null];
            }

            if ((int) ($json['status'] ?? 0) !== 1) {
                $message = is_string($json['message'] ?? null)
                    ? $json['message']
                    : (is_string($json['error'] ?? null) ? $json['error'] : 'Provider error');

                return ['success' => false, 'message' => $message, 'data' => $json['data'] ?? null];
            }

            return ['success' => true, 'message' => null, 'data' => $json['data'] ?? null];
        } catch (ConnectionException $e) {
            Log::error('Rental API connection failed', [
                'api_id' => $api->id,
                'method' => $method,
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Could not connect to API provider.', 'data' => null];
        } catch (Throwable $e) {
            Log::error('Rental API exception', [
                'api_id' => $api->id,
                'method' => $method,
                'message' => $e->getMessage(),
                'exception' => $e::class,
            ]);

            return ['success' => false, 'message' => 'API request failed. Please check the logs.', 'data' => null];
        }
    }

    public function fetchBalance(RentingApi $api): array
    {
        try {
            $response = $this->client(15)->get($this->handlerUrl($api), [
                'api_key' => $api->api_key,
                'action' => 'getBalance',
            ]);

            $body = trim($response->body());

            if (!$response->successful() || str_starts_with($body, 'BAD_')) {
                Log::warning('Rental API balance fetch failed', [
                    'api_id' => $api->id,
                    'status' => $response->status(),
                    'body' => $body,
                ]);

                return ['success' => false, 'message' => $body ?: 'API request failed.', 'balance' => null];
            }

            $parts = explode(':', $body, 2);

            if (($parts[0] ?? '') !== 'ACCESS_BALANCE' || !isset($parts[1])) {
                return ['success' => false, 'message' => 'Invalid balance response.', 'balance' => null];
            }

            return [
                'success' => true,
                'balance' => trim($parts[1]),
                'message' => null,
            ];
        } catch (Throwable $e) {
            Log::error('Rental API balance exception', [
                'api_id' => $api->id,
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Balance fetch failed.', 'balance' => null];
        }
    }

    public function importCountries(RentingApi $api): array
    {
        try {
            $response = $this->client(30)->get($this->rentUrl($api), [
                'method' => 'getcountries',
            ]);

            $body = trim($response->body());

            if (!$response->successful()) {
                return ['success' => false, 'message' => $body ?: 'API request failed.'];
            }

            $json = json_decode($body, true);

            if (!is_array($json) || (int) ($json['status'] ?? 0) !== 1) {
                return ['success' => false, 'message' => 'Invalid or empty countries response.'];
            }

            $items = $json['data'] ?? [];

            if (!is_array($items) || $items === []) {
                return ['success' => false, 'message' => 'No countries returned.'];
            }

            $imported = 0;
            $skipped = 0;

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $code = strtoupper((string) ($item['code'] ?? $item['ccode'] ?? $item['country'] ?? ''));
                $name = (string) ($item['name'] ?? $item['country_name'] ?? $item['eng'] ?? $code);

                if ($code === '') {
                    continue;
                }

                $exists = RentingCountry::where('api_id', $api->id)
                    ->where('code', $code)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                RentingCountry::create([
                    'name' => $name,
                    'code' => $code,
                    'country_id' => $code,
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
        } catch (Throwable $e) {
            Log::error('Rental countries import exception', [
                'api_id' => $api->id,
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Country import failed. Please check the logs.'];
        }
    }

    public function importServices(
        RentingApi $api,
        ?int $countryId = null,
        string $dtype = 'week',
        int $dcount = 1
    ): array {
        try {
            $countriesQuery = RentingCountry::where('api_id', $api->id)->where('status', 1);

            if ($countryId !== null) {
                $countriesQuery->where('id', $countryId);
            }

            $countries = $countriesQuery->get();

            if ($countries->isEmpty()) {
                return ['success' => false, 'message' => 'No active countries found. Import countries first.'];
            }

            $settings = RentingSetting::current();
            $rate = (float) $settings->currency_rate;
            $increasePct = (float) $settings->price_increase;

            $imported = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($countries as $country) {
                $result = $this->request($api, 'getdataWithProviders', [
                    'country' => $country->code,
                    'dtype' => $dtype,
                    'dcount' => $dcount,
                ], 60);

                if (!$result['success']) {
                    $skipped++;
                    continue;
                }

                $payload = $result['data'];

                if (!is_array($payload)) {
                    $skipped++;
                    continue;
                }

                $services = $payload['services'] ?? $payload;

                if (!is_array($services)) {
                    $skipped++;
                    continue;
                }

                $serviceRows = array_is_list($services) ? $services : $this->normalizeKeyedServices($services);

                foreach ($serviceRows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }

                    $code = (string) ($row['code'] ?? $row['scode'] ?? $row['service'] ?? '');
                    $name = (string) ($row['name'] ?? $row['sname'] ?? strtoupper($code));

                    if ($code === '') {
                        continue;
                    }

                    $priceDay = (float) ($row['price_day'] ?? 0);
                    $providerPrice = round($priceDay * $this->rentalDaysMultiplier($dtype, $dcount), 2);

                    if ($providerPrice <= 0) {
                        $providerPrice = (float) ($row['price'] ?? $row['cost'] ?? $row['amount'] ?? 0);
                    }

                    if ($providerPrice <= 0) {
                        continue;
                    }

                    $retailPrice = round($providerPrice * $rate * (1 + $increasePct / 100), 2);

                    $service = RentingService::where('api_id', $api->id)
                        ->where('country_id', $country->id)
                        ->where('code', $code)
                        ->where('dtype', $dtype)
                        ->where('dcount', $dcount)
                        ->first();

                    if ($service) {
                        $service->update(['name' => $name]);
                        $updated++;
                    } else {
                        $service = RentingService::create([
                            'name' => $name,
                            'code' => $code,
                            'country_id' => $country->id,
                            'api_id' => $api->id,
                            'dtype' => $dtype,
                            'dcount' => $dcount,
                            'status' => 1,
                        ]);
                        $imported++;
                    }

                    $operatorStock = $this->extractOperatorStock($row, $payload);

                    foreach ($operatorStock as $operatorName => $stock) {
                        $operator = RentingOperator::firstOrCreate(
                            [
                                'api_id' => $api->id,
                                'country_id' => $country->id,
                                'name' => $operatorName,
                            ],
                            ['status' => 1]
                        );

                        RentingServiceOperator::updateOrCreate(
                            [
                                'service_id' => $service->id,
                                'operator_id' => $operator->id,
                            ],
                            [
                                'provider_price' => $providerPrice,
                                'price' => $retailPrice,
                                'stock' => $stock,
                                'status' => 1,
                            ]
                        );
                    }
                }
            }

            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
            ];
        } catch (Throwable $e) {
            Log::error('Rental services import exception', [
                'api_id' => $api->id,
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Service import failed. Please check the logs.'];
        }
    }

    public function create(
        RentingApi $api,
        string $countryCode,
        string $serviceCode,
        string $dtype,
        int $dcount,
        ?string $provider = null
    ): array {
        $params = [
            'country' => $countryCode,
            'service' => $serviceCode,
            'dtype' => $dtype,
            'dcount' => $dcount,
        ];

        if ($provider !== null && $provider !== '') {
            $params['provider'] = $provider;
        }

        $result = $this->request($api, 'create', $params);

        if (!$result['success']) {
            return $result;
        }

        $item = $this->firstDataItem($result['data']);

        return [
            'success' => true,
            'message' => null,
            'provider_order_id' => (int) ($item['id'] ?? $item['orderid'] ?? $item['order_id'] ?? 0),
            'phone' => (string) ($item['pnumber'] ?? $item['number'] ?? $item['phone'] ?? ''),
            'until' => $item['until'] ?? $item['endDate'] ?? null,
            'data' => $item,
        ];
    }

    public function activate(RentingApi $api, int $providerOrderId): array
    {
        return $this->request($api, 'activate', ['id' => $providerOrderId]);
    }

    public function getOrders(RentingApi $api): array
    {
        $result = $this->request($api, 'orders');

        if (!$result['success']) {
            return $result;
        }

        return [
            'success' => true,
            'message' => null,
            'orders' => is_array($result['data']) ? $result['data'] : [],
        ];
    }

    public function getSms(RentingApi $api, int $providerOrderId): array
    {
        $result = $this->request($api, 'sms', ['id' => $providerOrderId], 15);

        if (!$result['success']) {
            return $result;
        }

        $data = is_array($result['data']) ? $result['data'] : [];

        return [
            'success' => true,
            'message' => null,
            'sms_list' => $data['SmsList'] ?? [],
            'other_sms' => $data['OtherSms'] ?? [],
            'data' => $data,
        ];
    }

    public function prolong(RentingApi $api, int $providerOrderId, string $dtype, int $dcount): array
    {
        return $this->request($api, 'prolong', [
            'id' => $providerOrderId,
            'dtype' => $dtype,
            'dcount' => $dcount,
        ]);
    }

    public function prolongMax(RentingApi $api, int $providerOrderId, int $dcount): array
    {
        return $this->request($api, 'prolong_max', [
            'id' => $providerOrderId,
            'dtype' => 'day',
            'dcount' => $dcount,
        ]);
    }

    public function getRentHistory(RentingApi $api, int $skip = 0, int $take = 10): array
    {
        $result = $this->request($api, 'get_rent_history', [
            'skip' => $skip,
            'take' => $take,
        ]);

        if (!$result['success']) {
            return $result;
        }

        return [
            'success' => true,
            'message' => null,
            'history' => is_array($result['data']) ? $result['data'] : [],
        ];
    }

    public function restorePrecalc(RentingApi $api, int $providerOrderId): array
    {
        return $this->request($api, 'restore_user_precalc', ['id' => $providerOrderId]);
    }

    public function restore(RentingApi $api, int $providerOrderId): array
    {
        return $this->request($api, 'restore_user', ['id' => $providerOrderId]);
    }

    public function getDataWithProviders(
        RentingApi $api,
        string $countryCode,
        string $dtype,
        int $dcount
    ): array {
        return $this->request($api, 'getdataWithProviders', [
            'country' => $countryCode,
            'dtype' => $dtype,
            'dcount' => $dcount,
        ]);
    }

    private function firstDataItem(mixed $data): array
    {
        if (!is_array($data)) {
            return [];
        }

        if (array_is_list($data)) {
            return is_array($data[0] ?? null) ? $data[0] : [];
        }

        return $data;
    }

    private function normalizeKeyedServices(array $services): array
    {
        $rows = [];

        foreach ($services as $code => $info) {
            if (!is_array($info)) {
                continue;
            }

            $rows[] = array_merge($info, [
                'code' => (string) ($info['code'] ?? $info['scode'] ?? $code),
            ]);
        }

        return $rows;
    }

    public function retailPrice(float $providerPrice): float
    {
        $settings = RentingSetting::current();
        $rate = (float) $settings->currency_rate;
        $increasePct = (float) $settings->price_increase;

        return round($providerPrice * $rate * (1 + $increasePct / 100), 2);
    }

    private function rentalDaysMultiplier(string $dtype, int $dcount): int
    {
        $dcount = max(1, $dcount);

        return match ($dtype) {
            'day' => $dcount,
            'week' => 7 * $dcount,
            'month' => 30 * $dcount,
            default => 7 * $dcount,
        };
    }

    /**
     * @return array<string, int> operator name => stock count
     */
    private function extractOperatorStock(array $row, array $payload): array
    {
        $stock = [];

        if (isset($row['count']) && is_array($row['count'])) {
            foreach ($row['count'] as $operatorName => $count) {
                if (is_string($operatorName) && $operatorName !== '') {
                    $stock[$operatorName] = max(0, (int) $count);
                }
            }
        }

        if ($stock === [] && isset($payload['allOperators']) && is_array($payload['allOperators'])) {
            $fallbackStock = max(0, (int) ($row['totalCount'] ?? 0));

            foreach ($payload['allOperators'] as $operatorName) {
                if (is_string($operatorName) && $operatorName !== '') {
                    $stock[$operatorName] = $fallbackStock;
                }
            }
        }

        return $stock;
    }
}
