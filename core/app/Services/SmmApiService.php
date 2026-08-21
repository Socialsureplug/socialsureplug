<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmmApiService
{
    /**
     * Build provider API endpoint URL.
     */
    private function apiUrl(array $provider): string
    {
        $url = rtrim((string) ($provider['url'] ?? ''), '/');

        if ($url === '') {
            return '';
        }

        return $url.'/api/v2';
    }

    /**
     * SSL verify option: use CA bundle path if file exists, else disable verify (avoids cURL 77 when cacert.pem is missing).
     */
    private function verifyOption(): bool|string
    {
        $path = config('http.ca_bundle');
        return ($path && is_file($path)) ? $path : false;
    }

    private function client()
    {
        return Http::withOptions(['verify' => $this->verifyOption()])->asForm()->timeout(30);
    }

    public function balance(array $provider): ?array
    {
        $url = $this->apiUrl($provider);
        $res = $this->client()->post($url, [
            'key' => $provider['key'],
            'action' => 'balance',
        ]);
        $data = $res->json();
        if (isset($data['balance'])) {
            return $data;
        }
        Log::warning('SMM API balance failed', ['response' => $data, 'provider' => $provider['id'] ?? null]);
        return null;
    }

    public function services(array $provider): array
    {
        $url = $this->apiUrl($provider);
        $res = $this->client()->post($url, [
            'key' => $provider['key'],
            'action' => 'services',
        ]);
        $data = $res->json();
        if (is_array($data)) {
            return $data;
        }
        return [];
    }

    /**
     * Services list keyed by API service ID (string) for quick lookups when syncing prices.
     *
     * @return array<string, array<string, mixed>>
     */
    public function servicesIndexedByServiceId(array $provider): array
    {
        $data = $this->services($provider);
        if ($data === [] || ! is_array($data)) {
            return [];
        }

        if (isset($data['error'])) {
            Log::warning('SMM API services returned error', [
                'provider' => $provider['id'] ?? null,
                'error' => $data['error'],
            ]);

            return [];
        }

        $indexed = [];
        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }

            $sid = $item['service'] ?? $item['id'] ?? null;
            if ($sid === null || $sid === '') {
                continue;
            }

            $indexed[(string) $sid] = $item;
        }

        return $indexed;
    }

    /**
     * Parse provider rate and min/max from one service row in the API list.
     *
     * @return array{rate: float, min: int, max: int}|null
     */
    public function extractRateAndLimits(array $serviceRow): ?array
    {
        $rawRate = $serviceRow['rate'] ?? $serviceRow['price'] ?? null;
        if ($rawRate === null || $rawRate === '') {
            return null;
        }

        return [
            'rate' => (float) $rawRate,
            'min' => (int) ($serviceRow['min'] ?? 1),
            'max' => (int) ($serviceRow['max'] ?? 10000),
        ];
    }

    public function order(array $provider, array $post): ?array
    {
        $url = $this->apiUrl($provider);
        $params = array_merge(['key' => $provider['key'], 'action' => 'add'], $post);
        $res = $this->client()->post($url, $params);
        return $res->json();
    }

    public function status(array $provider, string $orderId): ?array
    {
        $url = $this->apiUrl($provider);
        $res = $this->client()->post($url, [
            'key' => $provider['key'],
            'action' => 'status',
            'order' => $orderId,
        ]);
        return $res->json();
    }
}