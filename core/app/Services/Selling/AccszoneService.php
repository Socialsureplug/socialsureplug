<?php

namespace App\Services\Selling;

use App\Models\SellingApi;
use App\Models\SellingProduct;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AccszoneService
{
    private string $baseUrl;

    public function __construct(
        string $baseUrl,
        private readonly string $apiKey
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public static function fromSellingApi(SellingApi $api): self
    {
        return new self((string) $api->base_url, (string) $api->api_key);
    }

    public function balance(): string
    {
        $payload = $this->get('user/balance');

        return (string) data_get($payload['data'] ?? [], 'balance', '');
    }

    public function categories(): array
    {
        $payload = $this->get('categories');
        $data = $payload['data'] ?? [];

        return is_array($data) ? $data : [];
    }

    public function subcategories(int $remoteCategoryId): array
    {
        $payload = $this->get("categories/{$remoteCategoryId}/subcategories");
        $data = data_get($payload, 'data.subcategories', []);

        return is_array($data) ? $data : [];
    }

    public function listings(array $query = []): array
    {
        $payload = $this->get('listings', $query);

        return [
            'data' => is_array($payload['data'] ?? null) ? $payload['data'] : [],
            'meta' => is_array($payload['meta'] ?? null) ? $payload['meta'] : [],
        ];
    }

    public function fetchAllListings(): array
    {
        $merged = [];
        $page = 1;

        do {
            $chunk = $this->listings(['per_page' => 100, 'page' => $page]);
            $merged = array_merge($merged, $chunk['data']);
            $last = (int) ($chunk['meta']['last_page'] ?? 1);
            $page++;
        } while ($page <= $last);

        return $merged;
    }

    public function purchase(int $adId, int $quantity): array
    {
        $payload = $this->post('purchase', [
            'ad_id' => $adId,
            'quantity' => $quantity,
        ]);
        $data = $payload['data'] ?? [];

        return is_array($data) ? $data : [];
    }

    public function listingDetail(string $slug): array
    {
        $payload = $this->get("listings/{$slug}");
        $data = $payload['data'] ?? [];

        return is_array($data) ? $data : [];
    }

    public function fetchDescriptionForProduct(SellingProduct $product): array
    {
        $slug = (string) ($product->slug ?? '');

        if ($slug === '') {
            return [
                'id' => $product->id,
                'provider_id' => $product->provider_id,
                'title' => $product->title,
                'slug' => null,
                'description' => null,
                'success' => false,
                'error' => 'Missing slug',
            ];
        }

        try {
            $detail = $this->listingDetail($slug);
            $descriptionHtml = isset($detail['description']) ? (string) $detail['description'] : null;

            return [
                'id' => $product->id,
                'provider_id' => $product->provider_id,
                'title' => $product->title,
                'slug' => $slug,
                'description' => $this->normalizeDescriptionToPlainText($descriptionHtml),
                'success' => true,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'id' => $product->id,
                'provider_id' => $product->provider_id,
                'title' => $product->title,
                'slug' => $slug,
                'description' => null,
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function normalizeDescriptionToPlainText(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $value = trim($html);
        if ($value === '') {
            return null;
        }

        $value = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $value) ?? $value;
        $value = preg_replace('/<\/\s*p\s*>/i', "\n\n", $value) ?? $value;

        $text = strip_tags($value);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        $text = trim($text);

        return $text === '' ? null : $text;
    }

    private function get(string $path, array $query = []): array
    {
        try {
            $response = Http::baseUrl($this->baseUrl . '/')
                ->withHeaders(['X-API-Key' => $this->apiKey])
                ->acceptJson()
                ->timeout(45)
                ->get($path, $query);
        } catch (RequestException $e) {
            throw new RuntimeException('AccsZone request failed: ' . $e->getMessage(), 0, $e);
        }

        return $this->decodeSuccessfulResponse($response);
    }

    private function post(string $path, array $body): array
    {
        try {
            $response = Http::baseUrl($this->baseUrl . '/')
                ->withHeaders(['X-API-Key' => $this->apiKey])
                ->acceptJson()
                ->asJson()
                ->timeout(90)
                ->post($path, $body);
        } catch (RequestException $e) {
            throw new RuntimeException('AccsZone request failed: ' . $e->getMessage(), 0, $e);
        }

        return $this->decodeSuccessfulResponse($response);
    }

    private function decodeSuccessfulResponse(\Illuminate\Http\Client\Response $response): array
    {
        if (!$response->successful()) {
            $json = $response->json();
            $msg = is_array($json) ? (string) ($json['message'] ?? '') : '';
            if ($msg === '') {
                $msg = $response->body() ?: 'empty body';
            }

            throw new RuntimeException(
                'AccsZone HTTP ' . $response->status() . ': ' . $msg
            );
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json() ?? [];

        if (empty($payload['success'])) {
            throw new RuntimeException((string) ($payload['message'] ?? 'AccsZone reported failure'));
        }

        return $payload;
    }
}
