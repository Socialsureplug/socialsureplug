<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RentingApi;
use App\Models\RentingCountry;
use App\Models\RentingOrder;
use App\Models\RentingService;
use App\Models\RentingServiceOperator;
use App\Models\RentingSetting;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\UserServiceDiscount;
use App\Services\RentalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RentingController extends Controller
{
    public function __construct(
        protected RentalService $rentalService
    ) {}

    private function guard(): void
    {
        abort_unless((int) optional(Setting::first())->buy_rent === 1, 404);
    }

    public function index()
    {
        $this->guard();

        return view('frontend.user.renting.index', [
            'title' => 'Rent Number',
            'apis' => RentingApi::where('status', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'label', 'slug']),
        ]);
    }

    public function orders(Request $request)
    {
        $this->guard();

        RentingOrder::where('user_id', $request->user()->id)
            ->where('status', RentingOrder::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => RentingOrder::STATUS_EXPIRED]);

        $query = RentingOrder::where('user_id', $request->user()->id)->orderByDesc('id');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('service_name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('order_id', 'like', "%{$term}%")
                    ->orWhere('provider', 'like', "%{$term}%");
            });
        }

        $userId = $request->user()->id;

        $orders = $query->paginate(15)->withQueryString();
        $orders->getCollection()->transform(function (RentingOrder $order) use ($userId) {
            if ($order->isActive()) {
                $order->setAttribute('prolong_price', UserServiceDiscount::apply(
                    $userId,
                    UserServiceDiscount::SERVICE_RENT_NUMBER,
                    $this->resolveProlongPrice($order)
                ));
            }

            return $order;
        });

        return view('frontend.user.renting.orders', [
            'title' => 'Renting Log',
            'orders' => $orders,
            'search' => $request->get('search', ''),
        ]);
    }

    public function getCountries(Request $request)
    {
        $this->guard();

        $request->validate([
            'api' => 'required|integer|exists:renting_api,id',
        ]);

        $countries = RentingCountry::where('api_id', $request->api)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'country_id']);

        return response()->json(['countries' => $countries]);
    }

    public function getDurations(Request $request)
    {
        $this->guard();

        $request->validate([
            'api' => 'required|integer|exists:renting_api,id',
            'country' => 'required|integer|exists:renting_countries,id',
        ]);

        $country = RentingCountry::where('id', $request->country)
            ->where('api_id', $request->api)
            ->where('status', 1)
            ->first();

        if (!$country) {
            return response()->json(['durations' => []]);
        }

        $durations = RentingService::where('api_id', $request->api)
            ->where('country_id', $country->id)
            ->where('status', 1)
            ->whereHas('serviceOperators', fn ($q) => $q->where('status', 1)->where('stock', '>', 0))
            ->select('dtype', 'dcount')
            ->distinct()
            ->get()
            ->map(fn ($row) => [
                'dtype' => $row->dtype,
                'dcount' => (int) $row->dcount,
                'label' => $this->formatDurationLabel($row->dtype, (int) $row->dcount),
            ])
            ->sortBy(fn ($item) => $this->durationSortWeight($item['dtype'], $item['dcount']))
            ->values();

        return response()->json(['durations' => $durations]);
    }

    public function getServices(Request $request)
    {
        $this->guard();

        $request->validate([
            'api' => 'required|integer|exists:renting_api,id',
            'country' => 'required|integer|exists:renting_countries,id',
            'dtype' => 'required|string|in:week,month,day',
            'dcount' => 'required|integer|min:1',
        ]);

        $country = RentingCountry::where('id', $request->country)
            ->where('api_id', $request->api)
            ->where('status', 1)
            ->first();

        if (!$country) {
            return response()->json(['services' => []]);
        }

        $services = RentingService::where('api_id', $request->api)
            ->where('country_id', $country->id)
            ->where('dtype', $request->dtype)
            ->where('dcount', (int) $request->dcount)
            ->where('status', 1)
            ->whereHas('serviceOperators', fn ($q) => $q->where('status', 1)->where('stock', '>', 0))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'services' => $services->map(fn ($s) => [
                'id' => $s->id,
                'service_name' => $s->name,
                'service_code' => $s->code,
            ])->values(),
        ]);
    }

    public function getOperators(Request $request)
    {
        $this->guard();

        $request->validate([
            'service_id' => 'required|integer|exists:renting_services,id',
        ]);

        $service = RentingService::where('id', $request->service_id)
            ->where('status', 1)
            ->first();

        if (!$service) {
            return response()->json(['operators' => []]);
        }

        $offers = RentingServiceOperator::with('rentingOperator')
            ->where('service_id', $service->id)
            ->where('status', 1)
            ->where('stock', '>', 0)
            ->orderBy('price')
            ->get();

        $userId = optional($request->user())->id;

        return response()->json([
            'operators' => $offers->map(fn ($offer) => [
                'id' => $offer->id,
                'operator_name' => $offer->rentingOperator?->name ?? '—',
                'price' => UserServiceDiscount::apply($userId, UserServiceDiscount::SERVICE_RENT_NUMBER, (float) $offer->price),
                'stock' => $offer->stock,
            ])->values(),
        ]);
    }

    public function rent(Request $request)
    {
        $this->guard();

        $validated = $request->validate([
            'service_operator_id' => 'required|integer|exists:renting_service_operators,id',
        ]);

        $user = $request->user();

        $offer = RentingServiceOperator::with([
            'rentingOperator',
            'rentingService.rentingCountry',
            'rentingService.rentingApi',
        ])
            ->where('id', $validated['service_operator_id'])
            ->where('status', 1)
            ->first();

        if (!$offer || !$offer->rentingService || !$offer->rentingService->rentingCountry || !$offer->rentingService->rentingApi) {
            return response()->json(['status' => '400', 'message' => 'Invalid selection']);
        }

        $service = $offer->rentingService;

        if ($service->status !== 1 || $offer->stock <= 0) {
            return response()->json(['status' => '400', 'message' => 'Service or operator not available']);
        }

        $price = UserServiceDiscount::apply($user->id, UserServiceDiscount::SERVICE_RENT_NUMBER, (float) $offer->price);

        if ($user->balance < $price) {
            return response()->json(['status' => '400', 'message' => 'Insufficient balance']);
        }

        $api = $service->rentingApi;
        $country = $service->rentingCountry;
        $provider = $offer->rentingOperator?->name;
        $countryCode = (string) ($country->country_id ?: $country->code);

        $result = $this->rentalService->create(
            $api,
            $countryCode,
            $service->code,
            $service->dtype,
            $service->dcount,
            $provider
        );

        if (!$result['success'] || empty($result['provider_order_id'])) {
            return response()->json([
                'status' => '400',
                'message' => $result['message'] ?? 'Could not rent number',
            ]);
        }

        $activate = $this->rentalService->activate($api, $result['provider_order_id']);

        if (!$activate['success']) {
            return response()->json([
                'status' => '400',
                'message' => $activate['message'] ?? 'Rental created but activation failed. Contact support.',
            ]);
        }

        $orderId = Str::random(20);
        $rentedAt = now();
        $expiresAt = $this->parseExpiry($result['until'] ?? null, $service->dtype, $service->dcount);

        DB::beginTransaction();

        try {
            $before = $user->balance;
            $user->decrement('balance', $price);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $price,
                'balance_before' => $before,
                'balance_after' => $before - $price,
                'source' => 'renting_purchase',
                'reference' => $orderId,
                'description' => 'Rent number: ' . $service->name . ($provider ? " ({$provider})" : ''),
            ]);

            $order = RentingOrder::create([
                'user_id' => $user->id,
                'order_id' => $orderId,
                'api_id' => $api->id,
                'country_id' => $country->id,
                'service_id' => $service->id,
                'provider_order_id' => $result['provider_order_id'],
                'phone' => $result['phone'],
                'service_name' => $service->name,
                'service_code' => $service->code,
                'country_code' => $countryCode,
                'dtype' => $service->dtype,
                'dcount' => $service->dcount,
                'provider' => $provider,
                'price' => $price,
                'cost' => round((float) $offer->provider_price * (float) RentingSetting::current()->currency_rate, 4),
                'status' => RentingOrder::STATUS_ACTIVE,
                'activated_at' => $rentedAt,
                'expires_at' => $expiresAt,
                'rented_at' => $rentedAt,
                'refunded' => false,
            ]);

            $offer->decrement('stock');

            DB::commit();

            return response()->json([
                'status' => '200',
                'message' => 'Number rented successfully',
                'data' => $this->formatOrderForCard($order),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Renting purchase failed', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['status' => '500', 'message' => 'Purchase failed. Please try again.']);
        }
    }

    public function activeOrders(Request $request)
    {
        $this->guard();

        $orders = RentingOrder::where('user_id', $request->user()->id)
            ->where('status', RentingOrder::STATUS_ACTIVE)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => '200',
            'data' => $orders->map(fn ($o) => $this->formatOrderForCard($o))->values(),
        ]);
    }

    public function pollSms(Request $request)
    {
        $this->guard();

        $request->validate(['order_id' => 'required|string']);

        $order = RentingOrder::with('rentingApi')
            ->where('user_id', $request->user()->id)
            ->where('order_id', $request->order_id)
            ->first();

        if (!$order || !$order->isActive() || !$order->provider_order_id || !$order->rentingApi) {
            return response()->json(['status' => '500', 'sms' => []]);
        }

        if ($order->expires_at && $order->expires_at->isPast()) {
            $order->update(['status' => RentingOrder::STATUS_EXPIRED]);
            return response()->json(['status' => '500', 'sms' => []]);
        }

        $result = $this->rentalService->getSms($order->rentingApi, $order->provider_order_id);

        if (!$result['success']) {
            return response()->json(['status' => '100', 'sms' => $order->sms_messages ?? []]);
        }

        $messages = [
            'SmsList' => $result['sms_list'] ?? [],
            'OtherSms' => $result['other_sms'] ?? [],
        ];

        $order->update(['sms_messages' => $messages]);

        return response()->json([
            'status' => '200',
            'sms' => $messages,
        ]);
    }

    public function prolong(Request $request)
    {
        $this->guard();

        $validated = $request->validate([
            'order_id' => 'required|string',
        ]);

        $user = $request->user();

        $order = RentingOrder::with('rentingApi')
            ->where('user_id', $user->id)
            ->where('order_id', $validated['order_id'])
            ->first();

        if (!$order || !$order->isActive() || !$order->provider_order_id || !$order->rentingApi) {
            return response()->json(['status' => '400', 'message' => 'Order not found or not active']);
        }

        $prolongOffer = $this->resolveProlongOffer($order);
        $price = UserServiceDiscount::apply(
            $user->id,
            UserServiceDiscount::SERVICE_RENT_NUMBER,
            $prolongOffer['price']
        );

        if ($price <= 0) {
            return response()->json(['status' => '400', 'message' => 'Could not determine prolongation price']);
        }

        if ($user->balance < $price) {
            return response()->json(['status' => '400', 'message' => 'Insufficient balance']);
        }

        $dtype = $order->dtype;
        $dcount = (int) $order->dcount;

        $result = $this->rentalService->prolong(
            $order->rentingApi,
            $order->provider_order_id,
            $dtype,
            $dcount
        );

        if (!$result['success']) {
            return response()->json([
                'status' => '400',
                'message' => $result['message'] ?? 'Prolongation failed',
            ]);
        }

        DB::beginTransaction();

        try {
            $before = $user->balance;
            $user->decrement('balance', $price);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $price,
                'balance_before' => $before,
                'balance_after' => $before - $price,
                'source' => 'renting_prolong',
                'reference' => $order->order_id,
                'description' => 'Prolong rental: ' . $order->service_name . ' (' . $this->formatDurationLabel($dtype, $dcount) . ')',
            ]);

            $expiresAt = $this->parseExpiry(
                is_array($result['data']) ? ($result['data']['until'] ?? null) : null,
                $dtype,
                $dcount
            ) ?? $order->expires_at;

            $prolongCost = $prolongOffer['provider_price'] !== null
                ? round($prolongOffer['provider_price'] * (float) RentingSetting::current()->currency_rate, 4)
                : null;

            $order->update([
                'expires_at' => $expiresAt,
                'price' => (float) $order->price + $price,
                'cost' => ($order->cost !== null && $prolongCost !== null) ? round((float) $order->cost + $prolongCost, 4) : null,
            ]);

            $this->syncOrderFromProvider($order->fresh(['rentingApi']));

            DB::commit();

            return response()->json([
                'status' => '200',
                'message' => 'Rental prolonged successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Renting prolong failed', [
                'user_id' => $user->id,
                'order_id' => $order->order_id,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['status' => '500', 'message' => 'Prolongation failed. Please try again.']);
        }
    }

    public function restorePrecalc(Request $request)
    {
        $this->guard();

        $validated = $request->validate([
            'order_id' => 'required|string',
        ]);

        $order = RentingOrder::with('rentingApi')
            ->where('user_id', $request->user()->id)
            ->where('order_id', $validated['order_id'])
            ->first();

        if (!$order || !$order->provider_order_id || !$order->rentingApi) {
            return response()->json(['status' => '400', 'message' => 'Order not found']);
        }

        if ($order->isActive()) {
            return response()->json(['status' => '400', 'message' => 'This rental is still active']);
        }

        $result = $this->rentalService->restorePrecalc($order->rentingApi, $order->provider_order_id);

        if (!$result['success'] || !is_array($result['data'])) {
            return response()->json([
                'status' => '400',
                'message' => $result['message'] ?? 'Restore is not available for this order',
            ]);
        }

        $data = $result['data'];
        $providerPrice = (float) ($data['price'] ?? 0);
        $retailPrice = UserServiceDiscount::apply(
            $request->user()->id,
            UserServiceDiscount::SERVICE_RENT_NUMBER,
            $this->rentalService->retailPrice($providerPrice)
        );

        return response()->json([
            'status' => '200',
            'data' => [
                'price' => $retailPrice,
                'duration_days' => (int) ($data['prolongTo'] ?? $data['outdays'] ?? 0),
                'preview_phone' => (string) ($data['pnumber'] ?? ''),
                'service_name' => (string) ($data['sname'] ?? $order->service_name),
            ],
        ]);
    }

    public function restore(Request $request)
    {
        $this->guard();

        $validated = $request->validate([
            'order_id' => 'required|string',
        ]);

        $user = $request->user();

        $order = RentingOrder::with('rentingApi')
            ->where('user_id', $user->id)
            ->where('order_id', $validated['order_id'])
            ->first();

        if (!$order || !$order->provider_order_id || !$order->rentingApi) {
            return response()->json(['status' => '400', 'message' => 'Order not found']);
        }

        if ($order->isActive()) {
            return response()->json(['status' => '400', 'message' => 'This rental is still active']);
        }

        $precalc = $this->rentalService->restorePrecalc($order->rentingApi, $order->provider_order_id);

        if (!$precalc['success'] || !is_array($precalc['data'])) {
            return response()->json([
                'status' => '400',
                'message' => $precalc['message'] ?? 'Restore is not available for this order',
            ]);
        }

        $precalcData = $precalc['data'];
        $price = UserServiceDiscount::apply(
            $user->id,
            UserServiceDiscount::SERVICE_RENT_NUMBER,
            $this->rentalService->retailPrice((float) ($precalcData['price'] ?? 0))
        );

        if ($price <= 0) {
            return response()->json(['status' => '400', 'message' => 'Invalid restore price']);
        }

        if ($user->balance < $price) {
            return response()->json(['status' => '400', 'message' => 'Insufficient balance']);
        }

        $restore = $this->rentalService->restore($order->rentingApi, $order->provider_order_id);

        if (!$restore['success']) {
            return response()->json([
                'status' => '400',
                'message' => $restore['message'] ?? 'Restore failed',
            ]);
        }

        DB::beginTransaction();

        try {
            $before = $user->balance;
            $user->decrement('balance', $price);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $price,
                'balance_before' => $before,
                'balance_after' => $before - $price,
                'source' => 'renting_restore',
                'reference' => $order->order_id,
                'description' => 'Restore rental: ' . $order->service_name,
            ]);

            $durationDays = (int) ($precalcData['prolongTo'] ?? $precalcData['outdays'] ?? 0);
            $expiresAt = $durationDays > 0
                ? now()->addDays($durationDays)
                : $this->parseExpiry(null, $order->dtype, (int) $order->dcount);

            $restoreCost = round((float) ($precalcData['price'] ?? 0) * (float) RentingSetting::current()->currency_rate, 4);

            $order->update([
                'phone' => (string) ($precalcData['pnumber'] ?? $order->phone),
                'status' => RentingOrder::STATUS_ACTIVE,
                'expires_at' => $expiresAt,
                'activated_at' => now(),
                'price' => (float) $order->price + $price,
                'cost' => $order->cost !== null ? round((float) $order->cost + $restoreCost, 4) : null,
                'sms_messages' => null,
            ]);

            $this->syncOrderFromProvider($order->fresh(['rentingApi']));

            DB::commit();

            return response()->json([
                'status' => '200',
                'message' => 'Rental restored successfully. You may receive a new phone number.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Renting restore failed', [
                'user_id' => $user->id,
                'order_id' => $order->order_id,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['status' => '500', 'message' => 'Restore failed. Please try again.']);
        }
    }

    private function formatOrderForCard(RentingOrder $order): array
    {
        return [
            'order_id' => $order->order_id,
            'number' => $order->phone,
            'service_name' => $order->service_name,
            'provider' => $order->provider,
            'service_price' => (string) $order->price,
            'dtype' => $order->dtype,
            'dcount' => $order->dcount,
            'rented_at' => $order->rented_at?->toIso8601String(),
            'expires_at' => $order->expires_at?->toIso8601String(),
            'sms' => $order->sms_messages ?? [],
            'status' => $order->status,
        ];
    }

    private function resolveProlongPrice(RentingOrder $order): float
    {
        return $this->resolveProlongOffer($order)['price'];
    }

    /**
     * Same lookup as resolveProlongPrice(), but also returns the offer's
     * provider_price so callers that need to snapshot a cost (prolong())
     * don't have to run the query twice.
     */
    private function resolveProlongOffer(RentingOrder $order): array
    {
        if ($order->service_id) {
            $query = RentingServiceOperator::where('service_id', $order->service_id)
                ->where('status', 1);

            if ($order->provider) {
                $offer = (clone $query)
                    ->whereHas('rentingOperator', fn ($q) => $q->where('name', $order->provider))
                    ->first(['price', 'provider_price']);

                if ($offer !== null) {
                    return ['price' => (float) $offer->price, 'provider_price' => (float) $offer->provider_price];
                }
            }

            $fallback = $query->orderBy('price')->first(['price', 'provider_price']);

            if ($fallback !== null) {
                return ['price' => (float) $fallback->price, 'provider_price' => (float) $fallback->provider_price];
            }
        }

        return ['price' => (float) $order->price, 'provider_price' => null];
    }

    private function syncOrderFromProvider(RentingOrder $order): void
    {
        if (!$order->rentingApi || !$order->provider_order_id) {
            return;
        }

        $result = $this->rentalService->getOrders($order->rentingApi);

        if (!$result['success'] || !is_array($result['orders'])) {
            return;
        }

        foreach ($result['orders'] as $row) {
            if (!is_array($row)) {
                continue;
            }

            $providerId = (int) ($row['id'] ?? $row['orderid'] ?? $row['order_id'] ?? 0);

            if ($providerId !== (int) $order->provider_order_id) {
                continue;
            }

            $updates = [];

            $phone = (string) ($row['pnumber'] ?? $row['number'] ?? $row['phone'] ?? '');
            if ($phone !== '') {
                $updates['phone'] = $phone;
            }

            $until = $row['until'] ?? $row['endDate'] ?? null;
            if ($until) {
                $updates['expires_at'] = $this->parseExpiry($until, $order->dtype, (int) $order->dcount);
            }

            if (isset($row['canprolongmax'])) {
                $updates['can_prolong_max'] = (int) $row['canprolongmax'];
            }

            if (isset($row['canprolonguntil'])) {
                try {
                    $updates['can_prolong_until'] = Carbon::parse($row['canprolonguntil']);
                } catch (\Throwable) {
                }
            }

            if ($updates !== []) {
                $order->update($updates);
            }

            return;
        }
    }

    private function formatDurationLabel(string $dtype, int $dcount): string
    {
        $dcount = max(1, $dcount);

        return match ($dtype) {
            'day' => $dcount . ($dcount === 1 ? ' Day' : ' Days'),
            'week' => $dcount . ($dcount === 1 ? ' Week' : ' Weeks'),
            'month' => $dcount . ($dcount === 1 ? ' Month' : ' Months'),
            default => $dcount . ' ' . $dtype,
        };
    }

    private function durationSortWeight(string $dtype, int $dcount): int
    {
        $dcount = max(1, $dcount);
        $unitDays = match ($dtype) {
            'day' => 1,
            'week' => 7,
            'month' => 30,
            default => 100,
        };

        return $unitDays * $dcount;
    }

    private function parseExpiry(mixed $until, string $dtype, int $dcount): ?Carbon
    {
        if (is_string($until) && $until !== '') {
            try {
                return Carbon::parse($until);
            } catch (\Throwable) {
            }
        }

        $days = match ($dtype) {
            'day' => $dcount,
            'week' => 7 * $dcount,
            'month' => 30 * $dcount,
            default => 7 * $dcount,
        };

        return now()->addDays($days);
    }
}
