<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEmailTemplate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'meaning' => 'array',
    ];
}