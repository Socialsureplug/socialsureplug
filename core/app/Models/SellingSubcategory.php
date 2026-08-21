<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellingSubcategory extends Model
{
    protected $table = 'selling_subcategories';

    protected $fillable = [
        'selling_category_id',
        'name',
        'slug',
        'subcategory_id',
        'api_id',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'selling_category_id' => 'integer',
            'subcategory_id' => 'integer',
            'api_id' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function sellingCategory(): BelongsTo
    {
        return $this->belongsTo(SellingCategory::class, 'selling_category_id');
    }

    public function sellingApi(): BelongsTo
    {
        return $this->belongsTo(SellingApi::class, 'api_id');
    }

    public function sellingProducts(): HasMany
    {
        return $this->hasMany(SellingProduct::class, 'subcategory_id');
    }
}
