<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserPaymentEnabled
{
    /**
     * When user_payment is 0: block payment page and payment actions (redirect back with error).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = Setting::first();

        if (! $settings || (int) $settings->user_payment === 0) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Payment is currently disabled by administrator.');
        }

        return $next($request);
    }
}
