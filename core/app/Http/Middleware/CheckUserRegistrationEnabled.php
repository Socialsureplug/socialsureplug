<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRegistrationEnabled
{
    /**
     * When user_registration is 0: block register page and submit (redirect to login with error).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = Setting::first();

        if (! $settings || (int) $settings->user_registration === 0) {
            if ($request->isMethod('post')) {
                return back()->with('error', 'User registration is currently disabled by administrator.');
            }

            return redirect()->route('user.login.page')
                ->with('error', 'User registration is currently disabled by administrator.');
        }

        return $next($request);
    }
}
