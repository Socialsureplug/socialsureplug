<?php

namespace App\Jobs;

use App\Models\SellingApi;
use App\Models\SellingDescriptionSync;
use App\Models\SellingProduct;
use App\Services\Selling\AccszoneService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class SyncAccsbulkDescriptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(public int $syncId)
    {
    }

    public function handle(): void
    {
        $sync = SellingDescriptionSync::query()->findOrFail($this->syncId);
        $api = SellingApi::query()->findOrFail((int) $sync->api_id);

        if (strtolower(trim((string) $api->label)) !== 'accsbulk') {
            throw new RuntimeException('Description sync is only supported for AccsBulk APIs.');
        }

        $sync->update([
            'status' => 'running',
            'started_at' => now(),
            'finished_at' => null,
            'error_message' => null,
            'processed_products' => 0,
            'success_count' => 0,
            'failed_count' => 0,
        ]);

        $service = AccszoneService::fromSellingApi($api);

        $query = SellingProduct::query()
            ->where('api_id', $api->id)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select(['id', 'provider_id', 'title', 'slug', 'description']);

        $total = (clone $query)->count();
        $sync->update(['total_products' => $total]);

        $processed = 0;
        $success = 0;
        $failed = 0;

        try {
            $query
                ->orderBy('id')
                ->chunkById(100, function ($products) use (
                    $service,
                    &$processed,
                    &$success,
                    &$failed,
                    $sync
                ) {
                    foreach ($products as $product) {
                        /** @var SellingProduct $product */
                        $result = $service->fetchDescriptionForProduct($product);

                        $product->update(['description' => $result['description'] ?? null]);

                        $processed++;
                        if (!empty($result['success'])) {
                            $success++;
                        } else {
                            $failed++;
                        }
                    }

                    $sync->update([
                        'processed_products' => $processed,
                        'success_count' => $success,
                        'failed_count' => $failed,
                    ]);
                });

            $sync->update([
                'status' => 'completed',
                'processed_products' => $processed,
                'success_count' => $success,
                'failed_count' => $failed,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $sync->update([
                'status' => 'failed',
                'processed_products' => $processed,
                'success_count' => $success,
                'failed_count' => $failed,
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            throw $e;
        }
    }
}
