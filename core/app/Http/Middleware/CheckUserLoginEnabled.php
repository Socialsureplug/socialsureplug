<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserLoginEnabled
{
    /**
     * When user_login is 0: block login submit (return back with error).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = Setting::first();

        if (! $settings || (int) $settings->user_login === 0) {
            if ($request->isMethod('post')) {
                return back()->with('error', 'User login is currently disabled. Please contact administrator.');
            }

            return redirect()->route('user.login.page')
                ->with('error', 'User login is currently disabled. Please contact administrator.');
        }

        return $next($request);
    }
}
