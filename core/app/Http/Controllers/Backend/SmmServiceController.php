<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SmmCategory;
use App\Models\SmmProvider;
use App\Models\SmmService;
use App\Models\SmmSubcategory;
use App\Models\Setting;
use App\Services\SmmApiService;
use Illuminate\Http\Request;

class SmmServiceController extends Controller
{
    public function __construct(
        protected SmmApiService $smmApi
    ) {}

    public function index(Request $request)
    {
        $title = 'SMM Services';
        $services = SmmService::with(['smmCategory', 'smmSubcategory', 'smmProvider'])
            ->when($request->category_id, fn ($q) => $q->where('smm_category_id', $request->category_id))
            ->when($request->subcategory_id, fn ($q) => $q->where('smm_subcategory_id', $request->subcategory_id))
            ->when($request->provider_id, fn ($q) => $q->where('api_provider_id', $request->provider_id))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(20)->withQueryString();
        $categories = SmmCategory::orderBy('sort')->get();
        $subcategories = SmmSubcategory::with('smmCategory')->orderBy('smm_category_id')->orderBy('sort')->get();
        $smmSubcategoryRowsForJs = $subcategories->map(function (SmmSubcategory $sub) {
            $catName = optional($sub->smmCategory)->name ?? '';

            return [
                'id' => $sub->id,
                'cid' => (int) $sub->smm_category_id,
                'label' => $catName !== '' ? $catName.' — '.$sub->name : $sub->name,
            ];
        })->values();
        $providers = SmmProvider::where('status', 1)->get();
        $search = $request->search ?? '';

        return view('backend.smm.services.index', compact(
            'title',
            'services',
            'categories',
            'subcategories',
            'smmSubcategoryRowsForJs',
            'providers',
            'search'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'smm_category_id' => 'required|exists:smm_categories,id',
            'smm_subcategory_id' => 'required|exists:smm_subcategories,id',
            'min' => 'required|integer|min:0',
            'max' => 'required|integer|min:1',
            'average_time_minutes' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:0,1',
        ]);
        $sub = SmmSubcategory::findOrFail($request->smm_subcategory_id);
        if ((int) $sub->smm_category_id !== (int) $request->smm_category_id) {
            return redirect()->back()->withErrors(['smm_subcategory_id' => 'Subcategory must belong to the selected category.'])->withInput();
        }
        SmmService::create([
            'name' => $request->name,
            'smm_category_id' => $request->smm_category_id,
            'smm_subcategory_id' => $request->smm_subcategory_id,
            'min' => $request->min,
            'max' => $request->max,
            'average_time_minutes' => $request->average_time_minutes,
            'price' => $request->price,
            'add_type' => 'manual',
            'status' => (bool) $request->status,
        ]);

        return redirect()->back()->with('success', 'SMM service added.');
    }

    public function update(Request $request, $id)
    {
        $svc = SmmService::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'smm_category_id' => 'required|exists:smm_categories,id',
            'smm_subcategory_id' => 'required|exists:smm_subcategories,id',
            'min' => 'required|integer|min:0',
            'max' => 'required|integer|min:1',
            'average_time_minutes' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:0,1',
        ]);
        $sub = SmmSubcategory::findOrFail($request->smm_subcategory_id);
        if ((int) $sub->smm_category_id !== (int) $request->smm_category_id) {
            return redirect()->back()->withErrors(['smm_subcategory_id' => 'Subcategory must belong to the selected category.'])->withInput();
        }
        $svc->update($request->only('name', 'smm_category_id', 'smm_subcategory_id', 'min', 'max', 'average_time_minutes', 'price', 'status') + ['status' => (bool) $request->status]);

        return redirect()->back()->with('success', 'SMM service updated.');
    }

    public function destroy($id)
    {
        SmmService::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'SMM service deleted.');
    }

    public function syncProviderPrices(Request $request)
    {
        $request->validate([
            'offset' => 'nullable|integer|min:0',
            'batch_size' => 'nullable|integer|min:1|max:25',
        ]);

        $offset = (int) $request->input('offset', 0);
        $batchSize = (int) $request->input('batch_size', 5);

        $baseQuery = SmmService::query()
            ->where('add_type', 'api')
            ->whereNotNull('api_provider_id')
            ->whereNotNull('api_service_id');

        $total = (clone $baseQuery)->count();
        $rows = (clone $baseQuery)->orderBy('id')->skip($offset)->take($batchSize)->get();

        $updated = 0;
        $nulled = 0;
        $errors = 0;

        /** @var array<int, array<string, array<string, mixed>>|null> $listCache */
        $listCache = [];

        foreach ($rows as $index => $row) {
            if ($index > 0) {
                usleep(350_000);
            }

            $providerId = (int) $row->api_provider_id;
            if (! array_key_exists($providerId, $listCache)) {
                $provider = SmmProvider::find($providerId);
                if (! $provider || ! (int) $provider->status) {
                    $listCache[$providerId] = null;
                } else {
                    $listCache[$providerId] = $this->smmApi->servicesIndexedByServiceId(
                        $provider->only('url', 'key', 'id')
                    );
                }
            }

            $map = $listCache[$providerId];
            if ($map === null || $map === []) {
                $errors++;
                continue;
            }

            $apiId = (string) $row->api_service_id;
            $item = $map[$apiId] ?? null;
            if ($item === null) {
                $row->update(['original_price' => null]);
                $nulled++;
                continue;
            }

            $parsed = $this->smmApi->extractRateAndLimits($item);
            if ($parsed === null) {
                $errors++;
                continue;
            }

            $row->update([
                'original_price' => $parsed['rate'],
                'min' => $parsed['min'],
                'max' => $parsed['max'],
            ]);
            $updated++;
        }

        $processedNow = $rows->count();
        $nextOffset = $offset + $processedNow;

        return response()->json([
            'ok' => true,
            'processed_now' => $processedNow,
            'processed_total' => min($nextOffset, $total),
            'total' => $total,
            'next_offset' => $nextOffset,
            'has_more' => $nextOffset < $total,
            'updated' => $updated,
            'nulled' => $nulled,
            'errors' => $errors,
        ]);
    }

    public function syncTotalPrices(Request $request)
    {
        $request->validate([
            'offset' => 'nullable|integer|min:0',
            'batch_size' => 'nullable|integer|min:1|max:200',
        ]);

        $settings = Setting::first();
        if (! $settings) {
            return response()->json([
                'ok' => false,
                'message' => 'Settings not found.',
            ], 422);
        }

        // Currency conversion is per-provider (Settings → SMM Providers), since
        // different providers bill in different currencies (e.g. a USD panel vs
        // a NGN panel). The old single global currency_rate is no longer used
        // here — using it uniformly for every provider previously produced
        // wildly wrong costs/prices for any provider not actually priced in
        // that one currency.
        $providerRates = SmmProvider::pluck('currency_rate', 'id');
        $priceIncrease = (float) ($settings->price_increase ?? 0);

        $offset = (int) $request->input('offset', 0);
        $batchSize = (int) $request->input('batch_size', 100);

        $baseQuery = SmmService::query()->where('add_type', 'api');
        $total = (clone $baseQuery)->count();
        $rows = (clone $baseQuery)->orderBy('id')->skip($offset)->take($batchSize)->get();

        $updated = 0;
        $nulled = 0;

        foreach ($rows as $row) {
            if ($row->original_price === null) {
                $row->update(['price' => 0]);
                $nulled++;
                continue;
            }

            $currencyRate = (float) ($providerRates[$row->api_provider_id] ?? 1);
            $providerPrice = (float) $row->original_price;
            $base = $providerPrice * $currencyRate;
            $yourPrice = $base + (($base * $priceIncrease) / 100);

            $row->update([
                'price' => round($yourPrice, 4),
            ]);
            $updated++;
        }

        $processedNow = $rows->count();
        $nextOffset = $offset + $processedNow;

        return response()->json([
            'ok' => true,
            'processed_now' => $processedNow,
            'processed_total' => min($nextOffset, $total),
            'total' => $total,
            'next_offset' => $nextOffset,
            'has_more' => $nextOffset < $total,
            'updated' => $updated,
            'nulled' => $nulled,
            'price_increase' => $priceIncrease,
        ]);
    }
}