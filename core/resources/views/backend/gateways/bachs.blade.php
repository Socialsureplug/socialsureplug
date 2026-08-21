@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card">
        <form action="{{ route('backend.payment-gateway.bachs.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row mb-25">
                    <div class="width-30">
                        <div class="gateway-qr-code">
                            <div class="form-label">Gateway QR code</div>
                            <div class="gateway-qr-code-image">
                                <img class="upload-image" src="{{ ($gateway->gateway_image ?? null) ? asset($gateway->gateway_image) : 'https://propertynir.softnir.com/white/asset/images/placeholder.png' }}" alt="">
                                <input type="file" name="bachs_image" id="gateway_qr_code_bachs" accept="image/*" style="display: none;">
                                <button type="button" class="btn btn-primary upload-image-btn">Upload QR code</button>
                            </div>
                        </div>
                    </div>
                    <div class="width-70">
                        <div class="row">
                            <div class="form-group width-50">
                                <label class="form-label">Gateway currency<span class="required">*</span></label>
                                <input type="text" class="form-control" name="gateway_currency" placeholder="e.g. NGN, USD" value="{{ old('gateway_currency', $gateway->gateway_parameters->gateway_currency ?? '') }}">
                            </div>
                            <div class="form-group width-50">
                                <label class="form-label">API key<span class="required">*</span></label>
                                <input type="text" class="form-control" name="api_key" placeholder="sk_sandbox_xxx or sk_live_xxx" value="{{ old('api_key', $gateway->gateway_parameters->api_key ?? '') }}">
                                <small class="text-muted">Sandbox (sk_sandbox_) and production (sk_live_) route automatically &mdash; going live is just swapping this key.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group width-50">
                                <label class="form-label">Webhook signing secret<span class="required">*</span></label>
                                <input type="text" class="form-control" name="webhook_secret" placeholder="From Developer Portal &rarr; Webhooks" value="{{ old('webhook_secret', $gateway->gateway_parameters->webhook_secret ?? '') }}">
                                <small class="text-muted">
                                    Register <code>{{ url('/payment/bachs/webhook') }}</code> as a webhook endpoint in your Bachs developer portal
                                    (subscribe to <code>collection.succeeded</code>), then paste its signing secret here.
                                </small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group width-50">
                                <label class="form-label">Rate<span class="required">*</span></label>
                                <input type="number" step="0.00000001" class="form-control" name="rate" value="{{ old('rate', $gateway->rate ?? 1) }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group">
                                <label class="form-label">Status<span class="required">*</span></label>
                                <div class="toggle-switch">
                                    <input type="hidden" name="status" value="0">
                                    <input type="checkbox" id="status_bachs" name="status" value="1" {{ ($gateway->status ?? 0) == 1 ? 'checked' : '' }}>
                                    <label for="status_bachs" class="toggle-label">
                                        <span class="toggle-text">On</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group width-50">
                                <label class="form-label">Minimum payment<span class="required">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" name="min_payment" value="{{ old('min_payment', $gateway->min_payment ?? 0) }}">
                            </div>
                            <div class="form-group width-50">
                                <label class="form-label">Maximum payment</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="max_payment" value="{{ old('max_payment', $gateway->max_payment) }}">
                                <small class="text-muted">Leave empty for no maximum.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full mb-25">Update settings</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
    (function() {
        const uploadImageBtn = document.querySelector('.upload-image-btn');
        const uploadImage = document.querySelector('.upload-image');
        const fileInput = document.getElementById('gateway_qr_code_bachs');

        if (uploadImageBtn && fileInput) {
            uploadImageBtn.addEventListener('click', function(e) {
                e.preventDefault();
                fileInput.click();
            });
        }

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (!file.type.startsWith('image/')) {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({ message: 'Please upload an image', position: 'topRight' });
                        }
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({ message: 'File size must be less than 5MB', position: 'topRight' });
                        }
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        if (uploadImage) {
                            uploadImage.src = ev.target.result;
                            uploadImage.alt = 'Uploaded QR code';
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    })();
</script>
@endpush
