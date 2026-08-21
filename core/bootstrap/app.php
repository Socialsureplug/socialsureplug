<?php

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->name('user.')
                ->group(base_path('routes/user.php'));

            Route::middleware('web')
                ->prefix('backend')
                ->name('backend.')
                ->group(base_path('routes/backend.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'payment/korapay/webhook',
            'payment/bachs/webhook',
        ]);

        $middleware->alias([
            'user.status.check' => \App\Http\Middleware\CheckUserStatus::class,
            'user.registration.check' => \App\Http\Middleware\CheckUserRegistrationEnabled::class,
            'user.login.check' => \App\Http\Middleware\CheckUserLoginEnabled::class,
            'user.payment.check' => \App\Http\Middleware\CheckUserPaymentEnabled::class,
            'verified.email' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'ensure.buy_number' => \App\Http\Middleware\EnsureBuyNumberEnabled::class,
            'ensure.buy_rent' => \App\Http\Middleware\EnsureBuyRentEnabled::class,
            'ensure.boost_social' => \App\Http\Middleware\EnsureBoostSocialEnabled::class,
            'ensure.social_logs' => \App\Http\Middleware\EnsureSocialLogsEnabled::class,
            'ensure.referral' => \App\Http\Middleware\EnsureReferralEnabled::class,
        ]);
        // Redirect unauthenticated users to the correct login
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('backend') || $request->is('backend/*')) {
                return route('backend.login');
            }
            return route('user.login.page');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
