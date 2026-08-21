<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberSetting extends Model
{
    protected $table = 'number_settings';

    protected $fillable = [
        'currency_rate',
        'price_increase',
    ];

    protected function casts(): array
    {
        return [
            'currency_rate' => 'decimal:2',
            'price_increase' => 'decimal:2',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'currency_rate' => 1.00,
                'price_increase' => 0.00,
            ]
        );
    }
}
