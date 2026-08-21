@extends('frontend.layout.dash')

@section('title', $title ?? 'Profile')

@section('content')
    <div class="container-header">
        <h2 class="title">{{ $title ?? 'Profile' }}</h2>
    </div>

    <div class="card mp-20 profile-card">
        <div class="profile-image-wrap">
            <img id="profile-image-preview" src="{{ $user->image ? asset($user->image) : 'https://ui-avatars.com/api/?name=' . urlencode($user->fname . ' ' . $user->lname) . '&background=random' }}" alt="Profile">
        </div>
        <p class="profile-name">{{ $user->fname }} {{ $user->lname }}</p>
        <p class="profile-email">{{ $user->email }}</p>
        <form action="{{ route('user.profile.update-image') }}" method="POST" enctype="multipart/form-data" class="profile-image-form">
            @csrf
            <input type="file" name="image" id="profile-image-input" accept="image/*" style="display: none;">
            <button type="button" class="btn btn-primary" id="update-image-btn"><i class="ti ti-photo-up"></i> Update photo</button>
        </form>
    </div>

    <div class="card mp-20">
        <h2 class="title">Account details</h2>
        <form action="{{ route('user.profile.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">First name</label>
                    <input type="text" name="fname" class="form-control" value="{{ old('fname', $user->fname) }}" placeholder="First name">
                </div>
                <div class="form-group">
                    <label class="form-label">Last name</label>
                    <input type="text" name="lname" class="form-control" value="{{ old('lname', $user->lname) }}" placeholder="Last name">
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required placeholder="Email">
                </div>
                <div class="form-group">
                    <label class="form-label">New password</label>
                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Two-factor authentication (2FA)</label>
                    @php
                        $twoFactorEnabled = old('two_factor_enabled');
                        $twoFactorEnabled = $twoFactorEnabled === null
                            ? (bool) $user->two_factor_enabled
                            : (string) $twoFactorEnabled === '1';
                    @endphp
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="toggle-switch">
                            <input type="checkbox" id="two_factor_enabled" name="two_factor_enabled" value="1" {{ $twoFactorEnabled ? 'checked' : '' }}>
                            <label for="two_factor_enabled" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <label for="two_factor_enabled" style="margin:0;">Enable email code on login</label>
                    </div>
                    <input type="hidden" name="two_factor_enabled" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Current password (required to change 2FA)</label>
                    <input type="password" name="current_password" class="form-control" placeholder="Enter current password to enable/disable 2FA">
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i> Update</button>
        </form>
    </div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var updateBtn = document.getElementById('update-image-btn');
    var fileInput = document.getElementById('profile-image-input');
    var preview = document.getElementById('profile-image-preview');
    var form = document.querySelector('.profile-image-form');

    if (updateBtn && fileInput) {
        updateBtn.addEventListener('click', function() { fileInput.click(); });
        fileInput.addEventListener('change', function() {
            if (fileInput.files.length) form.submit();
        });
    }
    
    document.querySelectorAll('.toggle-switch input[type="checkbox"]').forEach(function(checkbox) {
        const hiddenInput = checkbox.closest('.form-group')?.querySelector('input[type="hidden"][name="' + checkbox.name + '"]');
        
        checkbox.addEventListener('change', function() {
            if (hiddenInput) {
                hiddenInput.disabled = this.checked;
            }
        });
        
        if (hiddenInput && checkbox.checked) {
            hiddenInput.disabled = true;
        }
    });
});
</script>
@endpush
