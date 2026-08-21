<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\ReferralCommission;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    public function applyDepositCommission(User $depositor, Payment $payment): void
    {
        $settings = Setting::first();
        if (! $settings || (int) ($settings->referral_enabled ?? 0) !== 1) {
            return;
        }

        $percent = (float) ($settings->referral_commission_percent ?? 0);
        if ($percent <= 0) {
            return;
        }

        if (! empty($depositor->reffered_by)) {
            $referrerId = (int) $depositor->reffered_by;
        } else {
            return;
        }

        if ($referrerId <= 0 || $referrerId === (int) $depositor->id) {
            return;
        }

        $referrer = User::find($referrerId);
        if (! $referrer) {
            return;
        }

        $alreadyCredited = ReferralCommission::where('payment_id', $payment->id)->exists();
        if ($alreadyCredited) {
            return;
        }

        if ((int) ($settings->referral_first_deposit_only ?? 1) === 1) {
            $hasSuccessfulDeposit = Payment::where('user_id', $depositor->id)
                ->where('status', 'success')
                ->where('id', '!=', $payment->id)
                ->exists();

            if ($hasSuccessfulDeposit) {
                return;
            }
        }

        $commissionAmount = round(((float) $payment->amount * $percent) / 100, 2);
        if ($commissionAmount <= 0) {
            return;
        }

        DB::transaction(function () use ($referrer, $depositor, $payment, $percent, $commissionAmount) {
            $before = (float) $referrer->balance;
            $after = $before + $commissionAmount;

            $referrer->balance = $after;
            $referrer->save();

            ReferralCommission::create([
                'referrer_user_id' => $referrer->id,
                'referred_user_id' => $depositor->id,
                'payment_id' => $payment->id,
                'deposit_amount' => $payment->amount,
                'commission_percent' => $percent,
                'commission_amount' => $commissionAmount,
                'reference' => $payment->reference,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            Transaction::create([
                'user_id' => $referrer->id,
                'type' => 'credit',
                'amount' => $commissionAmount,
                'balance_before' => $before,
                'balance_after' => $after,
                'source' => 'referral_commission',
                'reference' => $payment->reference,
                'description' => 'Referral commission from user deposit',
            ]);
        });
    }
}
