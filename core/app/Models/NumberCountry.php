<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumberCountry extends Model
{
    protected $table = 'number_countries';

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

    public function numberApi(): BelongsTo
    {
        return $this->belongsTo(NumberApi::class, 'api_id');
    }
}
