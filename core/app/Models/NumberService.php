<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumberService extends Model
{
    protected $table = 'number_services';

    protected $fillable = [
        'name',
        'code',
        'country_id',
        'api_id',
        'provider_price',
        'price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
            'api_id' => 'integer',
            'provider_price' => 'decimal:2',
            'price' => 'decimal:2',
            'status' => 'integer',
        ];
    }

    public function numberApi(): BelongsTo
    {
        return $this->belongsTo(NumberApi::class, 'api_id');
    }

    public function numberCountry(): BelongsTo
    {
        return $this->belongsTo(NumberCountry::class, 'country_id');
    }
}
