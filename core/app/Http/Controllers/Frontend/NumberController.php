<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\NumberApi;
use App\Models\NumberCountry;
use App\Models\NumberOrder;
use App\Models\NumberService;
use App\Models\NumberSetting;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\UserServiceDiscount;
use App\Services\VirtualService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NumberController extends Controller
{
    private const TIMER_MINUTES = 20;

    public function __construct(
        protected VirtualService $virtualService
    ) {}

    private function guard(): void
    {
        abort_unless((int) Setting::first()->buy_number === 1, 404);
    }

    public function index()
    {
        $this->guard();

        $data['title'] = 'Buy Number';

        $data['apis'] = NumberApi::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'label', 'slug']);

        return view('frontend.user.number.index', $data);
    }

    public function orders(Request $request)
    {
        $this->guard();

        $query = NumberOrder::where('user_id', $request->user()->id)->orderByDesc('id');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('service_name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('sms_code', 'like', "%{$term}%")
                    ->orWhere('order_id', 'like', "%{$term}%");
            });
        }

        return view('frontend.user.number.orders', [
            'title' => 'Number Log',
            'numbers' => $query->paginate(15)->withQueryString(),
            'search' => $request->get('search', ''),
        ]);
    }

    public function getCountries(Request $request)
    {
        $this->guard();

        $request->validate([
            'api' => 'required|integer|exists:number_api,id',
        ]);

        $countries = NumberCountry::where('api_id', $request->api)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'country_id']);

        return response()->json(['countries' => $countries]);
    }

    public function getServices(Request $request)
    {
        $this->guard();

        $request->validate([
            'api' => 'required|integer|exists:number_api,id',
            'country' => 'required|integer|exists:number_countries,id',
        ]);

        $country = NumberCountry::where('id', $request->country)
            ->where('api_id', $request->api)
            ->where('status', 1)
            ->first();

        if (!$country) {
            return response()->json(['service' => []]);
        }

        $services = NumberService::where('api_id', $request->api)
            ->where('country_id', $country->id)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'price']);

        $userId = optional($request->user())->id;

        return response()->json([
            'service' => $services->map(fn ($s) => [
                'id' => $s->id,
                'service_name' => $s->name,
                'service_code' => $s->code,
                'service_price' => UserServiceDiscount::apply($userId, UserServiceDiscount::SERVICE_VIRTUAL_NUMBER, (float) $s->price),
            ])->values(),
        ]);
    }

    public function buy(Request $request)
    {
        $this->guard();

        $validated = $request->validate([
            'api_id' => 'required|integer|exists:number_api,id',
            'country_id' => 'required|integer|exists:number_countries,id',
            'service_id' => 'required|integer|exists:number_services,id',
        ]);

        $user = $request->user();

        $service = NumberService::with(['numberCountry', 'numberApi'])
            ->where('id', $validated['service_id'])
            ->where('api_id', $validated['api_id'])
            ->where('country_id', $validated['country_id'])
            ->where('status', 1)
            ->first();

        if (!$service || !$service->numberApi || !$service->numberCountry) {
            return response()->json(['status' => '400', 'message' => 'Invalid service selection']);
        }

        $price = UserServiceDiscount::apply($user->id, UserServiceDiscount::SERVICE_VIRTUAL_NUMBER, (float) $service->price);
        if ($user->balance < $price) {
            return response()->json(['status' => '400', 'message' => 'Insufficient balance']);
        }

        $providerCountry = (string) ($service->numberCountry->country_id ?: $service->numberCountry->code);

        $result = $this->virtualService->getNumber(
            $service->numberApi,
            $service->code,
            $providerCountry
        );

        if (!$result['success']) {
            $msg = match ($result['message'] ?? '') {
                'NO_NUMBERS' => 'No numbers available. Try another service or country.',
                'NO_BALANCE' => 'Provider has no balance. Contact support.',
                default => $result['message'] ?: 'Could not buy number',
            };
            return response()->json(['status' => '400', 'message' => $msg]);
        }

        $orderId = Str::random(20);
        $purchasedAt = now();

        DB::beginTransaction();
        try {
            $before = $user->balance;
            $user->decrement('balance', $price);
            $user->increment('total_otp', 1);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $price,
                'balance_before' => $before,
                'balance_after' => $before - $price,
                'source' => 'number_purchase',
                'reference' => $orderId,
                'description' => 'Number purchase: ' . $service->name,
            ]);

            $order = NumberOrder::create([
                'user_id' => $user->id,
                'order_id' => $orderId,
                'api_id' => $service->api_id,
                'country_id' => $service->country_id,
                'service_id' => $service->id,
                'activation_id' => $result['activation_id'],
                'phone' => $result['phone'],
                'service_name' => $service->name,
                'service_code' => $service->code,
                'country_code' => $providerCountry,
                'price' => $price,
                'cost' => round((float) $service->provider_price * (float) NumberSetting::current()->currency_rate, 4),
                'status' => NumberOrder::STATUS_WAITING_SMS,
                'purchased_at' => $purchasedAt,
                'expires_at' => $purchasedAt->copy()->addMinutes(self::TIMER_MINUTES),
                'refunded' => false,
            ]);

            DB::commit();

            try {
                sendUserEmail('Number Purchased', [
                    'number' => $order->phone,
                    'service_name' => $order->service_name,
                    'amount' => (string) $order->price,
                ], $user);
            } catch (\Throwable $e) {
                Log::error('Number purchased user email failed: ' . $e->getMessage());
            }

            $admin = Admin::first();
            if ($admin) {
                try {
                    sendAdminEmail('user_number_purchase', [
                        'user_email' => $user->email,
                        'number' => $order->phone,
                        'service_name' => $order->service_name,
                        'amount' => (string) $order->price,
                    ], $admin);
                } catch (\Throwable $e) {
                    Log::error('Number purchased admin email failed: ' . $e->getMessage());
                }
            }

            return response()->json([
                'status' => '200',
                'message' => 'Number purchased successfully',
                'data' => $this->formatOrderForCard($order),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            if (!empty($result['activation_id'])) {
                $this->virtualService->setStatus(
                    $service->numberApi,
                    $result['activation_id'],
                    $service->numberApi->providerCancelStatus()
                );
            }

            Log::error('Number buy failed', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['status' => '500', 'message' => 'Purchase failed. Please try again.']);
        }
    }

    public function activeOrders(Request $request)
    {
        $this->guard();

        $orders = NumberOrder::where('user_id', $request->user()->id)
            ->where('status', NumberOrder::STATUS_WAITING_SMS)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => '200',
            'data' => $orders->map(fn ($o) => $this->formatOrderForCard($o))->values(),
        ]);
    }

    public function pollMessage(Request $request)
    {
        $this->guard();

        $request->validate(['order_id' => 'required|string']);

        $order = NumberOrder::with('numberApi')
            ->where('user_id', $request->user()->id)
            ->where('order_id', $request->order_id)
            ->first();

        if (!$order || !$order->isWaitingSms()) {
            return response()->json(['status' => '500', 'sms' => '']);
        }

        if ($order->expires_at && $order->expires_at->isPast()) {
            $this->expireOrder($order);
            return response()->json(['status' => '500', 'sms' => '']);
        }

        $api = $order->numberApi;

        if (!$api) {
            return response()->json(['status' => '500', 'sms' => '']);
        }

        if ($order->sms_code) {
            return response()->json(['status' => '200', 'sms' => $order->sms_code]);
        }

        $poll = $this->virtualService->getStatus($api, $order->activation_id);
        $body = $poll['body'] ?? '';

        if (str_starts_with($body, 'STATUS_OK:')) {
            $code = substr($body, strlen('STATUS_OK:'));
            $order->update(['sms_code' => $code]);
            $this->virtualService->setStatus($api, $order->activation_id, 6);

            try {
                sendUserEmail('Number SMS Received', [
                    'number' => $order->phone,
                    'service_name' => $order->service_name,
                    'code' => $code,
                    'message' => $code,
                ], $request->user());
            } catch (\Throwable $e) {
                Log::error('Number SMS received user email failed: ' . $e->getMessage());
            }

            return response()->json(['status' => '200', 'sms' => $code]);
        }

        if (in_array($body, ['STATUS_CANCEL', 'NO_ACTIVATION'], true)) {
            $this->refundOrder($order, NumberOrder::STATUS_CANCELLED);
            return response()->json(['status' => '500', 'sms' => '']);
        }

        return response()->json(['status' => '100', 'sms' => '']);
    }

    public function cancel(Request $request)
    {
        $this->guard();

        $request->validate(['order_id' => 'required|string']);

        $order = NumberOrder::with('numberApi')
            ->where('user_id', $request->user()->id)
            ->where('order_id', $request->order_id)
            ->first();

        if (!$order || !$order->isWaitingSms()) {
            return response()->json(['status' => '400', 'message' => 'Order not found or already closed']);
        }

        if ($order->sms_code) {
            return response()->json(['status' => '400', 'message' => 'Cannot cancel after SMS is received']);
        }

        if ($order->numberApi) {
            $this->virtualService->setStatus(
                $order->numberApi,
                $order->activation_id,
                $order->numberApi->providerCancelStatus()
            );
        }

        $this->refundOrder($order, NumberOrder::STATUS_CANCELLED);

        return response()->json(['status' => '200', 'message' => 'Order cancelled and refunded']);
    }

    public function dismiss(Request $request)
    {
        $this->guard();

        $request->validate(['order_id' => 'required|string']);

        $order = NumberOrder::where('user_id', $request->user()->id)
            ->where('order_id', $request->order_id)
            ->first();

        if (!$order) {
            return response()->json(['status' => '400', 'message' => 'Order not found']);
        }

        if (!$order->sms_code) {
            return response()->json(['status' => '400', 'message' => 'Wait for SMS code first']);
        }

        $order->update(['status' => NumberOrder::STATUS_COMPLETED]);

        return response()->json(['status' => '200', 'message' => 'Order completed']);
    }

    private function formatOrderForCard(NumberOrder $order): array
    {
        return [
            'order_id' => $order->order_id,
            'number' => $order->phone,
            'service_name' => $order->service_name,
            'service_price' => (string) $order->price,
            'buy_time' => $order->purchased_at?->toIso8601String(),
            'expires_at' => $order->expires_at?->toIso8601String(),
            'sms_text' => $order->sms_code ?? '',
        ];
    }

    private function refundOrder(NumberOrder $order, string $status): void
    {
        if ($order->refunded) {
            $order->update(['status' => $status]);
            return;
        }

        DB::transaction(function () use ($order, $status) {
            $order->loadMissing('user');
            $user = $order->user;
            $before = $user->balance;
            $amount = (float) $order->price;
            $user->increment('balance', $amount);
            $user->decrement('total_otp', 1);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $before + $amount,
                'source' => 'number_refund',
                'reference' => $order->order_id,
                'description' => 'Number refund: ' . $order->service_name,
            ]);

            $order->update(['status' => $status, 'refunded' => true]);
        });

        try {
            $order->loadMissing('user');
            if ($order->user) {
                sendUserEmail('Number Refunded', [
                    'number' => $order->phone,
                    'service_name' => $order->service_name,
                    'amount' => number_format((float) $order->price, 2),
                    'currency' => optional(Setting::first())->website_currency ?? 'USD',
                ], $order->user);
            }
        } catch (\Throwable $e) {
            Log::error('Number refund user email failed: ' . $e->getMessage());
        }
    }

    private function expireOrder(NumberOrder $order): void
    {
        if ($order->sms_code) {
            $order->update(['status' => NumberOrder::STATUS_COMPLETED]);
            return;
        }

        if ($order->numberApi) {
            $this->virtualService->setStatus(
                $order->numberApi,
                $order->activation_id,
                $order->numberApi->providerCancelStatus()
            );
        }

        $this->refundOrder($order, NumberOrder::STATUS_EXPIRED);
    }
}
