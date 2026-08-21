<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SmmService extends Model
{
    protected $fillable = [
        'name',
        'smm_category_id',
        'api_provider_id',
        'api_service_id',
        'smm_subcategory_id',
        'min',
        'max',
        'average_time_minutes',
        'price',
        'original_price',
        'type',
        'add_type',
        'dripfeed',
        'desc',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'min' => 'integer',
            'max' => 'integer',
            'average_time_minutes' => 'integer',
            'price' => 'decimal:4',
            'original_price' => 'decimal:4',
            'dripfeed' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function smmCategory(): BelongsTo
    {
        return $this->belongsTo(SmmCategory::class, 'smm_category_id');
    }

    public function smmProvider(): BelongsTo
    {
        return $this->belongsTo(SmmProvider::class, 'api_provider_id');
    }

    public function smmOrders(): HasMany
    {
        return $this->hasMany(SmmOrder::class, 'smm_service_id');
    }

    public function smmSubcategory(): BelongsTo
    {
        return $this->belongsTo(SmmSubcategory::class, 'smm_subcategory_id');
    }
}
