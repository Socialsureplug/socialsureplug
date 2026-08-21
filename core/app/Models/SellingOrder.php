<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellingOrder extends Model
{
    protected $table = 'selling_orders';

    protected $fillable = [
        'user_id',
        'selling_product_id',
        'quantity',
        'price',
        'total_amount',
        'cost',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'selling_product_id' => 'integer',
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sellingProduct(): BelongsTo
    {
        return $this->belongsTo(SellingProduct::class, 'selling_product_id');
    }

    public function sellingAccounts(): HasMany
    {
        return $this->hasMany(SellingAccount::class, 'selling_order_id');
    }
}
