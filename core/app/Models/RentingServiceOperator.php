<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentingServiceOperator extends Model
{
    protected $table = 'renting_service_operators';

    protected $fillable = [
        'service_id',
        'operator_id',
        'provider_price',
        'price',
        'stock',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'service_id' => 'integer',
            'operator_id' => 'integer',
            'provider_price' => 'decimal:2',
            'price' => 'decimal:2',
            'stock' => 'integer',
            'status' => 'integer',
        ];
    }

    public function rentingService(): BelongsTo
    {
        return $this->belongsTo(RentingService::class, 'service_id');
    }

    public function rentingOperator(): BelongsTo
    {
        return $this->belongsTo(RentingOperator::class, 'operator_id');
    }
}
