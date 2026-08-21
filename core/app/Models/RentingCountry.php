<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentingCountry extends Model
{
    protected $table = 'renting_countries';

    protected $fillable = [
        'name',
        'code',
        'country_id',
        'api_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'api_id' => 'integer',
            'status' => 'integer',
        ];
    }

    public function rentingApi(): BelongsTo
    {
        return $this->belongsTo(RentingApi::class, 'api_id');
    }
}
