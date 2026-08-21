<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'method',
        'currency',
        'amount',
        'reference',
        'status',
        'channel',
        'gateway_response',
        'proof_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}