<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CronController extends Controller
{
    /**
     * Processes database queue jobs (number service imports, etc.).
     */
    public function queueWork(): Response
    {
        $lock = Cache::lock('cron_queue_work', 55);

        if (!$lock->get()) {
            return response("Queue cron already running.\n", 200);
        }

        try {
            $exitCode = Artisan::call('queue:work', [
                'connection' => 'database',
                '--stop-when-empty' => true,
                '--max-time' => 50,
                '--sleep' => 3,
                '--tries' => 1,
                '--timeout' => 600,
            ]);

            $output = trim(Artisan::output());

            if ($output !== '') {
                return response("Exit: {$exitCode}\n{$output}\n", 200);
            }

            return response($exitCode === 0 ? "OK\n" : "Finished with code {$exitCode}\n", 200);
        } catch (\Throwable $e) {
            return response('Error: ' . $e->getMessage() . "\n", 500);
        } finally {
            $lock->release();
        }
    }
}
