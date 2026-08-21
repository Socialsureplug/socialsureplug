<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function loginPage()
    {
        $title = 'Admin Login';

        return view('backend.auth.login', compact('title'));
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $remember = $request->has('remember') ? true : false;

        if (auth()->guard('admin')->attempt($data, $remember)) {
            $admin = auth()->guard('admin')->user();
            try {
                sendAdminEmail('admin_login', [
                    'login_time' => now()->format('Y-m-d H:i:s'),
                    'ip_address' => request()->ip(),
                ], $admin);
            } catch (\Exception $e) {
                Log::error('Admin login email failed: ' . $e->getMessage());
            }
            return redirect()->route('backend.dashboard')->with('success', 'Login successful');
        }

        return redirect()->route('backend.login')->with('error', 'Invalid credentials');
    }

    public function logout()
    {
        auth()->guard('admin')->logout();
        return redirect()->route('backend.login')->with('success', 'Logout successful');
    }

    public function forgotPasswordPage()
    {
        $pageTitle = 'Forgot Password';

        return view('backend.auth.forgot-password', compact('pageTitle'));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:admins,email',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return redirect()->route('backend.forgot-password')->with('error', 'No admin found with this email address.');
        }

        $resetCode = (string) rand(100000, 999999);
        $admin->password_reset_token = $resetCode;
        $admin->password_reset_expires = now()->addHours(1);
        $admin->save();

        try {
            sendAdminEmail('admin_password_reset', [
                'reset_code' => $resetCode,
                'expires_in' => '1 hour',
            ], $admin);
        } catch (\Exception $e) {
            Log::error('Failed to send admin password reset email: ' . $e->getMessage());
        }

        return redirect()->route('backend.reset-password', ['email' => $admin->email])
            ->with('success', 'Password reset code has been sent to your email address.');
    }

    public function resetPasswordPage(Request $request)
    {
        $pageTitle = 'Reset Password';
        $email = $request->get('email', '');

        return view('backend.auth.reset-password', compact('pageTitle', 'email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:admins,email',
            'code' => 'required|numeric|digits:6',
            'password' => 'required|min:6|confirmed',
        ]);

        $admin = Admin::where('email', $request->email)
            ->where('password_reset_token', $request->code)
            ->where('password_reset_expires', '>', now())
            ->first();

        if (!$admin) {
            return redirect()->route('backend.reset-password')->with('error', 'Invalid or expired password reset code.');
        }

        $admin->password = $request->password;
        $admin->password_reset_token = null;
        $admin->password_reset_expires = null;
        $admin->save();

        return redirect()->route('backend.login')->with('success', 'Your password has been reset successfully. Please login with your new password.');
    }
}
