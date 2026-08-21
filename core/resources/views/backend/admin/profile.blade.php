@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>Admin profile</h1>
    </div>

    <div class="card p-20">
        <form action="{{ route('backend.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="form-group width-25">
                    <label class="form-label">Full name</label>
                    <input type="text" class="form-control" name="name" placeholder="Enter full name" value="{{ old('name', $admin->name) }}">
                </div>
                <div class="form-group width-25">
                    <label class="form-label">Username<span class="required">*</span></label>
                    <input type="text" class="form-control" name="username" placeholder="Enter username" value="{{ old('username', $admin->username) }}">
                </div>
                <div class="form-group width-25">
                    <label class="form-label">Email<span class="required">*</span></label>
                    <input type="email" class="form-control" name="email" placeholder="Enter email" value="{{ old('email', $admin->email) }}">
                </div>
                <div class="form-group width-25">
                    <label class="form-label">New password</label>
                    <input type="password" class="form-control" name="password" placeholder="Enter new password (optional)">
                </div>
            </div>

            <div class="row">
                <div class="image-container">
                    <label class="form-label">Profile image</label>
                    <div class="image-wrapper">
                        <img id="image_preview" src="{{ $admin->image ? asset($admin->image) : 'https://via.placeholder.com/300x200?text=Profile+Image' }}" alt="Profile Image">
                        <input type="file" name="image" id="image_input" accept="image/*" style="display: none;">
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('image_input').click()"><i class="las la-upload"></i>Upload image</button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="las la-save"></i>Update profile</button>
        </form>
    </div>
</div>

@push('script')
<script>
    (function(){
        const input = document.getElementById('image_input');
        const preview = document.getElementById('image_preview');
        if (input && preview) {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    })();
</script>
@endpush
@endsection
