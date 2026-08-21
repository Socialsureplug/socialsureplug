<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmmProvider extends Model
{
    protected $fillable = [
        'name', 'url', 'key', 'description', 'balance', 'currency_code', 'currency_rate', 'status',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'currency_rate' => 'decimal:4',
            'status' => 'integer',
        ];
    }

    public function smmServices(): HasMany
    {
        return $this->hasMany(SmmService::class, 'api_provider_id');
    }

    public function smmOrders(): HasMany
    {
        return $this->hasMany(SmmOrder::class, 'api_provider_id');
    }
}