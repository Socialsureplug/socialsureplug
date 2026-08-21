<?php

namespace App\Jobs;

use App\Models\RentingApi;
use App\Models\RentingCountry;
use App\Models\RentingSyncRun;
use App\Services\RentalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportRentingServicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const CHUNK_SIZE = 5;
    public const DELAY_BETWEEN_COUNTRIES = 2;
    public const DELAY_BETWEEN_CHUNKS = 3;

    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(
        public int $syncRunId,
        public int $chunkIndex = 0,
        public string $dtype = 'week',
        public int $dcount = 1,
    ) {}

    public function handle(RentalService $rentalService): void
    {
        $run = RentingSyncRun::find($this->syncRunId);

        if (!$run || $run->isFinished()) {
            return;
        }

        $api = RentingApi::find($run->api_id);
        if (!$api) {
            $run->markFailed('API not found.');
            return;
        }

        $countryIds = $this->countryIdsForRun($run);
        if ($countryIds === []) {
            $run->markFailed('No active countries. Import countries first.');
            return;
        }

        if ($this->chunkIndex === 0) {
            $run->update([
                'status' => RentingSyncRun::STATUS_RUNNING,
                'phase' => RentingSyncRun::PHASE_PROCESSING,
                'total_items' => count($countryIds),
                'processed_items' => 0,
                'imported_count' => 0,
                'skipped_count' => 0,
                'started_at' => $run->started_at ?? now(),
            ]);
        }

        $offset = $this->chunkIndex * self::CHUNK_SIZE;
        $chunkIds = array_slice($countryIds, $offset, self::CHUNK_SIZE);

        if ($chunkIds === []) {
            $run->markCompleted();
            return;
        }

        foreach ($chunkIds as $i => $countryId) {
            if ($i > 0) {
                sleep(self::DELAY_BETWEEN_COUNTRIES);
            }

            $result = $rentalService->importServices($api, $countryId, $this->dtype, $this->dcount);

            if ($result['success']) {
                $run->addCounts($result['imported'] ?? 0, $result['skipped'] ?? 0);
                if (isset($result['updated'])) {
                    $run->addCounts((int) $result['updated'], 0);
                }
            }

            $run->incrementProcessed(1);
        }

        $run->refresh();
        $hasMore = $run->processed_items < count($countryIds);

        if ($hasMore) {
            self::dispatch($this->syncRunId, $this->chunkIndex + 1, $this->dtype, $this->dcount)
                ->delay(now()->addSeconds(self::DELAY_BETWEEN_CHUNKS));
            return;
        }

        $run->markCompleted();
    }

    protected function countryIdsForRun(RentingSyncRun $run): array
    {
        $query = RentingCountry::where('api_id', $run->api_id)->where('status', 1);

        if ($run->country_id) {
            $query->where('id', $run->country_id);
        }

        return $query->orderBy('id')->pluck('id')->all();
    }

    public function failed(\Throwable $e): void
    {
        RentingSyncRun::find($this->syncRunId)?->markFailed($e->getMessage());
    }
}
