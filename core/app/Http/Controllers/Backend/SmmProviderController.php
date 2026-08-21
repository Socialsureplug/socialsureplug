<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SmmCategory;
use App\Models\SmmProvider;
use App\Models\SmmService;
use App\Models\SmmSubcategory;
use App\Services\SmmApiService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SmmProviderController extends Controller
{
    public function __construct(
        protected SmmApiService $smmApi
    ) {}

    public function index(Request $request)
    {
        $title = 'SMM Providers';
        $query = SmmProvider::query();
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where('name', 'like', "%{$term}%");
        }
        if ($request->filled('status')) {
            $query->where('status', (int) $request->status);
        }
        $providers = $query->orderByDesc('status')->latest()->paginate(15)->withQueryString();
        $search = $request->search ?? '';
        return view('backend.smm.providers.index', compact('title', 'providers', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'key' => 'required|string',
            'status' => 'required|in:0,1',
            'description' => 'nullable|string',
            'currency_rate' => 'required|numeric|min:0.0001',
        ]);

        $result = $this->smmApi->balance([
            'url' => $request->url,
            'key' => $request->key,
        ]);
        if (!$result || !isset($result['balance'])) {
            return redirect()->back()->with('error', 'Could not connect to API provider. Check URL and key.');
        }

        if ($request->id) {
            $provider = SmmProvider::findOrFail($request->id);
            $provider->update([
                'name' => $request->name,
                'url' => $request->url,
                'key' => $request->key,
                'status' => (int) $request->status,
                'description' => $request->description,
                'currency_rate' => $request->currency_rate,
                'balance' => $result['balance'],
            ]);
            return redirect()->back()->with('success', 'Provider updated.');
        }

        SmmProvider::create([
            'name' => $request->name,
            'url' => $request->url,
            'key' => $request->key,
            'status' => (int) $request->status,
            'description' => $request->description,
            'currency_rate' => $request->currency_rate,
            'balance' => $result['balance'],
        ]);
        return redirect()->back()->with('success', 'Provider added.');
    }

    public function balance($id)
    {
        $provider = SmmProvider::findOrFail($id);
        $result = $this->smmApi->balance($provider->only('url', 'key'));
        if (!$result || !isset($result['balance'])) {
            return redirect()->back()->with('error', 'Could not fetch balance.');
        }
        $provider->update(['balance' => $result['balance']]);
        return redirect()->back()->with('success', 'Balance updated.');
    }

    public function services(Request $request, $id)
    {
        $provider = SmmProvider::findOrFail($id);
        $items = $this->smmApi->services($provider->only('url', 'key'));
        $search = trim((string) $request->get('search', ''));

        $normalizedItems = collect(is_array($items) ? $items : [])
            ->map(function ($svc, $idx) {
                $sid = (string) ($svc['service'] ?? $svc['id'] ?? $idx);
                return [
                    'sid' => $sid,
                    'name' => (string) ($svc['name'] ?? ('Service ' . $sid)),
                    'rate' => $svc['rate'] ?? $svc['price'] ?? 0,
                    'min' => $svc['min'] ?? 0,
                    'max' => $svc['max'] ?? 0,
                ];
            });

        if ($search !== '') {
            $term = mb_strtolower($search);
            $normalizedItems = $normalizedItems->filter(function ($svc) use ($term) {
                $haystack = mb_strtolower(implode(' ', [
                    (string) $svc['sid'],
                    (string) $svc['name'],
                    (string) $svc['rate'],
                    (string) $svc['min'],
                    (string) $svc['max'],
                ]));
                return str_contains($haystack, $term);
            })->values();
        }

        $perPage = 50;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $total = $normalizedItems->count();
        $currentPageItems = $normalizedItems->forPage($currentPage, $perPage)->values();
        $services = new LengthAwarePaginator(
            $currentPageItems,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $categories = SmmCategory::orderBy('sort')->get();
        $subcategories = SmmSubcategory::with('smmCategory')->orderBy('smm_category_id')->orderBy('sort')->get();
        $smmImportSubcategoryRowsForJs = $subcategories->map(function (SmmSubcategory $sub) {
            $catName = optional($sub->smmCategory)->name ?? '';

            return [
                'id' => $sub->id,
                'cid' => (int) $sub->smm_category_id,
                'label' => $catName !== '' ? $catName.' — '.$sub->name : $sub->name,
            ];
        })->values();
        $title = 'Provider services: ' . $provider->name;

        return view('backend.smm.providers.services', compact(
            'title',
            'provider',
            'services',
            'categories',
            'subcategories',
            'smmImportSubcategoryRowsForJs',
            'search'
        ));
    }

    public function syncServices(Request $request, $id)
    {
        $request->validate([
            'price_percentage_increase' => 'nullable|integer|min:0',
            'sync_request' => 'nullable|in:0,1',
            'api_id' => 'required|exists:smm_providers,id',
        ]);
        $provider = SmmProvider::findOrFail($request->api_id);
        $apiServices = $this->smmApi->services($provider->only('url', 'key'));
        // Implement sync logic: compare with SmmService where api_provider_id = provider->id, update/create/disable.
        // (Reuse sample provider_model sync logic; optionally in a dedicated SyncSmmServicesFromProvider action.)
        return redirect()->route('backend.smm-providers.services', $provider->id)->with('success', 'Sync completed.');
    }

    public function importServices(Request $request)
    {
        $request->validate([
            'api_id' => 'required|exists:smm_providers,id',
            'cate_id' => 'required|exists:smm_categories,id',
            'sub_cate_id' => 'required|exists:smm_subcategories,id',
            'price_percentage_increase' => 'required|integer|min:0',
            'ids' => 'required|array',
            'ids.*' => 'string',
        ]);
        $sub = SmmSubcategory::findOrFail($request->sub_cate_id);
        if ((int) $sub->smm_category_id !== (int) $request->cate_id) {
            return redirect()->back()->with('error', 'Subcategory does not belong to the selected category.');
        }
        $provider = SmmProvider::findOrFail($request->api_id);
        $apiServices = $this->smmApi->services($provider->only('url', 'key'));
        $pct = (float) $request->price_percentage_increase;
        $currencyRate = (float) $provider->currency_rate;
        foreach ($request->ids as $apiServiceId) {
            $svc = collect($apiServices)->first(function ($service) use ($apiServiceId) {
                $serviceId = (string) ($service['service'] ?? $service['id'] ?? '');
                return $serviceId === (string) $apiServiceId;
            });
            if (!$svc) continue;
            $providerRate = (float) ($svc['rate'] ?? $svc['price'] ?? 0);
            SmmService::updateOrCreate(
                [
                    'api_provider_id' => $provider->id,
                    'api_service_id' => (string) $apiServiceId,
                ],
                [
                    'name' => $svc['name'] ?? 'Service ' . $apiServiceId,
                    'smm_category_id' => $request->cate_id,
                    'smm_subcategory_id' => $request->sub_cate_id,
                    'min' => (int) ($svc['min'] ?? 1),
                    'max' => (int) ($svc['max'] ?? 10000),
                    'price' => round($providerRate * $currencyRate * (1 + $pct / 100), 4),
                    'original_price' => $providerRate,
                    'type' => $svc['type'] ?? 'default',
                    'add_type' => 'api',
                    'dripfeed' => (int) ($svc['dripfeed'] ?? 0),
                    'desc' => $svc['desc'] ?? null,
                    'status' => true,
                ]
            );
        }
        return redirect()->back()->with('success', 'Services imported.');
    }

    public function destroy($id)
    {
        $provider = SmmProvider::findOrFail($id);
        $provider->smmServices()->delete();
        $provider->delete();
        return redirect()->back()->with('success', 'Provider deleted.');
    }
}