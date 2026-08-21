<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentingApi extends Model
{
    protected $table = 'renting_api';

    protected $fillable = [
        'name',
        'label',
        'slug',
        'base_url',
        'api_key',
        'balance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }
}
