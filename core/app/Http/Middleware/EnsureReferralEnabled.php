<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureReferralEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = Setting::first();
        if (! $settings || (int) ($settings->referral_enabled ?? 0) === 0) {
            if ($request->is('backend/*')) {
                return redirect()->route('backend.dashboard')
                    ->with('error', 'Referral feature is currently disabled.');
            }

            return redirect()->route('user.dashboard')
                ->with('error', 'Referral feature is currently disabled.');
        }

        return $next($request);
    }
}
