@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $pageTitle }}</h1>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="width-30">
                <div class="card mb-25">
                    <div class="user-image">
                        <img id="user-profile-image" src="{{ $user->image ? asset($user->image) : 'https://propertynir.softnir.com/white/asset/images/placeholder.png' }}" alt="{{ $user->username }}">
                        <button class="btn btn-primary" id="update-image-btn"><i class="las la-save"></i>Update image</button>
                    </div>

                    <div class="user-info">
                        <h2 class="user-name">{{ $user->fname }} {{ $user->lname }}</h2>
                        <p class="user-email">{{ $user->email }}</p>

                        <div class="user-actions">
                            <div class="row">
                                <button class="btn btn-primary width-50" id="send-email"><i class="las la-envelope"></i>Send email</button>
                                <a href="{{ route('backend.users.login-as-user', $user->id) }}" target="_blank" class="btn btn-primary width-50"><i class="las la-user"></i>Login as user</a>
                            </div>
                            <div class="row">
                                <a href="{{ route('backend.numbers.orders', ['user_id' => $user->id]) }}" class="btn btn-primary width-50"><i class="las la-sim-card"></i>Number orders</a>
                                <a href="{{ route('backend.logs.transactions', ['user_id' => $user->id]) }}" class="btn btn-primary width-50"><i class="las la-dollar-sign"></i>Transaction log</a>
                            </div>
                            <div class="row">
                                <a href="{{ route('backend.payments.index', ['user_id' => $user->id]) }}" class="btn btn-primary width-50"><i class="las la-dollar-sign"></i>Payment log</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-stat mb-25">
                    <div class="card-icon">
                        <i class="las la-dollar-sign"></i>
                    </div>
                    <div class="card-text">
                        <h4>User balance</h4>
                        <h2>{{ number_format($user->balance, 2) }} {{ optional($settings)->website_currency ?? 'USD' }}</h2>
                    </div>
                </div>
            </div>
            <div class="width-70">
                <div class="row">
                    <div class="card width-50 balance-card">
                        <h3>Add balance</h3>
                        <form action="{{ route('backend.users.add-balance', $user->id) }}" method="post">
                            @csrf
                            <div class="input-group">
                                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" max="999999.99" required>
                                <div class="input-group-append">
                                    <button><i class="las la-plus"></i>Add balance</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card width-50 balance-card">
                        <h3>Subtract balance</h3>
                        <form action="{{ route('backend.users.subtract-balance', $user->id) }}" method="post">
                            @csrf
                            <div class="input-group">
                                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" max="999999.99" required>
                                <div class="input-group-append">
                                    <button><i class="las la-minus"></i>Subtract balance</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card user-card">
                    <form action="{{ route('backend.users.update-user', $user->id) }}" method="post" class="mb-25">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="form-group width-50">
                                <label class="form-label">First name<span class="required">*</span></label>
                                <input type="text" class="form-control" name="fname" value="{{ $user->fname }}" placeholder="Enter First name">
                            </div>
                            <div class="form-group width-50">
                                <label class="form-label">Last name<span class="required">*</span></label>
                                <input type="text" class="form-control" name="lname" value="{{ $user->lname }}" placeholder="Enter Last name">
                            </div>

                        </div>

                        <div class="row">
                            <div class="form-group width-50">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" value="{{ $user->phone }}" placeholder="Enter phone number">
                            </div>
                            <div class="form-group width-50">
                                <label class="form-label">Email<span class="required">*</span></label>
                                <input type="text" class="form-control" name="email" value="{{ $user->email }}" placeholder="Enter Email">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group width-50">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" placeholder="Enter new password (leave blank to keep current)">
                            </div>
                        </div>

                        <button class="btn btn-primary">Update user</button>
                    </form>
                    <div class="row">
                        <div class="form-group">
                            <label class="form-label">Status<span class="required">*</span></label>
                            <div class="toggle-switch">
                                <input type="checkbox" id="toggle-{{ $user->id }}" {{ $user->status == 1 ? 'checked' : '' }}>
                                <label for="toggle-{{ $user->id }}" class="toggle-label">
                                    <span class="toggle-text">{{ $user->status == 1 ? 'On' : 'Off' }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card user-card">
                    <h3 class="mb-25">Service Discounts</h3>
                    <p class="text-muted mb-25">Set a % discount this user gets on each service. Leave at 0 for no discount.</p>
                    <form action="{{ route('backend.users.update-discounts', $user->id) }}" method="post">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            @foreach($discountServices as $serviceKey => $serviceLabel)
                            <div class="form-group width-50">
                                <label class="form-label">{{ $serviceLabel }} discount (%)</label>
                                <input type="number" class="form-control" name="discounts[{{ $serviceKey }}]"
                                    value="{{ old('discounts.'.$serviceKey, $discounts[$serviceKey] ?? 0) }}"
                                    min="0" max="100" step="0.01" placeholder="0">
                            </div>
                            @endforeach
                        </div>
                        <button class="btn btn-primary">Update discounts</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


</div>

<div class="modal" id="send-email-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Send email</h3>
            <button class="close-modal"><i class="las la-times"></i></button>
        </div>
        <form action="{{ route('backend.users.send-email', $user->id) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group mb-25">
                    <label class="form-label">Subject<span class="required">*</span></label>
                    <input type="text" class="form-control" name="subject" placeholder="Enter email subject" required>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Message<span class="required">*</span></label>
                    <textarea class="textarea" name="message" placeholder="Enter message" rows="6" required></textarea>
                </div>
                <div class="form-group mb-25">
                    <small class="text-muted">Available variables: {username}, {email}, {fname}, {lname}</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Send email</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="update-image-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Update Profile Image</h3>
            <button class="close-modal"><i class="las la-times"></i></button>
        </div>
        <form action="{{ route('backend.users.update-image', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group mb-25">
                    <label class="form-label">Select Image<span class="required">*</span></label>
                    <input type="file" class="form-control" name="image" id="image-input" accept="image/*" required>
                </div>
                <div class="form-group mb-25" id="image-preview-container" style="display: none;">
                    <label class="form-label">Preview</label>
                    <div style="text-align: center;">
                        <img id="image-preview" src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update Image</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleSwitches = document.querySelectorAll('input[type="checkbox"][id^="toggle-"]');

            toggleSwitches.forEach(toggle =>  {
                toggle.addEventListener('change', function() {
                    const userId = this.id.replace('toggle-', '');
                    const status = this.checked ? 1 : 0;
                    const toggleText = this.nextElementSibling.querySelector('.toggle-text');

                    toggleText.textContent = status ? 'On' : 'Off';

                    fetch('{{ route('backend.users.toggle-status') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            id: userId,
                            status: status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            iziToast.success({
                                message: 'Status updated successfully',
                                position: 'topRight'
                            })
                        } else {
                            this.checked = !this.checked;
                            toggleText.textContent = this.checked ? 'On' : 'Off';
                            iziToast.error({
                                message: 'Failed to update status',
                                position: 'topRight'
                            })
                        }
                    })
                    .catch(error =>{
                        console.error('Error:', error);
                        this.checked = !this.checked;
                        toggleText.textContent = this.checked ? 'On' : 'Off';
                        iziToast.error({
                            message: 'Failed to update status',
                            position: 'topRight'
                        })
                    } )
                })
            })

            // Send email modal handling
            const sendEmailBtn = document.getElementById('send-email');
            const sendEmailModal = document.getElementById('send-email-modal');
            const closeModalBtns = document.querySelectorAll('.close-modal');

            if (sendEmailBtn) {
                sendEmailBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    sendEmailModal.classList.add('active');
                });
            }

            // Update image modal handling
            const updateImageBtn = document.getElementById('update-image-btn');
            const updateImageModal = document.getElementById('update-image-modal');
            const imageInput = document.getElementById('image-input');
            const imagePreview = document.getElementById('image-preview');
            const imagePreviewContainer = document.getElementById('image-preview-container');

            if (updateImageBtn) {
                updateImageBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    updateImageModal.classList.add('active');
                });
            }

            // Image preview on file select
            if (imageInput) {
                imageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.src = e.target.result;
                            imagePreviewContainer.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        imagePreviewContainer.style.display = 'none';
                    }
                });
            }

            closeModalBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    modal.classList.remove('active');
                    // Reset image input and preview when closing update image modal
                    if (modal.id === 'update-image-modal') {
                        if (imageInput) imageInput.value = '';
                        if (imagePreviewContainer) imagePreviewContainer.style.display = 'none';
                    }
                });
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target.classList.contains('modal')) {
                    e.target.classList.remove('active');
                    // Reset image input and preview when clicking outside
                    if (e.target.id === 'update-image-modal') {
                        if (imageInput) imageInput.value = '';
                        if (imagePreviewContainer) imagePreviewContainer.style.display = 'none';
                    }
                }
            });
        })
    </script>
@endpush