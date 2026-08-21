<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentLogController extends Controller
{
    public function __construct(
        protected ReferralService $referralService
    ) {}

    public function index(Request $request)
    {
        $title = 'Payment Log';

        $query = Payment::with('user')->orderByDesc('id');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', "%{$term}%")
                    ->orWhere('amount', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($u) use ($term) {
                        $u->where('email', 'like', "%{$term}%")
                            ->orWhere('fname', 'like', "%{$term}%")
                            ->orWhere('lname', 'like', "%{$term}%");
                    });
            });
        }

        $payments = $query->paginate(20)->withQueryString();
        $search = $request->search ?? '';

        return view('backend.payments.index', compact('title', 'payments', 'search'));
    }

    public function show(Payment $payment)
    {
        $title = 'Payment Details';

        $params = null;
        if ($payment->method === 'bank') {
            $gateway = Gateway::where('gateway_name', 'bank')->first();
            $params  = (object) ($gateway->gateway_parameters ?? []);
        }

        return view('backend.payments.show', compact('title', 'payment', 'params'));
    }
    
    

    public function approveBank(Payment $payment)
    {
        if ($payment->method !== 'bank' || $payment->status !== 'pending') {
            return back()->with('error', 'Invalid payment state');
        }

        DB::transaction(function () use ($payment) {
            $user = User::findOrFail($payment->user_id);

            $before = $user->balance;
            $after  = $before + $payment->amount;

            $user->balance = $after;
            $user->save();

            $payment->status = 'success';
            $payment->save();

            Transaction::create([
                'user_id'        => $user->id,
                'type'           => 'credit',
                'amount'         => $payment->amount,
                'balance_before' => $before,
                'balance_after'  => $after,
                'source'         => 'wallet_topup',
                'reference'      => $payment->reference,
                'description'    => 'Wallet top-up via bank transfer (manual approval)',
            ]);
        });

        $paidUser = User::find($payment->user_id);
        if ($paidUser) {
            $this->referralService->applyDepositCommission($paidUser, $payment);
        }

        $user = User::find($payment->user_id);
        if ($user) {
            try {
                sendUserEmail('Payment Approved', [
                    'amount' => (string) $payment->amount,
                    'currency' => $payment->currency,
                    'reference' => $payment->reference,
                ], $user);
            } catch (\Exception $e) {
                Log::error('Payment approved email failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Payment approved and wallet credited.');
    }

    public function rejectBank(Payment $payment)
    {
        if ($payment->method !== 'bank' || $payment->status !== 'pending') {
            return back()->with('error', 'Invalid payment state');
        }

        $payment->status = 'failed';
        $payment->save();

        return back()->with('success', 'Payment marked as failed.');
    }
    
        public function markSuccess(Payment $payment)
    {
        // Only allow marking pending or failed payments
        if (!in_array($payment->status, ['pending', 'failed'])) {
            return back()->with('error', 'Only pending or failed payments can be marked as success.');
        }

        DB::transaction(function () use ($payment) {
            $user = User::findOrFail($payment->user_id);

            $before = $user->balance;
            $after  = $before + $payment->amount;

            $user->balance = $after;
            $user->save();

            $payment->status = 'success';
            $payment->save();

            Transaction::create([
                'user_id'        => $user->id,
                'type'           => 'credit',
                'amount'         => $payment->amount,
                'balance_before' => $before,
                'balance_after'  => $after,
                'source'         => 'wallet_topup',
                'reference'      => $payment->reference,
                'description'    => 'Wallet top-up via ' . ucfirst($payment->method) . ' (manual approval)',
            ]);
        });

        // Apply referral commission
        $paidUser = User::find($payment->user_id);
        if ($paidUser) {
            $this->referralService->applyDepositCommission($paidUser, $payment);
        }

        // Send email notification
        $user = User::find($payment->user_id);
        if ($user) {
            try {
                sendUserEmail('Payment Approved', [
                    'amount'    => (string) $payment->amount,
                    'currency'  => $payment->currency,
                    'reference' => $payment->reference,
                ], $user);
            } catch (\Exception $e) {
                Log::error('Payment approved email failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Payment marked as successful and wallet credited.');
    }
}