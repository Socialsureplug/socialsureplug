<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentingOperator extends Model
{
    protected $table = 'renting_operators';

    protected $fillable = [
        'api_id',
        'country_id',
        'name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'api_id' => 'integer',
            'country_id' => 'integer',
            'status' => 'integer',
        ];
    }

    public function rentingApi(): BelongsTo
    {
        return $this->belongsTo(RentingApi::class, 'api_id');
    }

    public function rentingCountry(): BelongsTo
    {
        return $this->belongsTo(RentingCountry::class, 'country_id');
    }

    public function serviceOperators(): HasMany
    {
        return $this->hasMany(RentingServiceOperator::class, 'operator_id');
    }
}
