<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'gateway_parameters' => 'object',
        'user_proof_param' => 'array',
        'rate' => 'decimal:8',
        'charge' => 'decimal:8',
        'min_payment' => 'decimal:2',
        'max_payment' => 'decimal:2',
    ];
}