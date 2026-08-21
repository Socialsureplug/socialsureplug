<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SmmCategory;
use App\Models\SmmOrder;
use App\Models\SmmService;
use App\Models\SmmSubcategory;
use App\Models\Transaction;
use App\Models\Setting;
use App\Models\UserServiceDiscount;
use App\Services\SmmApiService;
use Illuminate\Http\Request;

class SmmOrderController extends Controller
{
    public function __construct(
        protected SmmApiService $smmApi
    ) {}

    public function newOrder(Request $request)
    {
        $title = 'Boost Socials';
        $categories = SmmCategory::orderBy('sort')->get();
        $subcategories = SmmSubcategory::orderBy('sort')->get();
        $services = SmmService::where('status', true)
            ->whereNotNull('smm_subcategory_id')
            ->with(['smmCategory', 'smmSubcategory'])
            ->get();
        $userId = optional($request->user())->id;
        $subcategoriesData = $subcategories->map(fn ($sub) => [
            'id' => $sub->id,
            'smm_category_id' => $sub->smm_category_id,
            'name' => $sub->name,
        ])->values()->toArray();
        $servicesData = $services->map(fn ($s) => [
            'id' => $s->id,
            'smm_category_id' => $s->smm_category_id,
            'smm_subcategory_id' => $s->smm_subcategory_id,
            'name' => $s->name,
            'min' => $s->min,
            'max' => $s->max,
            'average_time_minutes' => $s->average_time_minutes,
            'price' => UserServiceDiscount::apply($userId, UserServiceDiscount::SERVICE_SMM, (float) $s->price),
        ])->values()->toArray();
        $currency = optional(Setting::first())->website_currency ?? 'NGN';

        return view('frontend.user.smm-order.add', compact(
            'title',
            'categories',
            'subcategories',
            'services',
            'currency',
            'servicesData',
            'subcategoriesData'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:smm_services,id',
            'link' => 'required|string|max:2000',
            'quantity' => 'required|integer|min:1',
            'agree' => 'accepted',
        ]);

        $service = SmmService::findOrFail($request->service_id);
        $user = $request->user();
        $quantity = (int) $request->quantity;
        $min = $service->min;
        $max = $service->max;
        if ($quantity < $min || $quantity > $max) {
            return response()->json(['status' => 'error', 'message' => "Quantity must be between {$min} and {$max}"], 422);
        }

        $price = UserServiceDiscount::apply($user->id, UserServiceDiscount::SERVICE_SMM, (float) $service->price);
        $charge = ($price * $quantity) / 1000;
        if ($user->balance < $charge) {
            return response()->json(['status' => 'error', 'message' => 'Insufficient balance'], 422);
        }

        $providerCurrencyRate = (float) (optional($service->smmProvider)->currency_rate ?? 1);
        $cost = $service->original_price !== null
            ? round(((float) $service->original_price * $providerCurrencyRate * $quantity) / 1000, 4)
            : null;

        $order = SmmOrder::create([
            'user_id' => $user->id,
            'smm_category_id' => $service->smm_category_id,
            'smm_service_id' => $service->id,
            'link' => $request->link,
            'quantity' => $quantity,
            'charge' => $charge,
            'cost' => $cost,
            'status' => 'awaiting',
            'api_provider_id' => $service->api_provider_id,
            'api_service_id' => $service->api_service_id,
            'service_type' => $service->type,
            'mode' => $service->add_type === 'api',
        ]);

        $newBalance = $user->balance - $charge;
        $user->update(['balance' => $newBalance]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => $charge,
            'balance_before' => $user->balance,
            'balance_after' => $newBalance,
            'source' => 'smm_order',
            'reference' => (string) $order->id,
            'description' => 'SMM order #' . $order->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Order placed.',
            'order_id' => $order->id,
            'balance' => $newBalance,
        ]);
    }

    public function massOrder(Request $request)
    {
        $request->validate([
            'mass_order' => 'required|string',
            'agree' => 'accepted',
        ]);
        $user = $request->user();
        $lines = array_filter(array_map('trim', explode("\n", $request->mass_order)));
        $orders = [];
        $totalCharge = 0;
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) < 3) continue;
            [$serviceId, $quantity, $link] = array_map('trim', $parts);
            $service = SmmService::find($serviceId);
            if (!$service || !$service->status) continue;
            $qty = (int) $quantity;
            if ($qty < $service->min || $qty > $service->max) continue;
            $price = UserServiceDiscount::apply($user->id, UserServiceDiscount::SERVICE_SMM, (float) $service->price);
            $charge = ($price * $qty) / 1000;
            $totalCharge += $charge;
            $providerCurrencyRate = (float) (optional($service->smmProvider)->currency_rate ?? 1);
            $cost = $service->original_price !== null
                ? round(((float) $service->original_price * $providerCurrencyRate * $qty) / 1000, 4)
                : null;
            $orders[] = [
                'user_id' => $user->id,
                'smm_category_id' => $service->smm_category_id,
                'smm_service_id' => $service->id,
                'link' => $link,
                'quantity' => $qty,
                'charge' => $charge,
                'cost' => $cost,
                'status' => 'awaiting',
                'api_provider_id' => $service->api_provider_id,
                'api_service_id' => $service->api_service_id,
                'service_type' => $service->type,
                'mode' => $service->add_type === 'api',
            ];
        }
        if ($totalCharge > $user->balance) {
            return response()->json(['status' => 'error', 'message' => 'Insufficient balance for total charge'], 422);
        }
        foreach ($orders as $data) {
            SmmOrder::create($data);
        }
        $user->update(['balance' => $user->balance - $totalCharge]);
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => $totalCharge,
            'balance_before' => $user->balance,
            'balance_after' => $user->balance - $totalCharge,
            'source' => 'smm_mass_order',
            'reference' => 'mass',
            'description' => 'SMM mass order',
        ]);
        return response()->json(['status' => 'success', 'message' => 'Orders placed.']);
    }
}