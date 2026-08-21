<?php

use App\Http\Controllers\Api\V1\HandlerApiController;
use App\Http\Controllers\Api\V1\SmmApiV1Controller;
use App\Http\Controllers\CronController;
use App\Http\Controllers\Frontend\SiteController;
use App\Http\Controllers\SmmCronController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\PaymentLogController;

Route::get('/', [SiteController::class, 'homePage'])->name('home');
Route::get('/terms', [SiteController::class, 'terms'])->name('frontend.terms');
Route::get('/privacy', [SiteController::class, 'privacy'])->name('frontend.privacy');
Route::get('/support', [SiteController::class, 'support'])->name('frontend.support');

Route::prefix('cron')->name('cron.')->group(function () {
    Route::get('queue-work', [CronController::class, 'queueWork'])->name('queue-work');
});

// SMM cron URLs
Route::prefix('cron/smm')->name('cron.smm.')->group(function () {
    Route::get('send-orders', [SmmCronController::class, 'sendOrders'])->name('send-orders');
    Route::get('sync-status', [SmmCronController::class, 'syncStatus'])->name('sync-status');
    Route::get('sync-services', [SmmCronController::class, 'syncServices'])->name('sync-services');
});

Route::prefix('api/v1')->group(function () {
    Route::get('balance',    [HandlerApiController::class, 'getBalance']);
    Route::get('servers',    [HandlerApiController::class, 'getServers']);
    Route::get('get-number', [HandlerApiController::class, 'getNumber']);
    Route::get('status',     [HandlerApiController::class, 'getStatus']);
    Route::get('set-status', [HandlerApiController::class, 'setStatus']);
    Route::get('countries',  [HandlerApiController::class, 'getCountries']);
    Route::get('services',   [HandlerApiController::class, 'getServices']);
});

Route::match(['get', 'post'], 'api/smm/v1', [SmmApiV1Controller::class, 'handle'])->name('api.smm.v1');


Route::patch('payments/{payment}/mark-success', [PaymentLogController::class, 'markSuccess'])
    ->name('backend.payments.mark-success');