<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberApi extends Model
{
    protected $table = 'number_api';

    protected $fillable = [
        'name',
        'label',
        'slug',
        'base_url',
        'api_key',
        'balance',
        'cancel_code',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    public function providerCancelStatus(): int
    {
        $code = (int) $this->cancel_code;

        return $code !== 0 ? $code : 8;
    }
}
