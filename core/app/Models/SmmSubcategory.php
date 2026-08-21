<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmmSubcategory extends Model
{
    protected $fillable = ['smm_category_id', 'name', 'sort'];

    public function smmCategory(): BelongsTo
    {
        return $this->belongsTo(SmmCategory::class, 'smm_category_id');
    }

    public function smmServices(): HasMany
    {
        return $this->hasMany(SmmService::class, 'smm_subcategory_id');
    }
}