<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NumberApi;
use App\Models\NumberCountry;
use App\Models\NumberOrder;
use App\Models\NumberService;
use App\Models\Transaction;
use App\Models\User;
use App\Services\VirtualService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HandlerApiController extends Controller
{
    private const TIMER_MINUTES = 20;

    public function __construct(
        protected VirtualService $virtualService
    ) {}

    protected function findUserByApiKey(?string $apiKey): ?User
    {
        if (!$apiKey) {
            return null;
        }

        return User::where('api_key', $apiKey)->where('status', 1)->first();
    }

    public function getBalance(Request $request)
    {
        $apiKey = $request->query('api_key');

        if (!$apiKey) {
            return response('BAD_KEY');
        }

        $user = $this->findUserByApiKey($apiKey);
        if (!$user) {
            return response('BAD_KEY');
        }

        return response('ACCESS_BALANCE:' . $user->balance);
    }

    public function getNumber(Request $request)
    {
        $apiKey = $request->query('api_key');
        $serviceCode = $request->query('service');
        $countryId = $request->query('country');

        if (!$apiKey) {
            return response('BAD_KEY');
        }
        if (!$serviceCode) {
            return response('BAD_SERVICE');
        }
        if (!$countryId) {
            return response('BAD_COUNTRY');
        }

        $user = $this->findUserByApiKey($apiKey);
        if (!$user) {
            return response('BAD_KEY');
        }

        $country = NumberCountry::where('id', $countryId)->where('status', 1)->first();
        if (!$country) {
            return response('BAD_COUNTRY');
        }

        $service = NumberService::with('numberApi')
            ->where('api_id', $country->api_id)
            ->where('country_id', $country->id)
            ->where('code', $serviceCode)
            ->where('status', 1)
            ->first();

        if (!$service || !$service->numberApi || (int) $service->numberApi->status !== 1) {
            return response('BAD_SERVICE');
        }

        $price = (float) $service->price;
        if ($user->balance < $price) {
            return response('NO_BALANCE');
        }

        $providerCountry = (string) ($country->country_id ?: $country->code);

        $result = $this->virtualService->getNumber(
            $service->numberApi,
            $service->code,
            $providerCountry
        );

        if (!$result['success']) {
            $msg = $result['message'] ?? '';
            if ($msg === 'NO_BALANCE') {
                return response('NO_API_NUMBER');
            }
            return response($msg ?: 'SERVER_ERROR');
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
                'description' => 'API number purchase: ' . $service->name,
            ]);

            NumberOrder::create([
                'user_id' => $user->id,
                'order_id' => $orderId,
                'api_id' => $service->api_id,
                'country_id' => $country->id,
                'service_id' => $service->id,
                'activation_id' => $result['activation_id'],
                'phone' => $result['phone'],
                'service_name' => $service->name,
                'service_code' => $service->code,
                'country_code' => $providerCountry,
                'price' => $price,
                'status' => NumberOrder::STATUS_WAITING_SMS,
                'purchased_at' => $purchasedAt,
                'expires_at' => $purchasedAt->copy()->addMinutes(self::TIMER_MINUTES),
                'refunded' => false,
            ]);

            DB::commit();

            return response('ACCESS_NUMBER:' . $orderId . ':' . $result['phone']);
        } catch (\Throwable $e) {
            DB::rollBack();
            if (!empty($result['activation_id'])) {
                $this->virtualService->setStatus($service->numberApi, $result['activation_id'], $service->numberApi->providerCancelStatus());
            }
            Log::error('API v1 getNumber error', ['ex' => $e->getMessage()]);
            return response('SERVER_ERROR');
        }
    }

    public function getStatus(Request $request)
    {
        $apiKey = $request->query('api_key');
        $orderId = $request->query('id');

        if (!$apiKey) {
            return response('BAD_KEY');
        }
        if (!$orderId) {
            return response('BAD_ID');
        }

        $user = $this->findUserByApiKey($apiKey);
        if (!$user) {
            return response('BAD_KEY');
        }

        $order = NumberOrder::with('numberApi')
            ->where('user_id', $user->id)
            ->where('order_id', $orderId)
            ->first();

        if (!$order) {
            return response('NO_ACTIVATION');
        }

        if (!$order->isWaitingSms()) {
            return response('STATUS_CANCEL');
        }

        $api = $order->numberApi;
        if (!$api) {
            return response('SERVER_ERROR');
        }

        if ($order->expires_at && $order->expires_at->isPast()) {
            $this->expireOrder($order);
            return response('STATUS_CANCEL');
        }

        if ($order->sms_code) {
            return response('STATUS_OK:' . $order->sms_code);
        }

        $poll = $this->virtualService->getStatus($api, $order->activation_id);
        $body = $poll['body'] ?? '';

        if ($body === '' || $body === 'STATUS_WAIT_CODE' || $body === 'STATUS_WAIT_RETRY') {
            return response('STATUS_WAIT_CODE');
        }

        if (in_array($body, ['STATUS_CANCEL', 'NO_ACTIVATION'], true)) {
            $this->refundOrder($order, NumberOrder::STATUS_CANCELLED);
            return response('STATUS_CANCEL');
        }

        if (str_starts_with($body, 'STATUS_OK:')) {
            $sms = substr($body, strlen('STATUS_OK:'));
            if ($order->sms_code !== $sms) {
                $order->update(['sms_code' => $sms]);
                $this->virtualService->setStatus($api, $order->activation_id, 6);
            }
            return response('STATUS_OK:' . $sms);
        }

        return response('TRY_AGAIN');
    }

    public function setStatus(Request $request)
    {
        $apiKey = $request->query('api_key');
        $orderId = $request->query('id');
        $status = (int) $request->query('status');

        if (!$apiKey) {
            return response('BAD_KEY');
        }
        if (!$orderId) {
            return response('BAD_ID');
        }

        $user = $this->findUserByApiKey($apiKey);
        if (!$user) {
            return response('BAD_KEY');
        }

        $order = NumberOrder::with('numberApi')
            ->where('user_id', $user->id)
            ->where('order_id', $orderId)
            ->first();

        if (!$order) {
            return response('NO_ACTIVATION');
        }

        if (!$order->isWaitingSms()) {
            return response('STATUS_CANCEL');
        }

        $api = $order->numberApi;
        if (!$api) {
            return response('SERVER_ERROR');
        }

        if ($status === 8) {
            if (!$order->sms_code) {
                $this->virtualService->setStatus($api, $order->activation_id, $api->providerCancelStatus());
                $this->refundOrder($order, NumberOrder::STATUS_CANCELLED);
            } else {
                $order->update(['status' => NumberOrder::STATUS_COMPLETED]);
            }
            return response('STATUS_CANCEL');
        }

        if ($status === 3) {
            if ($order->expires_at && $order->expires_at->isPast()) {
                if (!$order->sms_code) {
                    $this->virtualService->setStatus($api, $order->activation_id, $api->providerCancelStatus());
                    $this->refundOrder($order, NumberOrder::STATUS_EXPIRED);
                } else {
                    $order->update(['status' => NumberOrder::STATUS_COMPLETED]);
                }
                return response('STATUS_CANCEL');
            }

            $this->virtualService->setStatus($api, $order->activation_id, 3);
            return response('ACCESS_RETRY_GET');
        }

        return response('BAD_STATUS');
    }

    public function getServers(Request $request)
    {
        $apiKey = $request->query('api_key');

        if (!$apiKey) {
            return response('BAD_KEY');
        }

        if (!$this->findUserByApiKey($apiKey)) {
            return response('BAD_KEY');
        }

        $servers = NumberApi::query()
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'status' => 'success',
            'servers' => $servers,
        ]);
    }

    public function getCountries(Request $request)
    {
        $apiKey = $request->query('api_key');
        $apiId = $request->query('server_id');

        if (!$apiKey) {
            return response('BAD_KEY');
        }

        if (!$this->findUserByApiKey($apiKey)) {
            return response('BAD_KEY');
        }

        if (!$apiId) {
            return response('BAD_API');
        }

        $api = NumberApi::where('id', $apiId)->where('status', 1)->first();
        if (!$api) {
            return response('BAD_API');
        }

        $countries = NumberCountry::query()
            ->where('api_id', $api->id)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'country_id']);

        return response()->json([
            'status' => 'success',
            'countries' => $countries,
        ]);
    }

    public function getServices(Request $request)
    {
        $apiKey = $request->query('api_key');
        $apiId = $request->query('server_id');
        $countryId = $request->query('country');

        if (!$apiKey) {
            return response('BAD_KEY');
        }

        if (!$this->findUserByApiKey($apiKey)) {
            return response('BAD_KEY');
        }

        if (!$apiId) {
            return response('BAD_API');
        }

        if (!$countryId) {
            return response('BAD_COUNTRY');
        }

        $api = NumberApi::where('id', $apiId)->where('status', 1)->first();
        if (!$api) {
            return response('BAD_API');
        }

        $country = NumberCountry::query()
            ->where('id', $countryId)
            ->where('api_id', $api->id)
            ->where('status', 1)
            ->first();

        if (!$country) {
            return response('BAD_COUNTRY');
        }

        $services = NumberService::query()
            ->where('api_id', $api->id)
            ->where('country_id', $country->id)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'price']);

        if ($services->isEmpty()) {
            return response('BAD_COUNTRY');
        }

        return response()->json([
            'status' => 'success',
            'services' => $services->map(fn ($s) => [
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->name,
                'price' => number_format((float) $s->price, 2, '.', ''),
            ])->values(),
        ]);
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
                'description' => 'API number refund: ' . $order->service_name,
            ]);
            $order->update(['status' => $status, 'refunded' => true]);
        });
    }

    private function expireOrder(NumberOrder $order): void
    {
        if ($order->sms_code) {
            $order->update(['status' => NumberOrder::STATUS_COMPLETED]);
            return;
        }
        if ($order->numberApi) {
            $this->virtualService->setStatus($order->numberApi, $order->activation_id, $order->numberApi->providerCancelStatus());
        }
        $this->refundOrder($order, NumberOrder::STATUS_EXPIRED);
    }
}
