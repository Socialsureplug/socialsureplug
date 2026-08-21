<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellingCategory extends Model
{
    protected $table = 'selling_categories';

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'api_id',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'api_id' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function sellingApi(): BelongsTo
    {
        return $this->belongsTo(SellingApi::class, 'api_id');
    }

    public function sellingSubcategories(): HasMany
    {
        return $this->hasMany(SellingSubcategory::class, 'selling_category_id');
    }
}
