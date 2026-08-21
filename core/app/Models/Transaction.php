<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'source',
        'reference',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}