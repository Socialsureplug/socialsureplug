<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SellingAccount;
use App\Models\SellingCategory;
use App\Models\SellingOrder;
use App\Models\SellingProduct;
use App\Models\SellingSubcategory;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserServiceDiscount;
use App\Services\Selling\AccszoneService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SellingController extends Controller
{
    public function index(Request $request)
    {
        $query = SellingProduct::query()
            ->where('status', 1)
            ->with(['sellingSubcategory.sellingCategory', 'sellingApi']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', '%'.$term.'%')
                    ->orWhereHas('sellingSubcategory', fn ($sq) => $sq->where('name', 'like', '%'.$term.'%'));
            });
        }

        if ($request->filled('subcategory_id')) {
            $sid = (int) $request->input('subcategory_id');
            if ($sid > 0) {
                $query->where('subcategory_id', $sid);
            }
        } elseif ($request->filled('category_id')) {
            $cid = (int) $request->input('category_id');
            if ($cid > 0) {
                $query->whereHas('sellingSubcategory', fn ($sq) => $sq->where('selling_category_id', $cid));
            }
        }

        if ($request->price_sort === 'asc') {
            $query->orderBy('price', 'asc');
        } elseif ($request->price_sort === 'desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->orderByDesc('id');
        }

        $data['title'] = 'All Products';
        $data['products'] = $query->paginate(12)->withQueryString();
        $userId = optional($request->user())->id;
        $data['products']->getCollection()->transform(function (SellingProduct $product) use ($userId) {
            $product->price = UserServiceDiscount::apply($userId, UserServiceDiscount::SERVICE_ACCOUNT_SELLING, (float) $product->price);

            return $product;
        });
        $data['search'] = $request->search ?? '';
        $data['currency'] = optional(Setting::first())->website_currency ?? 'NGN';
        $data['categories'] = SellingCategory::query()->orderBy('sort')->orderBy('id')->get();
        $data['category_id'] = $request->input('category_id');
        $data['subcategory_id'] = $request->input('subcategory_id');
        $data['subcategoriesForJs'] = SellingSubcategory::query()
            ->orderBy('selling_category_id')
            ->orderBy('sort')
            ->get()
            ->map(fn (SellingSubcategory $sub) => [
                'id' => $sub->id,
                'categoryId' => (int) $sub->selling_category_id,
                'label' => $sub->name,
            ])
            ->values();

        return view('frontend.user.selling.index', $data);
    }

    public function show(Request $request, int $id)
    {
        $product = SellingProduct::query()
            ->where('status', 1)
            ->with(['sellingSubcategory.sellingCategory', 'sellingApi'])
            ->findOrFail($id);

        $product->price = UserServiceDiscount::apply(
            optional($request->user())->id,
            UserServiceDiscount::SERVICE_ACCOUNT_SELLING,
            (float) $product->price
        );

        $relatedQuery = SellingProduct::query()
            ->where('status', 1)
            ->where('id', '!=', $product->id);

        if ($product->subcategory_id !== null) {
            $relatedQuery->where('subcategory_id', $product->subcategory_id);
        }

        $related = $relatedQuery->latest()->take(4)->get();

        $data['title'] = $product->title;
        $data['product'] = $product;
        $data['related'] = $related;
        $data['currency'] = optional(Setting::first())->website_currency ?? 'NGN';

        return view('frontend.user.selling.show', $data);
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'selling_product_id' => 'required|integer|exists:selling_product,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $qty = (int) $request->quantity;
        $userId = (int) $request->user()->id;

        $currency = optional(Setting::first())->website_currency ?? 'NGN';

        try {
            $result = DB::transaction(function () use ($request, $qty, $userId) {
                $user = User::whereKey($userId)->lockForUpdate()->firstOrFail();

                $product = SellingProduct::query()
                    ->where('status', 1)
                    ->with(['sellingApi'])
                    ->lockForUpdate()
                    ->findOrFail((int) $request->selling_product_id);

                if ($product->stock < $qty) {
                    throw new HttpResponseException(response()->json([
                        'status' => 'error',
                        'message' => "Only {$product->stock} in stock.",
                    ], 422));
                }

                $unit = UserServiceDiscount::apply($user->id, UserServiceDiscount::SERVICE_ACCOUNT_SELLING, (float) $product->price);
                $totalAmount = $unit * $qty;
                $currencyRate = (float) (Setting::first()->currency_rate ?? 1);
                $totalCost = round((float) $product->provider_price * $currencyRate * $qty, 4);

                if ((float) $user->balance < $totalAmount) {
                    throw new HttpResponseException(response()->json([
                        'status' => 'error',
                        'message' => 'Insufficient balance. Fund your wallet first.',
                    ], 422));
                }

                $api = $product->sellingApi;
                $isAccszone = $product->api_id
                    && $api
                    && strcasecmp((string) $api->label, 'accsbulk') === 0;

                if ($isAccszone) {
                    $adId = (int) $product->provider_id;
                    if ($adId < 1) {
                        throw new HttpResponseException(response()->json([
                            'status' => 'error',
                            'message' => 'This listing is missing a provider id.',
                        ], 422));
                    }

                    try {
                        $service = AccszoneService::fromSellingApi($api);
                        $data = $service->purchase($adId, $qty);
                    } catch (RuntimeException $e) {
                        throw new HttpResponseException(response()->json([
                            'status' => 'error',
                            'message' => $e->getMessage(),
                        ], 422));
                    }

                    $accountsRaw = $data['accounts'] ?? [];
                    if (! is_array($accountsRaw) || count($accountsRaw) < $qty) {
                        throw new HttpResponseException(response()->json([
                            'status' => 'error',
                            'message' => 'Provider returned an unexpected response. Please contact support.',
                        ], 422));
                    }

                    $order = SellingOrder::create([
                        'user_id' => $user->id,
                        'selling_product_id' => $product->id,
                        'quantity' => $qty,
                        'price' => $unit,
                        'total_amount' => $totalAmount,
                        'cost' => $totalCost,
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);

                    $credentialStrings = [];
                    foreach ($accountsRaw as $item) {
                        $cred = is_string($item) ? $item : json_encode($item, JSON_UNESCAPED_UNICODE);
                        SellingAccount::create([
                            'name' => '',
                            'product_id' => $product->id,
                            'selling_order_id' => $order->id,
                            'credentials' => $cred,
                            'is_sold' => true,
                        ]);
                        $credentialStrings[] = $cred;
                    }

                    $product->stock = (int) $product->stock - $qty;
                    $product->sold = (int) $product->sold + $qty;
                    $product->save();
                } elseif ($product->api_id === null) {
                    $picked = SellingAccount::query()
                        ->where('product_id', $product->id)
                        ->where('is_sold', false)
                        ->orderBy('id')
                        ->limit($qty)
                        ->lockForUpdate()
                        ->get();

                    if ($picked->count() < $qty) {
                        throw new HttpResponseException(response()->json([
                            'status' => 'error',
                            'message' => 'Stock changed. Please refresh and try again.',
                        ], 422));
                    }

                    $order = SellingOrder::create([
                        'user_id' => $user->id,
                        'selling_product_id' => $product->id,
                        'quantity' => $qty,
                        'price' => $unit,
                        'total_amount' => $totalAmount,
                        'cost' => $totalCost,
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);

                    $credentialStrings = [];
                    foreach ($picked as $acc) {
                        $acc->update([
                            'is_sold' => true,
                            'selling_order_id' => $order->id,
                        ]);
                        $credentialStrings[] = (string) ($acc->credentials ?? '');
                    }

                    $product->stock = (int) $product->stock - $qty;
                    $product->sold = (int) $product->sold + $qty;
                    $product->save();
                } else {
                    throw new HttpResponseException(response()->json([
                        'status' => 'error',
                        'message' => 'This product cannot be purchased online yet.',
                    ], 422));
                }

                $balanceBefore = (float) $user->balance;
                $balanceAfter = $balanceBefore - $totalAmount;
                $user->update(['balance' => $balanceAfter]);

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $totalAmount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'source' => 'selling_order',
                    'reference' => (string) $order->id,
                    'description' => 'Selling order #'.$order->id.' - '.$product->title,
                ]);

                return [
                    'order' => $order,
                    'product' => $product,
                    'user' => $user,
                    'qty' => $qty,
                    'totalAmount' => $totalAmount,
                    'balanceAfter' => $balanceAfter,
                    'credentialStrings' => $credentialStrings,
                ];
            });
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        } catch (\Throwable $e) {
            Log::error('Selling purchase failed: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }

        $order = $result['order'];
        $product = $result['product'];
        $user = $result['user'];
        $qty = $result['qty'];
        $totalAmount = $result['totalAmount'];
        $balanceAfter = $result['balanceAfter'];
        $credentialStrings = $result['credentialStrings'];

        $credentialsHtml = '';
        foreach ($credentialStrings as $index => $raw) {
            $n = $index + 1;
            $safe = nl2br(e((string) $raw));
            $credentialsHtml .= '<div style="margin-bottom:16px;padding:12px;border:1px solid #e5e5e5;border-radius:8px;background:#fafafa;">';
            $credentialsHtml .= '<p style="margin:0 0 8px;font-weight:bold;">Credential #'.$n.'</p>';
            $credentialsHtml .= '<p style="margin:0;font-family:monospace;word-break:break-all;font-size:14px;">'.$safe.'</p>';
            $credentialsHtml .= '</div>';
        }

        try {
            sendUserEmail('Account Order Purchased', [
                'order_id' => $order->id,
                'product_name' => (string) $product->title,
                'quantity' => $qty,
                'total_amount' => number_format((float) $totalAmount, 2),
                'currency' => $currency,
                'credentials_html' => $credentialsHtml,
            ], $user);
        } catch (\Throwable $e) {
            Log::error('Selling order email failed: '.$e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order placed successfully.',
            'order_id' => $order->id,
            'balance' => $balanceAfter,
        ]);
    }
}
