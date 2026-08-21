@extends('backend.layout.auth')

@section('content')
<div class="auth-container">
    <div class="auth-wrapper">
        <div class="auth-header">
            <h3>{{ __($pageTitle) }}</h3>
        </div>
        <div class="auth-body">
            <div style="text-align: center; margin-bottom: 20px; color: #e2e8f0;">
                <p>Enter the verification code sent to your email and your new password.</p>
            </div>
            <form action="{{ route('backend.reset-password.submit') }}" method="POST" class="auth-form">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" type="email" class="form-control" name="email" value="{{ $email }}" readonly style="background-color: #374151; color: #9ca3af;">
                </div>
                <div class="form-group">
                    <label for="code" class="form-label">{{ __('Reset Code') }}</label>
                    <input id="code" type="text" class="form-control" name="code" placeholder="Enter 6-digit code" maxlength="6" required>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">{{ __('New Password') }}</label>
                    <div class="auth-input-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password" required>
                        <i class="las la-eye" id="password-icon"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                    <div class="auth-input-group">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm new password" required>
                        <i class="las la-eye" id="password_confirmation-icon"></i>
                    </div>
                </div>
                <button class="auth-button" type="submit">{{ __('Reset Password') }} <i class="las la-arrow-right"></i></button>
            </form>
            <div style="text-align: center; margin-top: 20px;">
                <p style="color: #e2e8f0;">Remember your password? <a href="{{ route('backend.login') }}" style="color: var(--primary-color);">Login</a></p>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var passwordIcon = document.getElementById('password-icon');
    var passwordConfirmationIcon = document.getElementById('password_confirmation-icon');
    if (passwordIcon) {
        passwordIcon.addEventListener('click', function() {
            var passwordField = document.getElementById('password');
            var icon = this;
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('la-eye');
                icon.classList.add('la-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('la-eye-slash');
                icon.classList.add('la-eye');
            }
        });
    }
    if (passwordConfirmationIcon) {
        passwordConfirmationIcon.addEventListener('click', function() {
            var passwordField = document.getElementById('password_confirmation');
            var icon = this;
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('la-eye');
                icon.classList.add('la-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('la-eye-slash');
                icon.classList.add('la-eye');
            }
        });
    }
});
</script>
@endpush
@endsection
