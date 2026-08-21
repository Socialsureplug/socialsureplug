<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentingService extends Model
{
    protected $table = 'renting_services';

    protected $fillable = [
        'name',
        'code',
        'country_id',
        'api_id',
        'dtype',
        'dcount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
            'api_id' => 'integer',
            'dcount' => 'integer',
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
        return $this->hasMany(RentingServiceOperator::class, 'service_id');
    }
}
