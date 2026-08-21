<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Jobs\ImportNumberServicesJob;
use App\Models\NumberApi;
use App\Models\NumberCountry;
use App\Models\NumberOrder;
use App\Models\NumberService;
use App\Models\NumberSetting;
use App\Models\NumberSyncRun;
use App\Services\VirtualService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NumberController extends Controller
{
    public function api(Request $request)
    {
        $data['title'] = 'API';

        $query = NumberApi::query();

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('label', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%");
            });
        }

        $data['apis'] = $query->latest()->paginate(10)->withQueryString();
        $data['search'] = $request->search ?? '';

        return view('backend.numbers.api', $data);
    }

    public function fetchBalances(Request $request, VirtualService $virtualService)
    {
        $validated = $request->validate([
            'api_ids' => 'required|array|min:1',
            'api_ids.*' => 'integer|exists:number_api,id',
        ]);

        $balances = [];

        foreach (NumberApi::whereIn('id', $validated['api_ids'])->orderBy('id')->get() as $api) {
            $result = $virtualService->fetchBalance($api);

            if ($result['success']) {
                $api->update(['balance' => $result['balance']]);
                $balances[(string) $api->id] = $result['balance'];
            } else {
                $api->update(['balance' => null]);
                $balances[(string) $api->id] = '—';
            }
        }

        return response()->json([
            'success' => true,
            'balances' => $balances,
        ]);
    }

    public function storeApi(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:number_api,slug',
            'base_url' => 'required|url|max:500',
            'api_key' => 'required|string|max:255',
            'cancel_code' => 'required|string|max:10',
            'status' => 'required|in:0,1',
        ]);

        NumberApi::create($validated);

        return redirect()
            ->route('backend.numbers.api')
            ->with('success', 'API added successfully');
    }

    public function updateApi(Request $request, $id)
    {
        $api = NumberApi::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:number_api,slug,' . $id,
            'base_url' => 'required|url|max:500',
            'api_key' => 'required|string|max:255',
            'cancel_code' => 'required|string|max:10',
            'status' => 'required|in:0,1',
        ]);

        $api->update($validated);

        return redirect()
            ->route('backend.numbers.api')
            ->with('success', 'API updated successfully');
    }

    public function destroyApi($id)
    {
        NumberApi::findOrFail($id)->delete();

        return redirect()
            ->route('backend.numbers.api')
            ->with('success', 'API deleted successfully');
    }

    public function toggleApiStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:number_api,id',
            'status' => 'required|in:0,1',
        ]);

        NumberApi::where('id', $validated['id'])->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    public function countries(Request $request, $apiId)
    {
        $api = NumberApi::findOrFail($apiId);

        $query = NumberCountry::where('api_id', $apiId);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhere('country_id', 'like', "%{$term}%");
            });
        }

        $data['title'] = 'Countries — ' . $api->name;
        $data['api'] = $api;
        $data['countries'] = $query->orderBy('name')->paginate(10)->withQueryString();
        $data['search'] = $request->search ?? '';

        return view('backend.numbers.countries', $data);
    }

    public function storeCountry(Request $request, $apiId)
    {
        NumberApi::findOrFail($apiId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'country_id' => 'required|string|max:50',
            'status' => 'required|in:0,1',
        ]);

        $validated['api_id'] = $apiId;

        NumberCountry::create($validated);

        return redirect()
            ->route('backend.numbers.api.countries', $apiId)
            ->with('success', 'Country added successfully');
    }

    public function updateCountry(Request $request, $id)
    {
        $country = NumberCountry::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'country_id' => 'required|string|max:50',
            'status' => 'required|in:0,1',
        ]);

        $country->update($validated);

        return redirect()
            ->route('backend.numbers.api.countries', $country->api_id)
            ->with('success', 'Country updated successfully');
    }

    public function destroyCountry($id)
    {
        $country = NumberCountry::findOrFail($id);
        $apiId = $country->api_id;
        $country->delete();

        return redirect()
            ->route('backend.numbers.api.countries', $apiId)
            ->with('success', 'Country deleted successfully');
    }

    public function toggleCountryStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:number_countries,id',
            'status' => 'required|in:0,1',
        ]);

        NumberCountry::where('id', $validated['id'])->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    public function importCountries($apiId, Request $request, VirtualService $virtualService)
    {
        $api = NumberApi::findOrFail($apiId);

        $result = $virtualService->importCountries($api);

        if ($request->expectsJson()) {
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => "Updated {$result['imported']} countries. Skipped {$result['skipped']}.",
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
            ]);
        }

        if (!$result['success']) {
            return redirect()
                ->route('backend.numbers.api.countries', $apiId)
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('backend.numbers.api.countries', $apiId)
            ->with('success', "Updated {$result['imported']} countries. Skipped {$result['skipped']}.");
    }

    public function services(Request $request, $apiId)
    {
        $api = NumberApi::findOrFail($apiId);

        $query = NumberService::with('numberCountry')->where('api_id', $apiId);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhereHas('numberCountry', function ($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%");
                    });
            });
        }

        $data['title'] = 'Services — ' . $api->name;
        $data['api'] = $api;
        $data['countries'] = NumberCountry::where('api_id', $apiId)->orderBy('name')->get();
        $data['services'] = $query->orderBy('name')->paginate(10)->withQueryString();
        $data['search'] = $request->search ?? '';
        $data['activeSyncRun'] = NumberSyncRun::where('api_id', $apiId)
            ->where('type', NumberSyncRun::TYPE_SERVICES)
            ->whereIn('status', [NumberSyncRun::STATUS_QUEUED, NumberSyncRun::STATUS_RUNNING])
            ->latest('id')
            ->first();

        return view('backend.numbers.services', $data);
    }

    public function storeService(Request $request, $apiId)
    {
        NumberApi::findOrFail($apiId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'country_id' => 'required|exists:number_countries,id',
            'provider_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:0,1',
        ]);

        $country = NumberCountry::where('id', $validated['country_id'])->where('api_id', $apiId)->firstOrFail();

        NumberService::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'country_id' => $country->id,
            'api_id' => $apiId,
            'provider_price' => $validated['provider_price'],
            'price' => $validated['price'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('backend.numbers.api.services', $apiId)
            ->with('success', 'Service added successfully');
    }

    public function updateService(Request $request, $id)
    {
        $service = NumberService::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'country_id' => 'required|exists:number_countries,id',
            'provider_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:0,1',
        ]);

        NumberCountry::where('id', $validated['country_id'])->where('api_id', $service->api_id)->firstOrFail();

        $service->update($validated);

        return redirect()
            ->route('backend.numbers.api.services', $service->api_id)
            ->with('success', 'Service updated successfully');
    }

    public function destroyService($id)
    {
        $service = NumberService::findOrFail($id);
        $apiId = $service->api_id;
        $service->delete();

        return redirect()
            ->route('backend.numbers.api.services', $apiId)
            ->with('success', 'Service deleted successfully');
    }

    public function toggleServiceStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:number_services,id',
            'status' => 'required|in:0,1',
        ]);

        NumberService::where('id', $validated['id'])->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    public function settings()
    {
        $data['title'] = 'Number settings';
        $data['numberSettings'] = NumberSetting::current();

        return view('backend.numbers.settings', $data);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'currency_rate' => 'required|numeric|min:0',
            'price_increase' => 'required|numeric|min:0',
        ]);

        NumberSetting::current()->update($validated);

        return redirect()
            ->route('backend.numbers.settings')
            ->with('success', 'Number settings updated successfully');
    }

    public function importServices($apiId, Request $request)
    {
        $api = NumberApi::findOrFail($apiId);

        $alreadyRunning = NumberSyncRun::where('api_id', $api->id)
            ->where('type', NumberSyncRun::TYPE_SERVICES)
            ->whereIn('status', [NumberSyncRun::STATUS_QUEUED, NumberSyncRun::STATUS_RUNNING])
            ->exists();

        if ($alreadyRunning) {
            return response()->json([
                'success' => false,
                'message' => 'A service import is already running for this API.',
            ], 409);
        }

        $countryId = $request->input('country_id');

        $run = NumberSyncRun::create([
            'api_id' => $api->id,
            'country_id' => $countryId ? (int) $countryId : null,
            'type' => NumberSyncRun::TYPE_SERVICES,
            'status' => NumberSyncRun::STATUS_QUEUED,
            'admin_id' => Auth::guard('admin')->id(),
        ]);

        ImportNumberServicesJob::dispatch($run->id, 0);

        return response()->json([
            'success' => true,
            'message' => 'Import queued. Progress will update on the button.',
            'sync_run_id' => $run->id,
            'status' => $run->status,
            'progress' => $run->progressLabel(),
            'percent' => $run->progressPercent(),
        ]);
    }

    public function syncRunStatus($id)
    {
        $run = NumberSyncRun::findOrFail($id);

        return response()->json([
            'success' => true,
            'id' => $run->id,
            'status' => $run->status,
            'phase' => $run->phase,
            'progress' => $run->progressLabel(),
            'percent' => $run->progressPercent(),
            'imported' => $run->imported_count,
            'skipped' => $run->skipped_count,
            'error' => $run->error_message,
            'finished' => $run->isFinished(),
        ]);
    }

    public function orders(Request $request)
    {
        $title = 'Number Orders';

        $query = NumberOrder::with(['user', 'numberApi', 'numberCountry'])->orderByDesc('id');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q
                    ->where('phone', 'like', "%{$term}%")
                    ->orWhere('service_name', 'like', "%{$term}%")
                    ->orWhere('service_code', 'like', "%{$term}%")
                    ->orWhere('order_id', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($u) use ($term) {
                        $u
                            ->where('email', 'like', "%{$term}%")
                            ->orWhere('fname', 'like', "%{$term}%")
                            ->orWhere('lname', 'like', "%{$term}%");
                    });
            });
        }

        $numbers = $query->paginate(20)->withQueryString();
        $search = $request->search ?? '';

        return view('backend.numbers.orders', compact('title', 'numbers', 'search'));
    }
}
