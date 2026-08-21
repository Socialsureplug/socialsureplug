<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Jobs\ImportRentingServicesJob;
use App\Models\RentingApi;
use App\Models\RentingCountry;
use App\Models\RentingOperator;
use App\Models\RentingOrder;
use App\Models\RentingService;
use App\Models\RentingServiceOperator;
use App\Models\RentingSetting;
use App\Models\RentingSyncRun;
use App\Services\RentalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentingController extends Controller
{
    public function api(Request $request)
    {
        $data['title'] = 'Renting API';

        $query = RentingApi::query();

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

        return view('backend.rentings.api', $data);
    }

    public function fetchBalances(Request $request, RentalService $rentalService)
    {
        $validated = $request->validate([
            'api_ids' => 'required|array|min:1',
            'api_ids.*' => 'integer|exists:renting_api,id',
        ]);

        $balances = [];

        foreach (RentingApi::whereIn('id', $validated['api_ids'])->orderBy('id')->get() as $api) {
            $result = $rentalService->fetchBalance($api);

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
            'slug' => 'required|string|max:255|unique:renting_api,slug',
            'base_url' => 'required|url|max:500',
            'api_key' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        RentingApi::create($validated);

        return redirect()
            ->route('backend.rentings.api')
            ->with('success', 'API added successfully');
    }

    public function updateApi(Request $request, $id)
    {
        $api = RentingApi::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:renting_api,slug,' . $id,
            'base_url' => 'required|url|max:500',
            'api_key' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        $api->update($validated);

        return redirect()
            ->route('backend.rentings.api')
            ->with('success', 'API updated successfully');
    }

    public function destroyApi($id)
    {
        RentingApi::findOrFail($id)->delete();

        return redirect()
            ->route('backend.rentings.api')
            ->with('success', 'API deleted successfully');
    }

    public function toggleApiStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:renting_api,id',
            'status' => 'required|in:0,1',
        ]);

        RentingApi::where('id', $validated['id'])->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    public function countries(Request $request, $apiId)
    {
        $api = RentingApi::findOrFail($apiId);

        $query = RentingCountry::where('api_id', $apiId);

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

        return view('backend.rentings.countries', $data);
    }

    public function storeCountry(Request $request, $apiId)
    {
        RentingApi::findOrFail($apiId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'country_id' => 'required|string|max:50',
            'status' => 'required|in:0,1',
        ]);

        $validated['api_id'] = $apiId;

        RentingCountry::create($validated);

        return redirect()
            ->route('backend.rentings.api.countries', $apiId)
            ->with('success', 'Country added successfully');
    }

    public function updateCountry(Request $request, $id)
    {
        $country = RentingCountry::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'country_id' => 'required|string|max:50',
            'status' => 'required|in:0,1',
        ]);

        $country->update($validated);

        return redirect()
            ->route('backend.rentings.api.countries', $country->api_id)
            ->with('success', 'Country updated successfully');
    }

    public function destroyCountry($id)
    {
        $country = RentingCountry::findOrFail($id);
        $apiId = $country->api_id;
        $country->delete();

        return redirect()
            ->route('backend.rentings.api.countries', $apiId)
            ->with('success', 'Country deleted successfully');
    }

    public function toggleCountryStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:renting_countries,id',
            'status' => 'required|in:0,1',
        ]);

        RentingCountry::where('id', $validated['id'])->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    public function importCountries($apiId, Request $request, RentalService $rentalService)
    {
        $api = RentingApi::findOrFail($apiId);

        $result = $rentalService->importCountries($api);

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
                ->route('backend.rentings.api.countries', $apiId)
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('backend.rentings.api.countries', $apiId)
            ->with('success', "Updated {$result['imported']} countries. Skipped {$result['skipped']}.");
    }

    public function services(Request $request, $apiId)
    {
        $api = RentingApi::findOrFail($apiId);

        $query = RentingService::with('rentingCountry')
            ->withCount('serviceOperators')
            ->where('api_id', $apiId);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhereHas('rentingCountry', function ($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%")
                            ->orWhere('code', 'like', "%{$term}%");
                    })
                    ->orWhereHas('serviceOperators.rentingOperator', function ($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%");
                    });
            });
        }

        $data['title'] = 'Services — ' . $api->name;
        $data['api'] = $api;
        $data['countries'] = RentingCountry::where('api_id', $apiId)->orderBy('name')->get();
        $data['services'] = $query->orderBy('name')->paginate(10)->withQueryString();
        $data['search'] = $request->search ?? '';
        $data['activeSyncRun'] = RentingSyncRun::where('api_id', $apiId)
            ->where('type', RentingSyncRun::TYPE_SERVICES)
            ->whereIn('status', [RentingSyncRun::STATUS_QUEUED, RentingSyncRun::STATUS_RUNNING])
            ->latest('id')
            ->first();

        return view('backend.rentings.services', $data);
    }

    public function storeService(Request $request, $apiId)
    {
        RentingApi::findOrFail($apiId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'country_id' => 'required|exists:renting_countries,id',
            'dtype' => 'required|string|in:week,month,day',
            'dcount' => 'required|integer|min:1',
            'status' => 'required|in:0,1',
        ]);

        RentingCountry::where('id', $validated['country_id'])->where('api_id', $apiId)->firstOrFail();

        RentingService::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'country_id' => $validated['country_id'],
            'api_id' => $apiId,
            'dtype' => $validated['dtype'],
            'dcount' => $validated['dcount'],
            'status' => (int) $validated['status'],
        ]);

        return redirect()
            ->route('backend.rentings.api.services', $apiId)
            ->with('success', 'Service added successfully');
    }

    public function updateService(Request $request, $id)
    {
        $service = RentingService::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'country_id' => 'required|exists:renting_countries,id',
            'dtype' => 'required|string|in:week,month,day',
            'dcount' => 'required|integer|min:1',
            'status' => 'required|in:0,1',
        ]);

        RentingCountry::where('id', $validated['country_id'])->where('api_id', $service->api_id)->firstOrFail();

        $service->update($validated);

        return redirect()
            ->route('backend.rentings.api.services', $service->api_id)
            ->with('success', 'Service updated successfully');
    }

    public function destroyService($id)
    {
        $service = RentingService::findOrFail($id);
        $apiId = $service->api_id;
        $service->delete();

        return redirect()
            ->route('backend.rentings.api.services', $apiId)
            ->with('success', 'Service deleted successfully');
    }

    public function operators(Request $request, $apiId, $countryId)
    {
        $api = RentingApi::findOrFail($apiId);
        $country = RentingCountry::where('id', $countryId)->where('api_id', $apiId)->firstOrFail();

        $query = RentingOperator::withCount('serviceOperators')
            ->where('api_id', $apiId)
            ->where('country_id', $country->id);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where('name', 'like', "%{$term}%");
        }

        $data['title'] = 'Operators — ' . $country->name;
        $data['api'] = $api;
        $data['country'] = $country;
        $data['operators'] = $query->orderBy('name')->paginate(10)->withQueryString();
        $data['search'] = $request->search ?? '';

        return view('backend.rentings.operators', $data);
    }

    public function updateOperator(Request $request, $id)
    {
        $operator = RentingOperator::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        $operator->update($validated);

        return redirect()
            ->route('backend.rentings.api.countries.operators', [$operator->api_id, $operator->country_id])
            ->with('success', 'Operator updated successfully');
    }

    public function destroyOperator($id)
    {
        $operator = RentingOperator::findOrFail($id);
        $apiId = $operator->api_id;
        $countryId = $operator->country_id;
        $operator->delete();

        return redirect()
            ->route('backend.rentings.api.countries.operators', [$apiId, $countryId])
            ->with('success', 'Operator deleted successfully');
    }

    public function toggleOperatorStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:renting_operators,id',
            'status' => 'required|in:0,1',
        ]);

        RentingOperator::where('id', $validated['id'])->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    public function serviceOperators(Request $request, $serviceId)
    {
        $service = RentingService::with(['rentingCountry', 'rentingApi'])->findOrFail($serviceId);

        $query = RentingServiceOperator::with('rentingOperator')
            ->where('service_id', $service->id);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('rentingOperator', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%");
            });
        }

        $data['title'] = 'Service Operators — ' . $service->name;
        $data['service'] = $service;
        $data['api'] = $service->rentingApi;
        $data['offers'] = $query->orderBy('id')->paginate(10)->withQueryString();
        $data['search'] = $request->search ?? '';

        return view('backend.rentings.service-operators', $data);
    }

    public function updateServiceOperator(Request $request, $id)
    {
        $offer = RentingServiceOperator::with('rentingService')->findOrFail($id);

        $validated = $request->validate([
            'provider_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        $offer->update([
            'provider_price' => $validated['provider_price'],
            'price' => $validated['price'],
            'stock' => $validated['stock'] ?? $offer->stock,
            'status' => (int) $validated['status'],
        ]);

        return redirect()
            ->route('backend.rentings.services.service-operators', $offer->service_id)
            ->with('success', 'Operator pricing updated successfully');
    }

    public function destroyServiceOperator($id)
    {
        $offer = RentingServiceOperator::findOrFail($id);
        $serviceId = $offer->service_id;
        $offer->delete();

        return redirect()
            ->route('backend.rentings.services.service-operators', $serviceId)
            ->with('success', 'Operator removed from service');
    }

    public function toggleServiceStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:renting_services,id',
            'status' => 'required|in:0,1',
        ]);

        RentingService::where('id', $validated['id'])->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    public function toggleServiceOperatorStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:renting_service_operators,id',
            'status' => 'required|in:0,1',
        ]);

        RentingServiceOperator::where('id', $validated['id'])->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Operator status updated successfully',
        ]);
    }

    public function settings()
    {
        $data['title'] = 'Renting settings';
        $data['rentingSettings'] = RentingSetting::current();

        return view('backend.rentings.settings', $data);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'currency_rate' => 'required|numeric|min:0',
            'price_increase' => 'required|numeric|min:0',
        ]);

        RentingSetting::current()->update($validated);

        return redirect()
            ->route('backend.rentings.settings')
            ->with('success', 'Renting settings updated successfully');
    }

    public function importServices($apiId, Request $request)
    {
        $api = RentingApi::findOrFail($apiId);

        $alreadyRunning = RentingSyncRun::where('api_id', $api->id)
            ->where('type', RentingSyncRun::TYPE_SERVICES)
            ->whereIn('status', [RentingSyncRun::STATUS_QUEUED, RentingSyncRun::STATUS_RUNNING])
            ->exists();

        if ($alreadyRunning) {
            return response()->json([
                'success' => false,
                'message' => 'A service import is already running for this API.',
            ], 409);
        }

        $request->validate([
            'country_id' => 'nullable|integer|exists:renting_countries,id',
            'dtype' => 'nullable|string|in:week,month,day',
            'dcount' => 'nullable|integer|min:1',
        ]);

        $countryId = $request->input('country_id');

        $run = RentingSyncRun::create([
            'api_id' => $api->id,
            'country_id' => $countryId ? (int) $countryId : null,
            'type' => RentingSyncRun::TYPE_SERVICES,
            'status' => RentingSyncRun::STATUS_QUEUED,
            'admin_id' => Auth::guard('admin')->id(),
        ]);

        ImportRentingServicesJob::dispatch(
            $run->id,
            0,
            $request->input('dtype', 'week'),
            (int) $request->input('dcount', 1)
        );

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
        $run = RentingSyncRun::findOrFail($id);

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
        $title = 'Renting Orders';

        $query = RentingOrder::with(['user', 'rentingApi', 'rentingCountry'])->orderByDesc('id');

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

        $orders = $query->paginate(20)->withQueryString();
        $search = $request->search ?? '';

        return view('backend.rentings.orders', compact('title', 'orders', 'search'));
    }
}
