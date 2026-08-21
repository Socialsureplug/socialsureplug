@extends('backend.layout.auth')

@section('content')
<div class="auth-container">
    <div class="auth-wrapper">
        <div class="auth-header">
            <h3>{{ __($title) }}</h3>
        </div>
        <div class="auth-body">
            <form action="{{ route('backend.login.submit') }}" method="POST" class="auth-form">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" tabindex="1" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <div class="auth-input-group">
                        <input type="password" id="password" name="password" class="form-control" value="{{ old('password') }}" placeholder="Enter your password">
                        <i class="las la-eye" id="password-icon"></i>
                    </div>
                </div>
                <div class="form-group auth-checkbox-group">
                    <div class="auth-checkbox">
                        <input type="checkbox" id="auth-check" name="remember" class="custom-checkbox">
                        <label for="auth-check" class="custom-checkbox-label">
                            <span class="checkmark"></span>
                        </label>
                        <label for="auth-check" class="auth-text">{{ __('Remember me') }}</label>
                    </div>
                    <a href="{{ route('backend.forgot-password') }}" class="forgot-password">{{ __('Forgot password?') }}</a>
                </div>
                <button class="auth-button">{{ __('Login') }} <i class="las la-arrow-right"></i></button>
            </form>
        </div>
    </div>
</div>
@endsection