@extends('frontend.layout.auth')

@section('content')
    <div class="auth-container">
        <div class="auth-wrapper">
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
                <h2 class="title">{{ $title ?? 'Reset Password' }}</h2>
                <p class="description">Enter the verification code sent to your email and your new password.</p>
            </div>

            <form class="auth-form" method="POST" action="{{ route('user.reset-password.submit') }}">
                @csrf
                <div class="form-group">
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $email ?? '') }}" placeholder="Email Address" readonly style="background-color: #f5f5f5;">
                </div>
                <div class="form-group">
                    <input type="text" name="code" id="code" class="form-control" placeholder="Reset Code (6 digits)" maxlength="6" value="{{ old('code') }}" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" id="password" class="form-control" placeholder="New Password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm New Password" required>
                </div>
                @if(optional($settings)->recaptcha == 1 && isset($captcha))
                <div class="form-group">
                    <label for="captcha" class="form-label">Captcha</label>
                    <div class="input-group" style="display: flex; align-items: center; gap: 8px;">
                        <input type="text" name="captcha" id="captcha" class="form-control" placeholder="Enter captcha" required autocomplete="off">
                        <span class="captcha-text" style="padding: 8px 12px; background: #eee; border-radius: 4px; font-weight: bold; letter-spacing: 2px;">{{ $captcha }}</span>
                        <button type="button" class="captcha-refresh btn btn-secondary" title="Refresh captcha"><i class="las la-sync-alt"></i></button>
                    </div>
                </div>
                @endif
                <div class="form-group">
                    <button type="submit" class="auth-btn">Reset Password</button>
                </div>
                <div class="auth-footer">
                    <p class="auth-footer-line">
                        Remember your password?
                        <a href="{{ route('user.login.page') }}" class="auth-footer-link">Login</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
    @if(optional($settings)->recaptcha == 1 && isset($captcha))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.querySelector('.captcha-refresh');
            if (btn) {
                btn.addEventListener('click', function() {
                    fetch('{{ route('user.refresh-captcha') }}', {
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                    }).then(function(r) { return r.json(); }).then(function(d) {
                        if (d.captcha) document.querySelector('.captcha-text').textContent = d.captcha;
                    });
                });
            }
        });
    </script>
    @endif
@endsection
