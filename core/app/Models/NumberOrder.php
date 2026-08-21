<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumberOrder extends Model
{
    protected $table = 'number_orders';

    public const STATUS_WAITING_SMS = 'waiting_sms';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'order_id',
        'api_id',
        'country_id',
        'service_id',
        'activation_id',
        'phone',
        'service_name',
        'service_code',
        'country_code',
        'price',
        'cost',
        'status',
        'sms_code',
        'purchased_at',
        'expires_at',
        'refunded',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'api_id' => 'integer',
            'country_id' => 'integer',
            'service_id' => 'integer',
            'price' => 'decimal:2',
            'refunded' => 'boolean',
            'purchased_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function numberApi(): BelongsTo
    {
        return $this->belongsTo(NumberApi::class, 'api_id');
    }

    public function numberCountry(): BelongsTo
    {
        return $this->belongsTo(NumberCountry::class, 'country_id');
    }

    public function numberService(): BelongsTo
    {
        return $this->belongsTo(NumberService::class, 'service_id');
    }

    public function isWaitingSms(): bool
    {
        return $this->status === self::STATUS_WAITING_SMS;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_WAITING_SMS
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
