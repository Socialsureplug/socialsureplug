@extends('frontend.layout.auth')

@section('content')
<style>
    /* Use site CSS variables - these will be controlled by admin settings */
    .auth-page-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--body-bg, #f8fafc);
        padding: 40px 20px;
        position: relative;
        transition: background 0.3s ease;
    }

    /* Dark mode support via data-theme attribute or .dark class */
    [data-theme="dark"] .auth-page-wrapper,
    .dark .auth-page-wrapper {
        background: var(--body-bg, #0f172a);
    }

    /* Ambient background effects using brand colors */
    .auth-bg-effects {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: none;
    }

    .auth-bg-effects::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, var(--primary-color, #2DBDB4) 0%, transparent 70%);
        opacity: 0.15;
        border-radius: 50%;
        animation: float 8s ease-in-out infinite;
    }

    .auth-bg-effects::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -20%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, var(--secondary-color, #F97316) 0%, transparent 70%);
        opacity: 0.1;
        border-radius: 50%;
        animation: float 8s ease-in-out infinite reverse;
    }

    [data-theme="dark"] .auth-bg-effects::before,
    .dark .auth-bg-effects::before {
        opacity: 0.1;
    }

    @keyframes float {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(20px, -20px) scale(1.05); }
    }

    /* Main Card - Uses site card styles */
    .auth-card {
        width: 100%;
        max-width: 480px;
        background: var(--card-bg, #ffffff);
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 24px;
        padding: 48px 40px;
        position: relative;
        z-index: 10;
        box-shadow: var(--box-shadow, 0 10px 40px rgba(0,0,0,0.08));
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .auth-card,
    .dark .auth-card {
        background: var(--card-bg, #1e293b);
        border-color: var(--border-color, #334155);
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }

    /* Header Section */
    .auth-header {
        text-align: center;
        margin-bottom: 32px;
    }

    .auth-logo {
        margin-bottom: 24px;
        display: flex;
        justify-content: center;
    }

    .auth-logo img {
        height: 50px;
        width: auto;
        max-width: 180px;
        object-fit: contain;
    }

    /* Show/hide logos based on theme */
    .logo-light {
        display: block;
    }

    .logo-dark {
        display: none;
    }

    [data-theme="dark"] .logo-light,
    .dark .logo-light {
        display: none;
    }

    [data-theme="dark"] .logo-dark,
    .dark .logo-dark {
        display: block;
    }

    .auth-logo-text {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--heading-color, #0f172a);
        background: linear-gradient(135deg, var(--primary-color, #2DBDB4), var(--secondary-color, #F97316));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .auth-header h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--heading-color, #0f172a);
        margin-bottom: 8px;
        transition: color 0.3s ease;
    }

    [data-theme="dark"] .auth-header h2,
    .dark .auth-header h2 {
        color: var(--heading-color, #f8fafc);
    }

    .auth-subtitle {
        font-size: 0.95rem;
        color: var(--text-muted, #64748b);
        line-height: 1.5;
        transition: color 0.3s ease;
    }

    [data-theme="dark"] .auth-subtitle,
    .dark .auth-subtitle {
        color: var(--text-muted, #94a3b8);
    }

    /* Form Styles using site variables */
    .auth-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        position: relative;
        margin: 0;
    }

    .input-wrapper {
        position: relative;
    }

    .form-control {
        width: 100%;
        padding: 16px 20px 16px 52px;
        background: var(--input-bg, #f8fafc);
        border: 2px solid var(--border-color, #e2e8f0);
        border-radius: 12px;
        color: var(--text-color, #334155);
        font-size: 1rem;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    [data-theme="dark"] .form-control,
    .dark .form-control {
        background: var(--input-bg, #0f172a);
        border-color: var(--border-color, #334155);
        color: var(--text-color, #f1f5f9);
    }

    .form-control::placeholder {
        color: var(--text-muted, #94a3b8);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color, #2DBDB4);
        box-shadow: 0 0 0 4px rgba(var(--primary-rgb, 45, 189, 180), 0.1);
    }

    .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted, #64748b);
        font-size: 1.25rem;
        transition: color 0.3s ease;
        pointer-events: none;
    }

    .form-control:focus + .input-icon,
    .input-wrapper:focus-within .input-icon {
        color: var(--primary-color, #2DBDB4);
    }

    /* Captcha Styles */
    .captcha-box {
        background: var(--input-bg, #f8fafc);
        border: 2px solid var(--border-color, #e2e8f0);
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .captcha-box,
    .dark .captcha-box {
        background: var(--input-bg, #0f172a);
        border-color: var(--border-color, #334155);
    }

    .captcha-label {
        display: block;
        color: var(--text-muted, #64748b);
        font-size: 0.875rem;
        margin-bottom: 12px;
        font-weight: 500;
    }

    [data-theme="dark"] .captcha-label,
    .dark .captcha-label {
        color: var(--text-muted, #94a3b8);
    }

    .captcha-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .captcha-code {
        padding: 12px 20px;
        background: linear-gradient(135deg, var(--primary-color, #2DBDB4), var(--secondary-color, #F97316));
        border-radius: 10px;
        font-weight: 700;
        letter-spacing: 3px;
        color: #ffffff;
        font-size: 1.25rem;
        font-family: 'Courier New', monospace;
        user-select: none;
        box-shadow: 0 4px 15px rgba(var(--primary-rgb, 45, 189, 180), 0.3);
    }

    .btn-refresh {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: var(--body-bg, #f1f5f9);
        border: 1px solid var(--border-color, #e2e8f0);
        color: var(--text-color, #334155);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .btn-refresh,
    .dark .btn-refresh {
        background: var(--body-bg, #1e293b);
        border-color: var(--border-color, #334155);
        color: var(--text-color, #f1f5f9);
    }

    .btn-refresh:hover {
        background: var(--primary-color, #2DBDB4);
        border-color: var(--primary-color, #2DBDB4);
        color: #ffffff;
        transform: rotate(180deg);
    }

    /* Terms Text */
    .auth-terms {
        text-align: center;
        color: var(--text-muted, #64748b);
        font-size: 0.875rem;
        line-height: 1.5;
        transition: color 0.3s ease;
    }

    [data-theme="dark"] .auth-terms,
    .dark .auth-terms {
        color: var(--text-muted, #94a3b8);
    }

    .auth-terms a {
        color: var(--primary-color, #2DBDB4);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .auth-terms a:hover {
        color: var(--secondary-color, #F97316);
    }

    /* Submit Button - Uses site button styles */
    .btn-login {
        width: 100%;
        padding: 16px;
        background: var(--primary-color, #2DBDB4);
        border: none;
        border-radius: 12px;
        color: #ffffff;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        position: relative;
        overflow: hidden;
    }

    .btn-login:hover {
        background: var(--primary-hover, #25a099);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(var(--primary-rgb, 45, 189, 180), 0.4);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .btn-login.loading {
        pointer-events: none;
        opacity: 0.8;
    }

    /* Footer Links */
    .auth-footer {
        margin-top: 24px;
        text-align: center;
    }

    .footer-text {
        color: var(--text-muted, #64748b);
        font-size: 0.95rem;
        margin-bottom: 8px;
        transition: color 0.3s ease;
    }

    [data-theme="dark"] .footer-text,
    .dark .footer-text {
        color: var(--text-muted, #94a3b8);
    }

    .footer-link {
        color: var(--primary-color, #2DBDB4);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .footer-link:hover {
        color: var(--secondary-color, #F97316);
    }

    .forgot-link {
        display: inline-block;
        margin-top: 12px;
        font-size: 0.9rem;
        color: var(--text-muted, #64748b);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    [data-theme="dark"] .forgot-link,
    .dark .forgot-link {
        color: var(--text-muted, #94a3b8);
    }

    .forgot-link:hover {
        color: var(--primary-color, #2DBDB4);
    }

    /* Error Messages */
    .invalid-feedback {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 8px;
    }

    [data-theme="dark"] .invalid-feedback,
    .dark .invalid-feedback {
        color: #f87171;
    }

    /* Floating Trust Badges */
    .trust-badge {
        position: absolute;
        background: var(--card-bg, #ffffff);
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 16px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: var(--box-shadow, 0 4px 20px rgba(0,0,0,0.08));
        animation: floatBadge 4s ease-in-out infinite;
        z-index: 5;
    }

    [data-theme="dark"] .trust-badge,
    .dark .trust-badge {
        background: var(--card-bg, #1e293b);
        border-color: var(--border-color, #334155);
    }

    .trust-badge.badge-1 {
        top: 10%;
        right: 5%;
        animation-delay: 0s;
    }

    .trust-badge.badge-2 {
        bottom: 15%;
        left: 5%;
        animation-delay: 2s;
    }

    @keyframes floatBadge {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    .badge-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--primary-color, #2DBDB4), var(--secondary-color, #F97316));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 1.25rem;
    }

    .badge-text {
        color: var(--text-color, #334155);
        font-weight: 600;
        font-size: 0.9rem;
    }

    [data-theme="dark"] .badge-text,
    .dark .badge-text {
        color: var(--text-color, #f1f5f9);
    }

    .badge-sub {
        color: var(--text-muted, #64748b);
        font-size: 0.75rem;
    }

    [data-theme="dark"] .badge-sub,
    .dark .badge-sub {
        color: var(--text-muted, #94a3b8);
    }

    /* Responsive */
    @media (max-width: 640px) {
        .auth-page-wrapper {
            padding: 20px;
        }

        .auth-card {
            padding: 32px 24px;
            border-radius: 20px;
        }

        .trust-badge {
            display: none;
        }

        .auth-header h2 {
            font-size: 1.25rem;
        }

        .captcha-row {
            flex-direction: column;
            align-items: stretch;
        }

        .captcha-code {
            text-align: center;
        }
    }

    /* Reduced Motion */
    @media (prefers-reduced-motion: reduce) {
        .auth-bg-effects::before,
        .auth-bg-effects::after,
        .trust-badge {
            animation: none;
        }
    }
</style>

<div class="auth-page-wrapper">
    <!-- Ambient Background -->
    <div class="auth-bg-effects"></div>

    <!-- Trust Badges (Desktop) -->
    <div class="trust-badge badge-1">
        <div class="badge-icon">
            <i class="ti ti-shield-check"></i>
        </div>
        <div>
            <div class="badge-text">100% Secure</div>
            <div class="badge-sub">SSL Encrypted</div>
        </div>
    </div>

    <div class="trust-badge badge-2">
        <div class="badge-icon">
            <i class="ti ti-users"></i>
        </div>
        <div>
            <div class="badge-text">10K+ Users</div>
            <div class="badge-sub">Trust our platform</div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                @if(optional($settings)->logo_white && optional($settings)->logo_dark)
                    <img src="{{ asset($settings->logo_dark) }}" class="logo logo-light" alt="{{ optional($settings)->website_name ?? 'Logo' }}">
                    <img src="{{ asset($settings->logo_white) }}" class="logo logo-dark" alt="{{ optional($settings)->website_name ?? 'Logo' }}">
                @elseif(optional($settings)->logo_white)
                    <img src="{{ asset($settings->logo_white) }}" class="logo" alt="{{ optional($settings)->website_name ?? 'Logo' }}">
                @elseif(optional($settings)->logo_dark)
                    <img src="{{ asset($settings->logo_dark) }}" class="logo" alt="{{ optional($settings)->website_name ?? 'Logo' }}">
                @else
                    <span class="auth-logo-text">{{ optional($settings)->website_name ?? config('app.name') }}</span>
                @endif
            </div>
            <h2>Two-Factor Verification</h2>
            <p class="auth-subtitle">Enter the 6-digit verification code sent to your email to continue.</p>
        </div>

        <form id="verifyForm" class="auth-form" method="POST" action="{{ route('user.two-factor.confirm') }}">
            @csrf

            <!-- Verification Code -->
            <div class="form-group">
                <div class="input-wrapper">
                    <input type="text" name="code" id="code" 
                        class="form-control @error('code') is-invalid @enderror" 
                        placeholder="2FA Code" 
                        value="{{ old('code') }}" 
                        required 
                        autocomplete="one-time-code">
                    <i class="ti ti-shield-check input-icon"></i>
                </div>
                @error('code')
                    <div class="invalid-feedback">
                        <i class="ti ti-alert-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="auth-terms">
                Didn't receive a code?
                <button
                    type="submit"
                    form="resendVerificationForm"
                    style="background: none; border: none; color: var(--primary-color, #2DBDB4); font-weight: 600; cursor: pointer; padding: 0;"
                >
                    Resend 2FA code
                </button>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-login" id="submitBtn">
                <i class="ti ti-check"></i>
                Verify and Continue
            </button>

            <!-- Footer -->
            <div class="auth-footer">
                <p class="footer-text">
                    Back to login?
                    <a href="{{ route('user.login.page') }}" class="footer-link">Sign In</a>
                </p>
            </div>
        </form>

        <form id="resendVerificationForm" method="POST" action="{{ route('user.two-factor.resend') }}" style="display: none;">
            @csrf
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Loading state
        const form = document.getElementById('verifyForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form && submitBtn) {
            form.addEventListener('submit', function() {
                submitBtn.classList.add('loading');
                submitBtn.innerHTML = '<i class="ti ti-loader-2 ti-spin"></i> Verifying...';
            });
        }
    });
</script>
@endsection