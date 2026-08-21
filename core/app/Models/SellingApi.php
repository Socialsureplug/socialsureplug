<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellingApi extends Model
{
    protected $table = 'selling_api';

    protected $fillable = [
        'name',
        'label',
        'base_url',
        'api_key',
        'status',
    ];

    /**
     * Hide the key when the model is cast to array / JSON (e.g. API responses).
     */
    protected $hidden = [
        'api_key',
    ];

    public function sellingProducts(): HasMany
    {
        return $this->hasMany(SellingProduct::class, 'api_id');
    }
}