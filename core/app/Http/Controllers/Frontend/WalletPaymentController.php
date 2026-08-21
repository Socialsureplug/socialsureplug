<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\Admin;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class WalletPaymentController extends Controller
{
    public function __construct(
        protected ReferralService $referralService
    ) {}

    // Shared: create a transaction row when changing balance
    protected function logTransaction($user, string $type, float $amount, string $source, ?string $reference, ?string $description = null): void
    {
        $before = $user->balance;
        $after  = $type === 'credit' ? $before + $amount : $before - $amount;

        Transaction::create([
            'user_id'        => $user->id,
            'type'           => $type,
            'amount'         => $amount,
            'balance_before' => $before,
            'balance_after'  => $after,
            'source'         => $source,
            'reference'      => $reference,
            'description'    => $description,
        ]);
    }

    protected function sendPaymentSuccessEmails($user, string $amount, string $currency, string $reference, string $method): void
    {
        try {
            sendUserEmail('Payment Success', [
                'amount' => $amount,
                'currency' => $currency,
                'reference' => $reference,
                'method' => $method,
            ], $user);
        } catch (\Exception $e) {
            Log::error('Payment success user email failed: ' . $e->getMessage());
        }
        $admin = Admin::first();
        if ($admin) {
            try {
                sendAdminEmail('user_payment', [
                    'user_email' => $user->email,
                    'amount' => $amount,
                    'currency' => $currency,
                    'reference' => $reference,
                    'method' => $method,
                ], $admin);
            } catch (\Exception $e) {
                Log::error('Payment success admin email failed: ' . $e->getMessage());
            }
        }
    }

    protected function getKorapayGateway(): ?Gateway
    {
        return Gateway::where('gateway_name', 'korapay')->where('status', 1)->first();
    }

    protected function getBachsGateway(): ?Gateway
    {
        return Gateway::where('gateway_name', 'bachs')->where('status', 1)->first();
    }

    /**
     * Bachs's key prefix decides which deployment to call — sk_sandbox_ keys
     * only work against the sandbox host, sk_live_ only against production.
     * Going live is a key swap, never a separate config toggle.
     */
    protected function bachsBaseUrl(string $apiKey): ?string
    {
        if (str_starts_with($apiKey, 'sk_live_')) {
            return 'https://api.bachs.io';
        }

        if (str_starts_with($apiKey, 'sk_sandbox_')) {
            return 'https://sandbox-api.bachs.io';
        }

        return null;
    }

    protected function amountWithinGatewayLimit(Gateway $gateway, float $amount): bool
    {
        $min = (float) ($gateway->min_payment ?? 0);
        $max = $gateway->max_payment !== null ? (float) $gateway->max_payment : null;

        if ($amount < $min) {
            return false;
        }

        if ($max !== null && $amount > $max) {
            return false;
        }

        return true;
    }

    protected function gatewayAmountLimitMessage(Gateway $gateway): string
    {
        $min = (float) ($gateway->min_payment ?? 0);
        $max = $gateway->max_payment !== null ? (float) $gateway->max_payment : null;

        if ($max !== null) {
            return 'Amount must be between '.number_format($min, 2).' and '.number_format($max, 2).'.';
        }

        return 'Amount must be at least '.number_format($min, 2).'.';
    }

    /**
     * @param  array<string, mixed>  $gatewayData
     */
    protected function creditWalletFromGateway(
        $user,
        string $method,
        float $amount,
        string $currency,
        string $reference,
        ?string $channel,
        array $gatewayData,
        string $description
    ): bool {
        $paymentCredited = false;
        $paymentModel = null;

        DB::transaction(function () use (
            $user,
            $method,
            $amount,
            $currency,
            $reference,
            $channel,
            $gatewayData,
            $description,
            &$paymentCredited,
            &$paymentModel
        ) {
            $payment = Payment::where('reference', $reference)->first();
            if (! $payment) {
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'method' => $method,
                    'currency' => $currency,
                    'amount' => $amount,
                    'reference' => $reference,
                    'status' => 'pending',
                    'channel' => $channel,
                    'gateway_response' => json_encode($gatewayData),
                ]);
            } else {
                $payment->gateway_response = json_encode($gatewayData);
                $payment->channel = $channel;
                $payment->currency = $currency;
                $payment->amount = $amount;
                $payment->save();
            }

            if ($payment->status !== 'success') {
                $user->balance += $amount;
                $user->save();

                $payment->status = 'success';
                $payment->save();

                $this->logTransaction(
                    $user,
                    'credit',
                    $amount,
                    'wallet_topup',
                    $reference,
                    $description
                );

                $paymentCredited = true;
            }

            $paymentModel = $payment;
        });

        if ($paymentCredited && $paymentModel) {
            $this->referralService->applyDepositCommission($user, $paymentModel);
        }

        return $paymentCredited;
    }

    /* ------------ Paystack callback (auto) ------------ */

    public function paystackCallback(Request $request)
    {
        $reference = $request->query('reference');
        if (!$reference) {
            return redirect()->route('user.payment.index')->with('error', 'Missing reference');
        }

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('user.login.page');
        }
        /** @var \App\Models\User $user */

        $gateway = Gateway::where('gateway_name', 'paystack')->where('status', 1)->first();
        if (!$gateway) {
            return redirect()->route('user.payment.index')->with('error', 'Paystack not configured');
        }

        $params    = (object) $gateway->gateway_parameters;
        $secretKey = $params->paystack_key ?? null; // YOUR secret key field
        if (!$secretKey) {
            return redirect()->route('user.payment.index')->with('error', 'Secret key missing');
        }

        // Verify with Paystack
        $verifyUrl = "https://api.paystack.co/transaction/verify/{$reference}";

        $caBundle = storage_path('app/cacert.pem');
        $http = Http::withToken($secretKey)->acceptJson();
        if (is_file($caBundle)) {
            $http = $http->withOptions(['verify' => $caBundle]);
        }

        $response = $http->get($verifyUrl);

        if (!$response->successful()) {
            return redirect()->route('user.payment.index')->with('error', 'Unable to verify payment');
        }

        $data = $response->json();
        if (!($data['status'] ?? false) || ($data['data']['status'] ?? '') !== 'success') {
            // Mark payment log as failed if already created
            Payment::where('reference', $reference)->update(['status' => 'failed', 'gateway_response' => json_encode($data)]);
            return redirect()->route('user.payment.index')->with('error', 'Payment not successful');
        }

        // amount returned in kobo
        $amount   = ($data['data']['amount'] ?? 0) / 100;
        $currency = $data['data']['currency'] ?? ($params->gateway_currency ?? 'NGN');

        if (! $this->amountWithinGatewayLimit($gateway, (float) $amount)) {
            Payment::where('reference', $reference)->update(['status' => 'failed', 'gateway_response' => json_encode($data)]);
            return redirect()->route('user.payment.index')->with('error', $this->gatewayAmountLimitMessage($gateway));
        }

        $paymentCredited = false;
        $paymentModel = null;
        DB::transaction(function () use ($user, $amount, $currency, $reference, $data, &$paymentCredited, &$paymentModel) {
            // Find existing or create new payment log
            $payment = Payment::where('reference', $reference)->first();
            if (! $payment) {
                $payment = Payment::create([
                    'user_id'          => $user->id,
                    'method'           => 'paystack',
                    'currency'         => $currency,
                    'amount'           => $amount,
                    'reference'        => $reference,
                    'status'           => 'pending',
                    'channel'          => $data['data']['channel'] ?? null,
                    'gateway_response' => json_encode($data),
                ]);
            }

            if ($payment->status !== 'success') {
                $user->balance += $amount;
                $user->save();

                $payment->status = 'success';
                $payment->save();

                $this->logTransaction(
                    $user,
                    'credit',
                    $amount,
                    'wallet_topup',
                    $reference,
                    'Wallet top-up via Paystack'
                );

                $paymentCredited = true;
            }
            $paymentModel = $payment;
        });

        if ($paymentCredited && $paymentModel) {
            $this->referralService->applyDepositCommission($user, $paymentModel);
        }

        $this->sendPaymentSuccessEmails($user, (string) $amount, $currency, $reference, 'Paystack');

        return redirect()->route('user.payment.index')
            ->with('success', "Wallet funded successfully with {$currency} {$amount}");
    }

    /* ------------ Flutterwave callback (auto) ------------ */

    public function flutterwaveCallback(Request $request)
    {
        $transactionId = $request->query('transaction_id') ?? $request->query('tx_ref');
        if (!$transactionId) {
            return redirect()->route('user.payment.index')->with('error', 'Missing transaction reference');
        }

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('user.login.page');
        }
        /** @var \App\Models\User $user */

        $gateway = Gateway::where('gateway_name', 'flutterwave')->where('status', 1)->first();
        if (!$gateway) {
            return redirect()->route('user.payment.index')->with('error', 'Flutterwave not configured');
        }

        $params    = (object) $gateway->gateway_parameters;
        $secretKey = $params->secret_key ?? null;
        if (!$secretKey) {
            return redirect()->route('user.payment.index')->with('error', 'Secret key missing');
        }

        $verifyUrl = "https://api.flutterwave.com/v3/transactions/{$transactionId}/verify";

        $caBundle = storage_path('app/cacert.pem');
        $http = Http::withToken($secretKey)->acceptJson();
        if (is_file($caBundle)) {
            $http = $http->withOptions(['verify' => $caBundle]);
        }

        $response = $http->get($verifyUrl);

        if (!$response->successful()) {
            return redirect()->route('user.payment.index')->with('error', 'Unable to verify payment');
        }

        $data = $response->json();
        if (($data['status'] ?? '') !== 'success' || ($data['data']['status'] ?? '') !== 'successful') {
            Payment::where('reference', $transactionId)->update(['status' => 'failed', 'gateway_response' => json_encode($data)]);
            return redirect()->route('user.payment.index')->with('error', 'Payment not successful');
        }

        $amount   = $data['data']['amount'] ?? 0;
        $currency = $data['data']['currency'] ?? ($params->gateway_currency ?? 'NGN');

        if (! $this->amountWithinGatewayLimit($gateway, (float) $amount)) {
            Payment::where('reference', $transactionId)->update(['status' => 'failed', 'gateway_response' => json_encode($data)]);
            return redirect()->route('user.payment.index')->with('error', $this->gatewayAmountLimitMessage($gateway));
        }

        $paymentCredited = false;
        $paymentModel = null;
        DB::transaction(function () use ($user, $amount, $currency, $transactionId, $data, &$paymentCredited, &$paymentModel) {
            $payment = Payment::where('reference', $transactionId)->first();
            if (! $payment) {
                $payment = Payment::create([
                    'user_id'          => $user->id,
                    'method'           => 'flutterwave',
                    'currency'         => $currency,
                    'amount'           => $amount,
                    'reference'        => $transactionId,
                    'status'           => 'pending',
                    'channel'          => $data['data']['payment_type'] ?? null,
                    'gateway_response' => json_encode($data),
                ]);
            }

            if ($payment->status !== 'success') {
                $user->balance += $amount;
                $user->save();

                $payment->status = 'success';
                $payment->save();

                $this->logTransaction(
                    $user,
                    'credit',
                    $amount,
                    'wallet_topup',
                    $transactionId,
                    'Wallet top-up via Flutterwave'
                );

                $paymentCredited = true;
            }
            $paymentModel = $payment;
        });

        if ($paymentCredited && $paymentModel) {
            $this->referralService->applyDepositCommission($user, $paymentModel);
        }

        $this->sendPaymentSuccessEmails($user, (string) $amount, $currency, $transactionId, 'Flutterwave');

        return redirect()->route('user.payment.index')
            ->with('success', "Wallet funded successfully with {$currency} {$amount}");
    }

    public function korapayInitiate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $gateway = $this->getKorapayGateway();
        if (! $gateway) {
            return response()->json(['message' => 'KoraPay not configured'], Response::HTTP_BAD_REQUEST);
        }

        $params = (object) $gateway->gateway_parameters;
        $secretKey = $params->secret_key ?? null;
        if (! $secretKey) {
            return response()->json(['message' => 'KoraPay secret key missing'], Response::HTTP_BAD_REQUEST);
        }

        $amount = (float) $request->input('amount');
        $currency = $params->gateway_currency ?? 'NGN';

        if (! $this->amountWithinGatewayLimit($gateway, $amount)) {
            return response()->json(['message' => $this->gatewayAmountLimitMessage($gateway)], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $reference = 'KORA-'.Str::upper(Str::random(12));

        $callbackUrl = route('user.payment.korapay.callback');
        $payload = [
            'amount' => $amount,
            'currency' => $currency,
            'reference' => $reference,
            'redirect_url' => $callbackUrl,
            'customer' => [
                'name' => trim(($user->fname ?? '').' '.($user->lname ?? '')) ?: $user->email,
                'email' => $user->email,
            ],
            'notification_url' => route('user.payment.korapay.webhook'),
        ];

        $caBundle = storage_path('app/cacert.pem');
        $http = Http::withToken($secretKey)->acceptJson();
        if (is_file($caBundle)) {
            $http = $http->withOptions(['verify' => $caBundle]);
        }

        $response = $http->post('https://api.korapay.com/merchant/api/v1/charges/initialize', $payload);
        if (! $response->successful()) {
            Log::error('KoraPay initialize failed', ['body' => $response->body()]);

            return response()->json(['message' => 'Unable to initialize KoraPay payment'], Response::HTTP_BAD_GATEWAY);
        }

        $data = $response->json();
        if (! ($data['status'] ?? false)) {
            return response()->json(['message' => 'KoraPay initialization failed'], Response::HTTP_BAD_GATEWAY);
        }

        $checkoutUrl = $data['data']['checkout_url'] ?? null;
        if (! $checkoutUrl) {
            return response()->json(['message' => 'KoraPay checkout URL missing'], Response::HTTP_BAD_GATEWAY);
        }

        Payment::updateOrCreate(
            ['reference' => $reference],
            [
                'user_id' => $user->id,
                'method' => 'korapay',
                'currency' => $currency,
                'amount' => $amount,
                'status' => 'pending',
                'channel' => null,
                'gateway_response' => json_encode($data),
            ]
        );

        return response()->json([
            'checkout_url' => $checkoutUrl,
            'reference' => $reference,
        ]);
    }

    public function korapayCallback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('transaction_reference');
        if (! $reference) {
            return redirect()->route('user.payment.index')->with('error', 'Missing KoraPay reference');
        }

        $user = Auth::user();
        if (! $user) {
            return redirect()->route('user.login.page');
        }

        $gateway = $this->getKorapayGateway();
        if (! $gateway) {
            return redirect()->route('user.payment.index')->with('error', 'KoraPay not configured');
        }

        $params = (object) $gateway->gateway_parameters;
        $secretKey = $params->secret_key ?? null;
        if (! $secretKey) {
            return redirect()->route('user.payment.index')->with('error', 'KoraPay secret key missing');
        }

        $caBundle = storage_path('app/cacert.pem');
        $http = Http::withToken($secretKey)->acceptJson();
        if (is_file($caBundle)) {
            $http = $http->withOptions(['verify' => $caBundle]);
        }

        $response = $http->get("https://api.korapay.com/merchant/api/v1/charges/{$reference}");
        if (! $response->successful()) {
            return redirect()->route('user.payment.index')->with('error', 'Unable to verify KoraPay payment');
        }

        $data = $response->json();
        $chargeData = $data['data'] ?? [];
        $chargeStatus = strtolower((string) ($chargeData['status'] ?? ''));
        if (! ($data['status'] ?? false) || ! in_array($chargeStatus, ['success', 'successful', 'paid'], true)) {
            Payment::where('reference', $reference)->update([
                'status' => 'failed',
                'gateway_response' => json_encode($data),
            ]);

            return redirect()->route('user.payment.index')->with('error', 'KoraPay payment not successful');
        }

        $amount = (float) ($chargeData['amount'] ?? 0);
        $currency = $chargeData['currency'] ?? ($params->gateway_currency ?? 'NGN');
        $channel = $chargeData['channel'] ?? ($chargeData['payment_method'] ?? null);

        if (! $this->amountWithinGatewayLimit($gateway, $amount)) {
            Payment::where('reference', $reference)->update([
                'status' => 'failed',
                'gateway_response' => json_encode($data),
            ]);

            return redirect()->route('user.payment.index')->with('error', $this->gatewayAmountLimitMessage($gateway));
        }

        $credited = $this->creditWalletFromGateway(
            $user,
            'korapay',
            $amount,
            $currency,
            $reference,
            $channel,
            $data,
            'Wallet top-up via KoraPay'
        );

        if ($credited) {
            $this->sendPaymentSuccessEmails($user, (string) $amount, $currency, $reference, 'KoraPay');
        }

        return redirect()->route('user.payment.index')
            ->with('success', "Wallet funded successfully with {$currency} {$amount}");
    }

    public function korapayWebhook(Request $request)
    {
        $gateway = $this->getKorapayGateway();
        if (! $gateway) {
            return response()->json(['message' => 'Gateway unavailable'], Response::HTTP_BAD_REQUEST);
        }

        $params = (object) $gateway->gateway_parameters;
        $secret = $params->webhook_secret ?? $params->secret_key ?? null;
        if (! $secret) {
            return response()->json(['message' => 'Webhook secret missing'], Response::HTTP_BAD_REQUEST);
        }

        $rawBody = $request->getContent();
        $signature = $request->header('x-korapay-signature');
        $expected = hash_hmac('sha256', $rawBody, $secret);
        if (! $signature || ! hash_equals($expected, (string) $signature)) {
            return response()->json(['message' => 'Invalid signature'], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $request->all();
        $event = strtolower((string) ($payload['event'] ?? ''));
        $data = $payload['data'] ?? [];
        $reference = $data['reference'] ?? $data['transaction_reference'] ?? null;

        if (! $reference) {
            return response()->json(['message' => 'Missing reference'], Response::HTTP_BAD_REQUEST);
        }

        $payment = Payment::where('reference', $reference)->first();
        if (! $payment) {
            return response()->json(['message' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        $status = strtolower((string) ($data['status'] ?? ''));
        $isSuccessEvent = in_array($event, ['charge.success', 'payment.success'], true);
        $isSuccessStatus = in_array($status, ['success', 'successful', 'paid'], true);

        if ($isSuccessEvent || $isSuccessStatus) {
            $user = $payment->user;
            if ($user) {
                $amount = (float) ($data['amount'] ?? $payment->amount);
                $currency = $data['currency'] ?? $payment->currency;
                $channel = $data['channel'] ?? ($data['payment_method'] ?? $payment->channel);

                if (! $this->amountWithinGatewayLimit($gateway, $amount)) {
                    $payment->status = 'failed';
                    $payment->gateway_response = json_encode($payload);
                    $payment->save();

                    return response()->json(['message' => 'Amount outside allowed gateway range'], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $credited = $this->creditWalletFromGateway(
                    $user,
                    'korapay',
                    $amount,
                    $currency,
                    $reference,
                    $channel,
                    $payload,
                    'Wallet top-up via KoraPay'
                );

                if ($credited) {
                    $this->sendPaymentSuccessEmails($user, (string) $amount, $currency, $reference, 'KoraPay');
                }
            }
        } elseif (in_array($status, ['failed', 'cancelled'], true)) {
            $payment->status = $status === 'failed' ? 'failed' : 'cancelled';
            $payment->gateway_response = json_encode($payload);
            $payment->save();
        }

        return response()->json(['message' => 'ok']);
    }

    /* ------------ Bachs (auto) ------------ */

    public function bachsInitiate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $gateway = $this->getBachsGateway();
        if (! $gateway) {
            return response()->json(['message' => 'Bachs not configured'], Response::HTTP_BAD_REQUEST);
        }

        $params = (object) $gateway->gateway_parameters;
        $apiKey = $params->api_key ?? null;
        $baseUrl = $apiKey ? $this->bachsBaseUrl($apiKey) : null;
        if (! $apiKey || ! $baseUrl) {
            return response()->json(['message' => 'Bachs API key missing or invalid'], Response::HTTP_BAD_REQUEST);
        }

        $amount = (float) $request->input('amount');
        $currency = $params->gateway_currency ?? 'NGN';

        if (! $this->amountWithinGatewayLimit($gateway, $amount)) {
            return response()->json(['message' => $this->gatewayAmountLimitMessage($gateway)], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Our own idempotency token for the create call (protects against a
        // network retry creating two sessions); NOT what we key the payment
        // record on — the Bachs-generated checkout_id is, since that's what
        // comes back on both the redirect and the collection.succeeded webhook.
        $clientReference = 'BACHS-'.Str::upper(Str::random(16));

        $payload = [
            'pricing' => [
                'currency' => $currency,
                'amount' => number_format($amount, 2, '.', ''),
            ],
            'customer' => [
                'email' => $user->email,
                'name' => trim(($user->fname ?? '').' '.($user->lname ?? '')) ?: $user->email,
            ],
            'success_url' => route('user.payment.bachs.callback'),
            'cancel_url' => route('user.payment.index'),
            'reference' => $clientReference,
            'metadata' => ['user_id' => (string) $user->id],
        ];

        $caBundle = storage_path('app/cacert.pem');
        $http = Http::withToken($apiKey)->acceptJson();
        if (is_file($caBundle)) {
            $http = $http->withOptions(['verify' => $caBundle]);
        }

        $response = $http->withHeaders(['Idempotency-Key' => $clientReference])
            ->post($baseUrl.'/v1/checkout-sessions', $payload);

        if (! $response->successful()) {
            Log::error('Bachs checkout session creation failed', ['body' => $response->body()]);

            return response()->json(['message' => 'Unable to initialize Bachs payment'], Response::HTTP_BAD_GATEWAY);
        }

        $data = $response->json();
        $checkoutId = $data['checkout_id'] ?? null;
        $checkoutUrl = $data['checkout_url'] ?? null;
        if (! $checkoutId || ! $checkoutUrl) {
            return response()->json(['message' => 'Bachs checkout session missing expected fields'], Response::HTTP_BAD_GATEWAY);
        }

        Payment::updateOrCreate(
            ['reference' => $checkoutId],
            [
                'user_id' => $user->id,
                'method' => 'bachs',
                'currency' => $currency,
                'amount' => $amount,
                'status' => 'pending',
                'channel' => null,
                'gateway_response' => json_encode($data),
            ]
        );

        return response()->json([
            'checkout_url' => $checkoutUrl,
            'reference' => $checkoutId,
        ]);
    }

    public function bachsCallback(Request $request)
    {
        $checkoutId = $request->query('checkout_id');
        if (! $checkoutId) {
            return redirect()->route('user.payment.index')->with('error', 'Missing Bachs checkout reference');
        }

        $user = Auth::user();
        if (! $user) {
            return redirect()->route('user.login.page');
        }

        $gateway = $this->getBachsGateway();
        if (! $gateway) {
            return redirect()->route('user.payment.index')->with('error', 'Bachs not configured');
        }

        $params = (object) $gateway->gateway_parameters;
        $apiKey = $params->api_key ?? null;
        $baseUrl = $apiKey ? $this->bachsBaseUrl($apiKey) : null;
        if (! $apiKey || ! $baseUrl) {
            return redirect()->route('user.payment.index')->with('error', 'Bachs API key missing or invalid');
        }

        // The redirect only tells us the customer came back — never trust it
        // for crediting. Re-verify the checkout session against Bachs before
        // touching the wallet; the collection.succeeded webhook remains the
        // authoritative confirmation regardless of what happens here.
        $caBundle = storage_path('app/cacert.pem');
        $http = Http::withToken($apiKey)->acceptJson();
        if (is_file($caBundle)) {
            $http = $http->withOptions(['verify' => $caBundle]);
        }

        $response = $http->get($baseUrl.'/v1/checkout-sessions/'.$checkoutId);
        if (! $response->successful()) {
            return redirect()->route('user.payment.index')->with('error', 'Unable to verify Bachs payment');
        }

        $data = $response->json();
        $status = strtoupper((string) ($data['status'] ?? ''));
        $charge = $data['charge'] ?? null;
        $chargeStatus = strtolower((string) ($charge['status'] ?? ($data['payment_status'] ?? '')));

        if ($status !== 'COMPLETED' || $chargeStatus !== 'succeeded') {
            return redirect()->route('user.payment.index')->with('error', 'Bachs payment not successful');
        }

        $amount = (float) ($charge['amount'] ?? $data['amount'] ?? 0);
        $currency = $charge['currency'] ?? $data['currency'] ?? ($params->gateway_currency ?? 'NGN');

        if (! $this->amountWithinGatewayLimit($gateway, $amount)) {
            Payment::where('reference', $checkoutId)->update([
                'status' => 'failed',
                'gateway_response' => json_encode($data),
            ]);

            return redirect()->route('user.payment.index')->with('error', $this->gatewayAmountLimitMessage($gateway));
        }

        $credited = $this->creditWalletFromGateway(
            $user,
            'bachs',
            $amount,
            $currency,
            $checkoutId,
            $charge['payment_method'] ?? ($data['payment_method'] ?? null),
            $data,
            'Wallet top-up via Bachs'
        );

        if ($credited) {
            $this->sendPaymentSuccessEmails($user, (string) $amount, $currency, $checkoutId, 'Bachs');
        }

        return redirect()->route('user.payment.index')
            ->with('success', "Wallet funded successfully with {$currency} {$amount}");
    }

    public function bachsWebhook(Request $request)
    {
        $gateway = $this->getBachsGateway();
        if (! $gateway) {
            return response()->json(['message' => 'Gateway unavailable'], Response::HTTP_BAD_REQUEST);
        }

        $params = (object) $gateway->gateway_parameters;
        $secret = $params->webhook_secret ?? null;
        if (! $secret) {
            return response()->json(['message' => 'Webhook secret missing'], Response::HTTP_BAD_REQUEST);
        }

        // Signature is HMAC-SHA256 of "{timestamp}.{raw_body}" — must be
        // computed against the untouched raw body, not a re-serialized parse
        // of it, or the signature will never match.
        $rawBody = $request->getContent();
        $timestampHeader = $request->header('X-Bachs-Timestamp');
        $signatureHeader = $request->header('X-Bachs-Signature');

        if (! $timestampHeader || ! $signatureHeader) {
            return response()->json(['message' => 'Missing signature headers'], Response::HTTP_UNAUTHORIZED);
        }

        if (abs(time() - (int) $timestampHeader) > 300) {
            return response()->json(['message' => 'Stale webhook delivery'], Response::HTTP_UNAUTHORIZED);
        }

        $expected = hash_hmac('sha256', $timestampHeader.'.'.$rawBody, $secret);
        if (! hash_equals($expected, (string) $signatureHeader)) {
            return response()->json(['message' => 'Invalid signature'], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $request->all();
        $type = $payload['type'] ?? '';
        $data = $payload['data'] ?? [];
        $checkoutId = $data['checkout_id'] ?? null;

        if (! $checkoutId) {
            return response()->json(['message' => 'Missing checkout_id']);
        }

        $payment = Payment::where('reference', $checkoutId)->first();
        if (! $payment) {
            return response()->json(['message' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        $status = strtolower((string) ($data['status'] ?? ''));

        if ($type === 'collection.succeeded' || $status === 'succeeded') {
            $user = $payment->user;
            if ($user) {
                $amount = (float) ($data['amount'] ?? $payment->amount);
                $currency = $data['currency'] ?? $payment->currency;

                if (! $this->amountWithinGatewayLimit($gateway, $amount)) {
                    $payment->status = 'failed';
                    $payment->gateway_response = json_encode($payload);
                    $payment->save();

                    return response()->json(['message' => 'Amount outside allowed gateway range'], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $credited = $this->creditWalletFromGateway(
                    $user,
                    'bachs',
                    $amount,
                    $currency,
                    $checkoutId,
                    null,
                    $payload,
                    'Wallet top-up via Bachs'
                );

                if ($credited) {
                    $this->sendPaymentSuccessEmails($user, (string) $amount, $currency, $checkoutId, 'Bachs');
                }
            }
        } elseif ($type === 'collection.failed' || in_array($status, ['failed', 'cancelled'], true)) {
            $payment->status = $status === 'cancelled' ? 'cancelled' : 'failed';
            $payment->gateway_response = json_encode($payload);
            $payment->save();
        }

        return response()->json(['message' => 'ok']);
    }

    /* ------------ Bank transfer (manual) ------------ */

    // Called when user submits amount + chooses bank transfer
    public function initiateBankTransfer(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user   = Auth::user();
        $amount = (float) $request->input('amount');

        $gateway = Gateway::where('gateway_name', 'bank')->where('status', 1)->first();
        if (!$gateway) {
            return redirect()->route('user.payment.index')->with('error', 'Bank transfer not available');
        }

        if (! $this->amountWithinGatewayLimit($gateway, $amount)) {
            return redirect()->route('user.payment.index')->with('error', $this->gatewayAmountLimitMessage($gateway));
        }

        $params = (object) $gateway->gateway_parameters;

        $reference = 'BANK-' . Str::upper(Str::random(10));

        // Do NOT create a payment log yet; only when user uploads proof.
        // Store pending bank transfer data in session and use a simple object for the view.
        $currency = $params->gateway_currency ?? 'NGN';

        session()->put('pending_bank_transfer', [
            'user_id'  => $user->id,
            'amount'   => $amount,
            'currency' => $currency,
            'reference'=> $reference,
        ]);

        $paymentViewData = (object) [
            'user_id'  => $user->id,
            'method'   => 'bank',
            'currency' => $currency,
            'amount'   => $amount,
            'reference'=> $reference,
            'status'   => 'pending',
        ];

        // Show bank details + reference + proof upload form
        return view('frontend.user.payment.bank', [
            'title'    => 'Bank Transfer',
            'gateway'  => $gateway,
            'params'   => $params,
            'payment'  => $paymentViewData,
        ]);
    }

    // User uploads proof for a pending bank transfer
    public function uploadBankProof(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('user.login.page');
        }

        $pending = session('pending_bank_transfer');
        if (!$pending || ($pending['user_id'] ?? null) !== Auth::id()) {
            return redirect()->route('user.payment.index')->with('error', 'No pending bank transfer found.');
        }

        $request->validate([
            'proof' => 'required|image|max:4096',
        ]);

        // Handle payment proof upload (follow existing image pattern)
        $file = $request->file('proof');
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $destinationPath = base_path('../assets/frontend/images/payment_proof');

        if (! file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $file->move($destinationPath, $filename);
        $paymentProofPath = 'assets/frontend/images/payment_proof/'.$filename;

        // Create the actual payment log now that the user has submitted proof
        $payment = Payment::create([
            'user_id'    => $pending['user_id'],
            'method'     => 'bank',
            'currency'   => $pending['currency'],
            'amount'     => $pending['amount'],
            'reference'  => $pending['reference'],
            'status'     => 'pending',
            'proof_path' => $paymentProofPath,
        ]);

        // Clear pending data so we don't reuse it accidentally
        session()->forget('pending_bank_transfer');

        $user = Auth::user();
        try {
            sendUserEmail('Payment Submitted', [
                'amount' => (string) $pending['amount'],
                'currency' => $pending['currency'],
                'reference' => $pending['reference'],
            ], $user);
        } catch (\Exception $e) {
            Log::error('Payment submitted user email failed: ' . $e->getMessage());
        }
        $admin = Admin::first();
        if ($admin) {
            try {
                sendAdminEmail('user_payment', [
                    'user_email' => $user->email,
                    'amount' => (string) $pending['amount'],
                    'currency' => $pending['currency'],
                    'reference' => $pending['reference'],
                    'method' => 'Bank transfer (pending approval)',
                ], $admin);
            } catch (\Exception $e) {
                Log::error('Payment submitted admin email failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('user.log.payments')
            ->with('success', 'Payment proof uploaded. Awaiting admin approval.');
    }
}