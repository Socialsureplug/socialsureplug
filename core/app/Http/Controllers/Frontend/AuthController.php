<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function registerPage($referrer_id = null)
    {
        $data['title'] = 'Register';
        $data['referralId'] = $referrer_id;
        $settings = Setting::first();
        if ($settings && (int) ($settings->recaptcha ?? 0) === 1) {
            $data['captcha'] = $this->generateCaptcha();
        } else {
            $data['captcha'] = null;
        }

        return view('frontend.auth.register', $data);
    }

    public function register(Request $request, $referrer_id = null)
    {
        $request->validate([
            'fname' => 'required',
            'lname' => 'required',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'reffered_by' => 'nullable|integer',
        ]);

        $settings = Setting::first();
        if ($settings && (int) ($settings->recaptcha ?? 0) === 1) {
            if ($request->captcha !== Session::get('captcha')) {
                return redirect()->route('user.register', ['referrer_id' => $referrer_id])->with('error', 'Invalid captcha.');
            }
        }

        $referrerId = (int) ($request->reffered_by ?: $referrer_id ?: 0);
        $referid = null;
        if ($referrerId > 0) {
            $referUser = User::find($referrerId);

            if (! $referUser) {
                $notify[] = ['error', 'No User Found Associated with this referrer Name'];
                return redirect()->route('user.register', ['referrer_id' => $referrer_id])->withNotify($notify);
            }

            $referid = $referUser->id;
        }

        $emailVerificationEnabled = $settings->email_verification ?? 0;
        $verificationCode = $emailVerificationEnabled ? (string) random_int(100000, 999999) : null;

        $user = User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => $request->password,
            'status' => 1,
            'reffered_by' => $referid,
            'verification_code' => $verificationCode,
        ]);

        try {
            sendUserEmail('Welcome Email', [
                'fname' => $user->fname,
                'lname' => $user->lname,
            ], $user);
        } catch (\Exception $e) {
            Log::error('Welcome email failed: ' . $e->getMessage());
        }

        if ($emailVerificationEnabled && $verificationCode) {
            try {
                sendUserEmail('Email Verification', [
                    'code' => $verificationCode,
                ], $user);
            } catch (\Exception $e) {
                Log::error('Email verification email failed: ' . $e->getMessage());
            }
        }

        event(new Registered($user));

        Auth::login($user);
        $notify[] = ['success', 'Successfully Registered'];
        return redirect()->route('user.dashboard')->withNotify($notify);
    }

    public function loginPage()
    {
        $data['title'] = 'Login';
        $settings = Setting::first();
        if ($settings && (int) ($settings->recaptcha ?? 0) === 1) {
            $data['captcha'] = $this->generateCaptcha();
        } else {
            $data['captcha'] = null;
        }

        return view('frontend.auth.login', $data);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $settings = Setting::first();
        if ($settings && (int) ($settings->recaptcha ?? 0) === 1) {
            if ($request->captcha !== Session::get('captcha')) {
                return redirect()->route('user.login.page')->with('error', 'Invalid captcha.');
            }
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return redirect()->route('user.login.page')->with('error', 'No user found associated with this email');
        }

        if ($user->status == 0) {
            return redirect()->route('user.login.page')->with('error', 'Your account has been deactivated. Please contact administrator.');
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();

            $emailVerificationEnabled = $settings->email_verification ?? 0;
            
            // Check if email verification is required
            if ($emailVerificationEnabled && is_null($user->email_verified_at)) {
                if (! $user->verification_code) {
                    $user->verification_code = (string) random_int(100000, 999999);
                    $user->save();
                }
                Auth::logout();
                session(['user' => $user->id]);
                return redirect()->route('user.verify-email')
                    ->with('error', 'Please verify your email first.');
            }

            if ((bool) $user->two_factor_enabled) {
                $this->generateAndSendTwoFactorCode($user);

                Auth::logout();
                $request->session()->put('pending_2fa_user_id', $user->id);
                $request->session()->regenerateToken();

                return redirect()->route('user.two-factor')
                    ->with('success', 'A verification code has been sent to your email.');
            }
            
            return redirect()->route('user.dashboard')->with('success', 'Login successful');
        }

        return redirect()->route('user.login.page')->with('error', 'Invalid credentials');
    }

    public function refreshCaptcha()
    {
        $settings = Setting::first();
        if (! $settings || (int) ($settings->recaptcha ?? 0) !== 1) {
            return response()->json(['message' => 'Captcha disabled'], 403);
        }

        $captchaCode = $this->generateCaptcha();

        return response()->json(['captcha' => $captchaCode]);
    }

    private function generateCaptcha(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $captchaCode = '';
        for ($i = 0; $i < 6; $i++) {
            $captchaCode .= $characters[random_int(0, strlen($characters) - 1)];
        }
        Session::put('captcha', $captchaCode);

        return $captchaCode;
    }

    public function forgotPasswordPage()
    {
        $data['title'] = 'Forgot Password';
        $settings = Setting::first();
        if ($settings && (int) ($settings->recaptcha ?? 0) === 1) {
            $data['captcha'] = $this->generateCaptcha();
        } else {
            $data['captcha'] = null;
        }

        return view('frontend.auth.forgot-password', $data);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $settings = Setting::first();
        if ($settings && (int) ($settings->recaptcha ?? 0) === 1) {
            if ($request->captcha !== Session::get('captcha')) {
                return redirect()->route('user.forgot-password')->with('error', 'Invalid captcha.');
            }
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return redirect()->route('user.forgot-password')->with('error', 'No user found with this email address.');
        }

        $resetCode = (string) random_int(100000, 999999);
        $user->password_reset_token = $resetCode;
        $user->password_reset_expires = now()->addHours(1);
        $user->save();

        try {
            sendUserEmail('Password Reset', [
                'reset_code' => $resetCode,
                'expires_in' => '1 hour',
                'fname' => $user->fname,
            ], $user);
        } catch (\Exception $e) {
            Log::error('Password reset email failed: ' . $e->getMessage());
        }

        return redirect()->route('user.reset-password', ['email' => $user->email])
            ->with('success', 'Password reset code has been sent to your email address.');
    }

    public function resetPasswordPage(Request $request)
    {
        $data['title'] = 'Reset Password';
        $data['email'] = $request->get('email', '');
        $settings = Setting::first();
        if ($settings && (int) ($settings->recaptcha ?? 0) === 1) {
            $data['captcha'] = $this->generateCaptcha();
        } else {
            $data['captcha'] = null;
        }

        return view('frontend.auth.reset-password', $data);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|numeric|digits:6',
            'password' => 'required|min:6|confirmed',
        ]);

        $settings = Setting::first();
        if ($settings && (int) ($settings->recaptcha ?? 0) === 1) {
            if ($request->captcha !== Session::get('captcha')) {
                return redirect()->back()->with('error', 'Invalid captcha.');
            }
        }

        $user = User::where('email', $request->email)
            ->where('password_reset_token', $request->code)
            ->where('password_reset_expires', '>', now())
            ->first();

        if (! $user) {
            return redirect()->route('user.reset-password', ['email' => $request->email])->with('error', 'Invalid or expired password reset code.');
        }

        $user->password = Hash::make($request->password);
        $user->password_reset_token = null;
        $user->password_reset_expires = null;
        $user->save();

        return redirect()->route('user.login.page')
            ->with('success', 'Your password has been reset successfully. Please login with your new password.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user.login.page')->with('success', 'You have been logged out.');
    }

    public function emailVerify()
    {
        $pageTitle = "Email Verify";

        return view('frontend.auth.email', compact('pageTitle'));
    }

    public function emailVerifyConfirm(Request $request)
    {
        $request->validate(['code' => 'required']);

        $user = User::findOrFail(session('user'));

        if($request->code == $user->verification_code) {
            $user->verification_code = null;
            $user->email_verified_at = now();
            $user->status = 1;
            $user->save();

            Auth::login($user);

            $notify[] = ['success','Successfully verify your account'];

            return redirect()->route('user.dashboard')->withNotify($notify);
        }

        $notify[] = ['error','Invalid Code'];

        return back()->withNotify($notify);
    }

    public function resendVerificationCode()
    {
        // Check if user session exists
        if (!session('user')) {
            return redirect()->route('user.login.page')->with('error', 'Session expired. Please login again.');
        }

        $user = User::findOrFail(session('user'));

        // Check if user is already verified
        if ($user->email_verified_at) {
            return redirect()->route('user.login.page')->with('success', 'Your email is already verified. Please login.');
        }

        // Generate new verification code
        $user->verification_code = rand(100000, 999999);
        $user->save();

        // Send verification email
        sendUserEmail('Email Verification', [
            'code' => $user->verification_code,
        ], $user);

        return redirect()->route('user.verify-email')->with('success', 'Verification code has been resent to your email.');
    }

    public function twoFactorPage()
    {
        if (! session('pending_2fa_user_id')) {
            return redirect()->route('user.login.page')->with('error', 'Session expired. Please login again.');
        }

        $settings = Setting::first();

        return view('frontend.auth.two-factor', compact('settings'));
    }

    public function twoFactorConfirm(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $pendingUserId = session('pending_2fa_user_id');
        if (! $pendingUserId) {
            return redirect()->route('user.login.page')->with('error', 'Session expired. Please login again.');
        }

        $user = User::find($pendingUserId);
        if (! $user) {
            session()->forget('pending_2fa_user_id');
            return redirect()->route('user.login.page')->with('error', 'User not found.');
        }

        if (! $user->two_factor_enabled) {
            session()->forget('pending_2fa_user_id');
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->route('user.dashboard')->with('success', 'Login successful');
        }

        if (
            $request->code !== (string) $user->two_factor_code ||
            ! $user->two_factor_expires_at ||
            now()->greaterThan($user->two_factor_expires_at)
        ) {
            return back()->with('error', 'Invalid or expired verification code.');
        }

        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        session()->forget('pending_2fa_user_id');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard')->with('success', 'Login successful');
    }

    public function resendTwoFactorCode()
    {
        $pendingUserId = session('pending_2fa_user_id');
        if (! $pendingUserId) {
            return redirect()->route('user.login.page')->with('error', 'Session expired. Please login again.');
        }

        $user = User::find($pendingUserId);
        if (! $user || ! $user->two_factor_enabled) {
            session()->forget('pending_2fa_user_id');
            return redirect()->route('user.login.page')->with('error', 'Two-factor authentication is not available.');
        }

        $this->generateAndSendTwoFactorCode($user);

        return redirect()->route('user.two-factor')->with('success', 'Verification code resent successfully.');
    }

    private function generateAndSendTwoFactorCode(User $user): void
    {
        $user->two_factor_code = (string) random_int(100000, 999999);
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        try {
            sendUserEmail('Two Factor Authentication', [
                'fname' => $user->fname,
                'code' => $user->two_factor_code,
                'expires_in' => '10 minutes',
            ], $user);
        } catch (\Exception $e) {
            Log::error('Two-factor email failed: ' . $e->getMessage());
        }
    }
}
