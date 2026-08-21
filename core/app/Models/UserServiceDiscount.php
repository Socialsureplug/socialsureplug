<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserServiceDiscount extends Model
{
    public const SERVICE_VIRTUAL_NUMBER = 'virtual_number';
    public const SERVICE_RENT_NUMBER = 'rent_number';
    public const SERVICE_SMM = 'smm';
    public const SERVICE_ACCOUNT_SELLING = 'account_selling';

    public const SERVICES = [
        self::SERVICE_VIRTUAL_NUMBER => 'Virtual Number',
        self::SERVICE_RENT_NUMBER => 'Rent Number',
        self::SERVICE_SMM => 'SMM',
        self::SERVICE_ACCOUNT_SELLING => 'Account Selling',
    ];

    protected $fillable = [
        'user_id',
        'service',
        'discount_percent',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
        ];
    }

    protected static array $percentCache = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function percentFor(?int $userId, string $service): float
    {
        if (!$userId) {
            return 0.0;
        }

        $key = $userId . ':' . $service;

        if (!array_key_exists($key, static::$percentCache)) {
            static::$percentCache[$key] = (float) (static::where('user_id', $userId)
                ->where('service', $service)
                ->value('discount_percent') ?? 0);
        }

        return static::$percentCache[$key];
    }

    public static function apply(?int $userId, string $service, float $price): float
    {
        $percent = static::percentFor($userId, $service);

        if ($percent <= 0) {
            return $price;
        }

        $discounted = $price - ($price * $percent / 100);

        return round(max($discounted, 0), 2);
    }
}
