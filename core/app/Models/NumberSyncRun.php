<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumberSyncRun extends Model
{
    protected $table = 'number_sync_runs';

    public const TYPE_SERVICES = 'services';
    public const TYPE_COUNTRIES = 'countries';

    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const PHASE_FETCHING = 'fetching';
    public const PHASE_PROCESSING = 'processing';

    protected $fillable = [
        'api_id',
        'country_id',
        'type',
        'status',
        'phase',
        'total_items',
        'processed_items',
        'imported_count',
        'skipped_count',
        'error_message',
        'admin_id',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'api_id' => 'integer',
            'country_id' => 'integer',
            'admin_id' => 'integer',
            'total_items' => 'integer',
            'processed_items' => 'integer',
            'imported_count' => 'integer',
            'skipped_count' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function numberApi(): BelongsTo
    {
        return $this->belongsTo(NumberApi::class, 'api_id');
    }

    public function numberCountry(): BelongsTo
    {
        return $this->belongsTo(NumberCountry::class, 'country_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function isQueued(): bool
    {
        return $this->status === self::STATUS_QUEUED;
    }

    public function isRunning(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED], true);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_QUEUED, self::STATUS_RUNNING], true);
    }

    public function progressPercent(): int
    {
        if ($this->total_items <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->processed_items / $this->total_items) * 100));
    }

    public function progressLabel(): string
    {
        return "{$this->processed_items}/{$this->total_items}";
    }

    public function markRunning(?string $phase = null): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'phase' => $phase,
            'started_at' => $this->started_at ?? now(),
        ]);
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'phase' => null,
            'processed_items' => $this->total_items > 0 ? $this->total_items : $this->processed_items,
            'finished_at' => now(),
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'phase' => null,
            'error_message' => $message,
            'finished_at' => now(),
        ]);
    }

    public function incrementProcessed(int $by = 1): void
    {
        $this->increment('processed_items', $by);
    }

    public function addCounts(int $imported = 0, int $skipped = 0): void
    {
        if ($imported > 0) {
            $this->increment('imported_count', $imported);
        }
        if ($skipped > 0) {
            $this->increment('skipped_count', $skipped);
        }
    }
}
