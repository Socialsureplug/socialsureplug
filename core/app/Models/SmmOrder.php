<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmmOrder extends Model
{
    protected $fillable = [
        'user_id', 'smm_category_id', 'smm_service_id', 'link', 'quantity', 'charge', 'cost',
        'refunded', 'status', 'api_provider_id', 'api_service_id', 'api_order_id',
        'start_counter', 'remains', 'mode', 'note', 'service_type', 'comments',
        'is_drip_feed', 'runs', 'interval', 'dripfeed_quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'charge' => 'decimal:2',
            'refunded' => 'boolean',
            'start_counter' => 'integer',
            'remains' => 'integer',
            'mode' => 'boolean',
            'is_drip_feed' => 'boolean',
            'runs' => 'integer',
            'interval' => 'integer',
            'dripfeed_quantity' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function smmCategory(): BelongsTo
    {
        return $this->belongsTo(SmmCategory::class, 'smm_category_id');
    }

    public function smmService(): BelongsTo
    {
        return $this->belongsTo(SmmService::class, 'smm_service_id');
    }

    public function smmProvider(): BelongsTo
    {
        return $this->belongsTo(SmmProvider::class, 'api_provider_id');
    }

    public function shouldRefundForStatus(string $newStatus): bool
    {
        if ($this->refunded || (float) $this->charge <= 0) {
            return false;
        }

        if ($newStatus === 'partial') {
            return false;
        }

        if ($newStatus === 'canceled') {
            return true;
        }

        return in_array($newStatus, ['error', 'fail'], true) && empty($this->api_order_id);
    }

    public function refundIfEligible(string $newStatus): bool
    {
        if (!$this->shouldRefundForStatus($newStatus)) {
            return false;
        }

        return $this->refund($newStatus);
    }

    public function refund(string $status): bool
    {
        if ($this->refunded || (float) $this->charge <= 0) {
            return false;
        }

        try {
            DB::transaction(function () use ($status) {
                $order = static::where('id', $this->id)->lockForUpdate()->firstOrFail();
                $order->loadMissing('user', 'smmService');

                if ($order->refunded || (float) $order->charge <= 0 || !$order->user) {
                    return;
                }

                $amount = (float) $order->charge;
                $before = (float) $order->user->balance;
                $order->user->increment('balance', $amount);

                Transaction::create([
                    'user_id' => $order->user_id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'balance_before' => $before,
                    'balance_after' => $before + $amount,
                    'source' => 'smm_refund',
                    'reference' => (string) $order->id,
                    'description' => 'SMM refund #' . $order->id . ': ' . ($order->smmService?->name ?? 'Order'),
                ]);

                $order->update([
                    'status' => $status,
                    'refunded' => true,
                ]);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('SMM refund failed', [
                'order_id' => $this->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}