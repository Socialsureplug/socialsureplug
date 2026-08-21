<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SmmCategory extends Model
{
    protected $fillable = ['name', 'sort'];

    public function smmServices(): HasMany
    {
        return $this->hasMany(SmmService::class, 'smm_category_id');
    }

    public function smmOrders(): HasMany
    {
        return $this->hasMany(SmmOrder::class, 'smm_category_id');
    }

    public function smmSubcategories(): HasMany
    {
        return $this->hasMany(SmmSubcategory::class, 'smm_category_id')->orderBy('sort');
    }
}
