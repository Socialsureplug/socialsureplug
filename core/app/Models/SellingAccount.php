<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellingAccount extends Model
{
    protected $table = 'selling_accounts';

    protected $fillable = [
        'name',
        'product_id',
        'selling_order_id',
        'credentials',
        'is_sold',
    ];

    protected function casts(): array
    {
        return [
            'is_sold' => 'boolean',
            'selling_order_id' => 'integer',
        ];
    }

    protected $hidden = [
        'credentials',
    ];

    public function sellingProduct(): BelongsTo
    {
        return $this->belongsTo(SellingProduct::class, 'product_id');
    }

    public function sellingOrder(): BelongsTo
    {
        return $this->belongsTo(SellingOrder::class, 'selling_order_id');
    }
}
