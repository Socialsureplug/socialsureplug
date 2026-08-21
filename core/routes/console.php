<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('http:install-ca-cert', function () {
    $url = 'https://curl.se/ca/cacert.pem';
    $path = storage_path('app/cacert.pem');

    $this->info('Downloading CA certificate bundle from curl.se ...');

    try {
        $content = Http::timeout(30)
            ->withOptions(['verify' => false])
            ->get($url)
            ->body();
    } catch (\Throwable $e) {
        $this->error('Download failed: ' . $e->getMessage());
        return 1;
    }

    if (strlen($content) < 1000 || !str_contains($content, '-----BEGIN CERTIFICATE-----')) {
        $this->error('Downloaded content does not look like a CA bundle.');
        return 1;
    }

    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, $content);
    $this->info('Saved to: ' . $path);
    $this->info('HTTPS requests will now use this bundle for SSL verification.');
    return 0;
})->purpose('Download CA certificate bundle to fix cURL SSL errors (run once)');
