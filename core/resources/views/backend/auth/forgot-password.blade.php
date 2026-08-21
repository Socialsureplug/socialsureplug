@extends('backend.layout.auth')

@section('content')
<div class="auth-container">
    <div class="auth-wrapper">
        <div class="auth-header">
            <h3>{{ __($pageTitle) }}</h3>
        </div>
        <div class="auth-body">
            <div style="text-align: center; margin-bottom: 20px; color: #e2e8f0;">
                <p>Enter your email address and we'll send you a verification code to reset your password.</p>
            </div>
            <form action="{{ route('backend.forgot-password.submit') }}" method="POST" class="auth-form">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Enter email" required>
                </div>
                <button class="auth-button" type="submit">{{ __('Send Reset Code') }} <i class="las la-arrow-right"></i></button>
            </form>
            <div style="text-align: center; margin-top: 20px;">
                <p style="color: #e2e8f0;">Remember your password? <a href="{{ route('backend.login') }}" style="color: var(--primary-color);">Login</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
