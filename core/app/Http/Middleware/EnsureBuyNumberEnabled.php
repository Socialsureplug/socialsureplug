<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBuyNumberEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = Setting::first();
        if (!$settings || (int) $settings->buy_number === 0) {
            if ($request->is('backend/*')) {
                return redirect()->route('backend.dashboard')
                    ->with('error', 'Buy Number feature is currently disabled.');
            }
            return redirect()->route('user.dashboard')
                ->with('error', 'Buy Number feature is currently disabled.');
        }
        return $next($request);
    }
}
