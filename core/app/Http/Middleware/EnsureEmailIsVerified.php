<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $settings = Setting::first();
        
        // Only check if email verification is enabled
        if ($settings && $settings->email_verification == 1) {
            $user = $request->user();
            
            // Check if user email is not verified
            if ($user && is_null($user->email_verified_at)) {
                // Store user ID in session
                session(['user' => $user->id]);
                
                return redirect()->route('user.verify-email')
                    ->with('error', 'Please verify your email to continue.');
            }
        }
        
        return $next($request);
    }
}