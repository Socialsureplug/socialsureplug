<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    protected $fillable = ['title', 'url', 'status', 'sort_order'];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
