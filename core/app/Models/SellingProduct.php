<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellingProduct extends Model
{
    protected $table = 'selling_product';

    protected $fillable = [
        'provider_id',
        'title',
        'description',
        'slug',
        'provider_price',
        'price',
        'stock',
        'sold',
        'api_id',
        'subcategory_id',
        'status',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'provider_id' => 'integer',
            'provider_price' => 'decimal:2',
            'price' => 'decimal:2',
            'stock' => 'integer',
            'sold' => 'integer',
            'subcategory_id' => 'integer',
            'status' => 'integer',
        ];
    }

    public function sellingApi(): BelongsTo
    {
        return $this->belongsTo(SellingApi::class, 'api_id');
    }

    public function sellingSubcategory(): BelongsTo
    {
        return $this->belongsTo(SellingSubcategory::class, 'subcategory_id');
    }

    public function isAccsbulk(): bool
    {
        if ($this->api_id === null) {
            return false;
        }

        $this->loadMissing('sellingApi');

        return strcasecmp((string) optional($this->sellingApi)->label, 'accsbulk') === 0;
    }

    public function sellingAccounts(): HasMany
    {
        return $this->hasMany(SellingAccount::class, 'product_id');
    }

    public function sellingOrders(): HasMany
    {
        return $this->hasMany(SellingOrder::class, 'selling_product_id');
    }
}
