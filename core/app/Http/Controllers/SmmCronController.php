<?php

namespace App\Http\Controllers;

use App\Models\SmmOrder;
use App\Models\SmmProvider;
use App\Models\SmmService;
use App\Services\SmmApiService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SmmCronController extends Controller
{
    public function __construct(
        protected SmmApiService $smmApi
    ) {}

    /**
     * Send awaiting SMM orders to API providers.
     * URL: /cron/smm/send-orders
     */
    public function sendOrders(): Response
    {
        $lock = Cache::lock('smm_cron_send_orders', 60);
        if (!$lock->get()) {
            return response('Order cron already running.', 200);
        }

        try {
            $orders = SmmOrder::where('status', 'awaiting')
                ->where('mode', true)
                ->whereNotNull('api_provider_id')
                ->limit(15)
                ->get();

            if ($orders->isEmpty()) {
                return response("No orders to send.\n", 200);
            }

            foreach ($orders as $order) {
                $provider = SmmProvider::find($order->api_provider_id);
                if (!$provider) {
                    $order->update(['status' => 'error', 'note' => 'API Provider does not exist']);
                    $order->fresh()->refundIfEligible('error');
                    continue;
                }

                $post = [
                    'service' => $order->api_service_id,
                    'link' => $order->link,
                    'quantity' => $order->quantity,
                ];
                $response = $this->smmApi->order($provider->only('url', 'key'), $post);

                if ($response && isset($response['order'])) {
                    $order->update([
                        'api_order_id' => (string) $response['order'],
                        'status' => 'inprogress',
                        'note' => null,
                    ]);
                } else {
                    $error = $response['error'] ?? $response['message'] ?? 'Unknown API error';
                    $order->update([
                        'status' => 'error',
                        'note' => is_string($error) ? $error : json_encode($error),
                    ]);
                    $order->fresh()->refundIfEligible('error');
                    Log::warning('SMM cron order failed', ['order_id' => $order->id, 'response' => $response]);
                }
            }

            return response("Successfully\n", 200);
        } finally {
            $lock->release();
        }
    }

    /**
     * Sync status of in-progress orders from API providers.
     * URL: /cron/smm/sync-status
     */
    public function syncStatus(): Response
    {
        $lock = Cache::lock('smm_cron_sync_status', 60);
        if (!$lock->get()) {
            return response('Status cron already running.', 200);
        }

        try {
            $orders = SmmOrder::whereIn('status', ['awaiting', 'inprogress'])
                ->whereNotNull('api_order_id')
                ->whereNotNull('api_provider_id')
                ->limit(15)
                ->get();

            if ($orders->isEmpty()) {
                return response("No orders to update.\n", 200);
            }

            foreach ($orders as $order) {
                $provider = SmmProvider::find($order->api_provider_id);
                if (!$provider) {
                    continue;
                }

                try {
                    $response = $this->smmApi->status($provider->only('url', 'key'), $order->api_order_id);
                    if (!$response) {
                        continue;
                    }

                    $status = $this->mapApiStatusToOrder($response['status'] ?? '');
                    $order->update([
                        'status' => $status,
                        // Both columns are unsigned in the DB; some providers report a
                        // negative remains/start_count on error/over-delivery, which
                        // would otherwise throw and abort the whole batch (see log
                        // history for order #159 blocking every run for weeks).
                        'start_counter' => isset($response['start_count']) ? max(0, (int) $response['start_count']) : $order->start_counter,
                        'remains' => isset($response['remains']) ? max(0, (int) $response['remains']) : $order->remains,
                        'note' => $response['note'] ?? $order->note,
                    ]);
                    $order->fresh()->refundIfEligible($status);
                } catch (\Throwable $e) {
                    Log::error('SMM cron status sync failed for one order, continuing with the rest', [
                        'order_id' => $order->id,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            return response("Successfully\n", 200);
        } finally {
            $lock->release();
        }
    }

    /**
     * Sync services and balance for a few providers.
     * URL: /cron/smm/sync-services
     */
    public function syncServices(): Response
    {
        $lock = Cache::lock('smm_cron_sync_services', 120);
        if (!$lock->get()) {
            return response('Sync services cron already running.', 200);
        }

        try {
            $providers = SmmProvider::where('status', 1)->limit(3)->get();
            if ($providers->isEmpty()) {
                return response("No providers to sync.\n", 200);
            }

            foreach ($providers as $provider) {
                $apiServices = $this->smmApi->services($provider->only('url', 'key'));
                if (!is_array($apiServices)) {
                    continue;
                }

                foreach ($apiServices as $apiRow) {
                    $apiServiceId = (string) ($apiRow['service'] ?? $apiRow['id'] ?? '');
                    if ($apiServiceId === '') {
                        continue;
                    }
                    $updates = [];
                    if (isset($apiRow['name'])) {
                        $updates['name'] = $apiRow['name'];
                    }
                    if (isset($apiRow['min'])) {
                        $updates['min'] = (int) $apiRow['min'];
                    }
                    if (isset($apiRow['max'])) {
                        $updates['max'] = (int) $apiRow['max'];
                    }
                    if ($updates !== []) {
                        SmmService::where('api_provider_id', $provider->id)
                            ->where('api_service_id', $apiServiceId)
                            ->update($updates);
                    }
                }

                $balanceResult = $this->smmApi->balance($provider->only('url', 'key'));
                if ($balanceResult && isset($balanceResult['balance'])) {
                    $provider->update(['balance' => $balanceResult['balance']]);
                }
            }

            return response("Successfully\n", 200);
        } finally {
            $lock->release();
        }
    }

    private function mapApiStatusToOrder(string $apiStatus): string
    {
        $s = strtolower(trim($apiStatus));
        if (in_array($s, ['completed', 'complete', 'delivered'], true)) {
            return 'completed';
        }
        if (in_array($s, ['partial', 'partially'], true)) {
            return 'partial';
        }
        if (in_array($s, ['canceled', 'cancelled', 'cancel'], true)) {
            return 'canceled';
        }
        if (in_array($s, ['in progress', 'inprogress', 'processing', 'pending'], true)) {
            return 'inprogress';
        }
        if (in_array($s, ['error', 'fail', 'failed'], true)) {
            return 'error';
        }
        return 'inprogress';
    }
}
