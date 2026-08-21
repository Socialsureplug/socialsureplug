<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SmmOrder;
use App\Models\SmmService;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmmApiV1Controller extends Controller
{
    protected function findUserByKey(?string $key): ?User
    {
        if (!$key) {
            return null;
        }
        return User::where('api_key', $key)->where('status', 1)->first();
    }

    /**
     * Single entry: key + action (services, add, status, balance). Supports GET and POST.
     */
    public function handle(Request $request): JsonResponse
    {
        $key = $request->input('key') ?? $request->query('key');
        $action = $request->input('action') ?? $request->query('action');

        if (!$key) {
            return response()->json(['error' => 'API key is required. Get it from your profile.']);
        }

        $user = $this->findUserByKey($key);
        if (!$user) {
            return response()->json(['error' => 'Invalid API key or account disabled.']);
        }

        if (!$action) {
            return response()->json(['error' => 'Action is required (services, add, status, balance).']);
        }

        return match ($action) {
            'services' => $this->services($user),
            'add' => $this->add($user, $request),
            'status' => $this->status($user, $request),
            'balance' => $this->balance($user),
            default => response()->json(['error' => 'Invalid action.']),
        };
    }

    protected function services(User $user): JsonResponse
    {
        $list = SmmService::where('status', true)
            ->with('smmCategory')
            ->orderBy('smm_category_id')
            ->orderBy('name')
            ->get()
            ->map(fn ($s) => [
                'service' => (string) $s->id,
                'name' => $s->name,
                'category' => $s->smmCategory?->name ?? '',
                'rate' => (float) $s->price,
                'min' => (int) $s->min,
                'max' => (int) $s->max,
                'type' => $s->type ?? 'default',
                'dripfeed' => (int) $s->dripfeed,
            ]);

        return response()->json($list);
    }

    protected function add(User $user, Request $request): JsonResponse
    {
        $serviceId = $request->input('service') ?? $request->query('service');
        $link = $request->input('link') ?? $request->query('link');
        $quantity = $request->input('quantity') ?? $request->query('quantity');

        if (!$serviceId) {
            return response()->json(['error' => 'Parameter "service" (service ID) is required.']);
        }

        $service = SmmService::where('id', $serviceId)->where('status', true)->first();
        if (!$service) {
            return response()->json(['error' => 'Service not found.']);
        }

        if (!trim((string) $link)) {
            return response()->json(['error' => 'Parameter "link" is required.']);
        }

        $quantity = (int) $quantity;
        if ($quantity < $service->min || $quantity > $service->max) {
            return response()->json(['error' => "Quantity must be between {$service->min} and {$service->max}."]);
        }

        $charge = ($service->price * $quantity) / 1000;
        if ($user->balance < $charge) {
            return response()->json(['error' => 'Insufficient balance.']);
        }

        $balanceBefore = (float) $user->balance;
        $balanceAfter = $balanceBefore - $charge;

        $order = SmmOrder::create([
            'user_id' => $user->id,
            'smm_category_id' => $service->smm_category_id,
            'smm_service_id' => $service->id,
            'link' => $link,
            'quantity' => $quantity,
            'charge' => $charge,
            'status' => 'awaiting',
            'api_provider_id' => $service->api_provider_id,
            'api_service_id' => $service->api_service_id,
            'service_type' => $service->type,
            'mode' => $service->add_type === 'api',
        ]);

        $user->update(['balance' => $balanceAfter]);
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => $charge,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'source' => 'smm_order',
            'reference' => (string) $order->id,
            'description' => 'SMM order #' . $order->id,
        ]);

        return response()->json(['status' => 'success', 'order' => $order->id]);
    }

    protected function status(User $user, Request $request): JsonResponse
    {
        $orderId = $request->input('order') ?? $request->query('order');
        $orderIds = $request->input('orders') ?? $request->query('orders');

        if ($orderIds !== null && $orderIds !== '') {
            $ids = is_array($orderIds) ? $orderIds : array_map('trim', explode(',', (string) $orderIds));
            $result = [];
            foreach ($ids as $id) {
                $order = SmmOrder::where('id', $id)->where('user_id', $user->id)->first();
                if (!$order) {
                    $result[$id] = 'Incorrect order ID';
                } else {
                    $result[$id] = [
                        'order' => $order->id,
                        'status' => $this->formatStatus($order->status),
                        'charge' => (float) $order->charge,
                        'start_count' => (int) $order->start_counter,
                        'remains' => (int) $order->remains,
                    ];
                }
            }
            return response()->json($result);
        }

        if (!$orderId) {
            return response()->json(['error' => 'Parameter "order" or "orders" is required.']);
        }

        $order = SmmOrder::where('id', $orderId)->where('user_id', $user->id)->first();
        if (!$order) {
            return response()->json(['error' => 'Incorrect order ID.']);
        }

        return response()->json([
            'order' => $order->id,
            'status' => $this->formatStatus($order->status),
            'charge' => (float) $order->charge,
            'start_count' => (int) $order->start_counter,
            'remains' => (int) $order->remains,
        ]);
    }

    protected function balance(User $user): JsonResponse
    {
        $currency = optional(Setting::first())->website_currency ?? 'NGN';
        return response()->json([
            'status' => 'success',
            'balance' => (string) number_format((float) $user->balance, 2, '.', ''),
            'currency' => $currency,
        ]);
    }

    private function formatStatus(string $status): string
    {
        return match ($status) {
            'completed' => 'Completed',
            'partial' => 'Partial',
            'canceled' => 'Canceled',
            'inprogress', 'awaiting' => 'In progress',
            'error', 'fail' => 'Error',
            default => 'Pending',
        };
    }
}
