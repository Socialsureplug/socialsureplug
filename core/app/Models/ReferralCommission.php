<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralCommission extends Model
{
    protected $fillable = [
        'referrer_user_id',
        'referred_user_id',
        'payment_id',
        'deposit_amount',
        'commission_percent',
        'commission_amount',
        'reference',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'deposit_amount' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
