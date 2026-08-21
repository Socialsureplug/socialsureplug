<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentingOrder extends Model
{
    protected $table = 'renting_orders';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_RESTORED = 'restored';

    protected $fillable = [
        'user_id',
        'order_id',
        'api_id',
        'country_id',
        'service_id',
        'provider_order_id',
        'phone',
        'service_name',
        'service_code',
        'country_code',
        'dtype',
        'dcount',
        'provider',
        'price',
        'cost',
        'status',
        'activated_at',
        'expires_at',
        'can_prolong_max',
        'can_prolong_until',
        'sms_messages',
        'rented_at',
        'refunded',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'api_id' => 'integer',
            'country_id' => 'integer',
            'service_id' => 'integer',
            'provider_order_id' => 'integer',
            'dcount' => 'integer',
            'price' => 'decimal:2',
            'can_prolong_max' => 'integer',
            'sms_messages' => 'array',
            'refunded' => 'boolean',
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'can_prolong_until' => 'datetime',
            'rented_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rentingApi(): BelongsTo
    {
        return $this->belongsTo(RentingApi::class, 'api_id');
    }

    public function rentingCountry(): BelongsTo
    {
        return $this->belongsTo(RentingCountry::class, 'country_id');
    }

    public function rentingService(): BelongsTo
    {
        return $this->belongsTo(RentingService::class, 'service_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function isActivated(): bool
    {
        return $this->activated_at !== null;
    }
}
