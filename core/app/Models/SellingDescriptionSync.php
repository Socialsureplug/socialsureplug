<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellingDescriptionSync extends Model
{
    protected $table = 'selling_description_syncs';

    protected $fillable = [
        'api_id',
        'status',
        'total_products',
        'processed_products',
        'success_count',
        'failed_count',
        'output_path',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'api_id' => 'integer',
            'total_products' => 'integer',
            'processed_products' => 'integer',
            'success_count' => 'integer',
            'failed_count' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function sellingApi(): BelongsTo
    {
        return $this->belongsTo(SellingApi::class, 'api_id');
    }
}
