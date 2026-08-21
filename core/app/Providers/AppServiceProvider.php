<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\Tutorial;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share settings with backend and frontend views so layouts can use $settings safely
        View::composer(['backend.*', 'frontend.*'], function ($view) {
            $view->with('settings', Setting::first());
        });

        // Share active tutorials for frontend accordion
        View::composer('frontend.*', function ($view) {
            $view->with('tutorials', Tutorial::active()->orderBy('sort_order')->orderBy('id')->get());
        });
    }
}
