@extends('backend.layout.app')

@section('content')
    <div class="container">
        <div class="container-header">
            <h1>General settings</h1>
        </div>

        <div class="card p-20">
            <form action="{{ route('backend.settings.update-setting') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="form-group width-30">
                        <label class="form-label">Website name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="website_name" placeholder="Enter site name"
                            value="{{ old('website_name', optional($settings)->website_name ?? '') }}">
                    </div>
                    <div class="form-group width-30">
                        <label class="form-label">Website currency<span class="required">*</span></label>
                        <input type="text" class="form-control" name="website_currency" placeholder="Enter site currency"
                            value="{{ old('website_currency', optional($settings)->website_currency ?? '') }}">
                    </div>
                    <div class="form-group width-30">
                        <label class="form-label">Website color<span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" name="website_color" class="form-control input-color-value"
                                value="{{ old('website_color', optional($settings)->website_color ?? '#000000') }}"
                                id="colorValue">
                            <div class="input-color">
                                <input type="color" id="colorPicker"
                                    value="{{ old('website_color', optional($settings)->website_color ?? '#000000') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Currency rate<span class="required">*</span></label>
                        <input type="number" step="0.0001" min="0" class="form-control" name="currency_rate"
                            value="{{ old('currency_rate', optional($settings)->currency_rate ?? 0) }}"
                            placeholder="e.g. 1500.0000">
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">Price increase (%)<span class="required">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" name="price_increase"
                            value="{{ old('price_increase', optional($settings)->price_increase ?? 0) }}"
                            placeholder="e.g. 20.00">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group width-30">
                        <label class="form-label">Timezone<span class="required">*</span></label>
                        <select name="timezone" class="form-control">
                            <option value="UTC"
                                {{ old('timezone', optional($settings)->timezone ?? '') == 'UTC' ? 'selected' : '' }}>UTC
                            </option>
                            <option value="Africa/Nairobi"
                                {{ old('timezone', optional($settings)->timezone ?? '') == 'Africa/Nairobi' ? 'selected' : '' }}>
                                Africa/Nairobi</option>
                            <option value="Africa/Lagos"
                                {{ old('timezone', optional($settings)->timezone ?? '') == 'Africa/Lagos' ? 'selected' : '' }}>
                                Africa/Lagos</option>
                            <option value="Africa/Johannesburg"
                                {{ old('timezone', optional($settings)->timezone ?? '') == 'Africa/Johannesburg' ? 'selected' : '' }}>
                                Africa/Johannesburg</option>
                            <option value="Africa/Kampala"
                                {{ old('timezone', optional($settings)->timezone ?? '') == 'Africa/Kampala' ? 'selected' : '' }}>
                                Africa/Kampala</option>
                            <option value="America/New_York"
                                {{ old('timezone', optional($settings)->timezone ?? '') == 'America/New_York' ? 'selected' : '' }}>
                                America/New York</option>
                            <option value="America/Chicago"
                                {{ old('timezone', optional($settings)->timezone ?? '') == 'America/Chicago' ? 'selected' : '' }}>
                                America/Chicago</option>
                            <option value="America/Los_Angeles"
                                {{ old('timezone', optional($settings)->timezone ?? '') == 'America/Los_Angeles' ? 'selected' : '' }}>
                                America/Los Angeles</option>
                            <option value="Europe/London"
                                {{ old('timezone', optional($settings)->timezone ?? '') == 'Europe/London' ? 'selected' : '' }}>
                                Europe/London</option>
                            <option value="Asia/Dubai"
                                {{ old('timezone', optional($settings)->timezone ?? '') == 'Asia/Dubai' ? 'selected' : '' }}>
                                Asia/Dubai</option>
                        </select>
                    </div>
                    <div class="form-group width-30">
                        <label class="form-label">Copyright Text<span class="required">*</span></label>
                        <input type="text" class="form-control" name="copyright_text" placeholder="Enter copyright text"
                            value="{{ old('copyright_text', optional($settings)->copyright_text ?? '') }}">
                    </div>

                    <div class="form-group width-30">
                        <label class="form-label">Referral Commission %<span class="required">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control"
                            name="referral_commission_percent"
                            value="{{ old('referral_commission_percent', optional($settings)->referral_commission_percent ?? 0) }}">
                    </div>
                </div>

                <div class="settings-grid">
                    <div class="form-group">
                        <label class="form-label">Buy Number<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="buy_number" name="buy_number" value="1"
                                {{ old('buy_number', optional($settings)->buy_number ?? 1) == 1 ? 'checked' : '' }}>
                            <label for="buy_number" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <input type="hidden" name="buy_number" value="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rent Number<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="buy_rent" name="buy_rent" value="1"
                                {{ old('buy_rent', optional($settings)->buy_rent ?? 0) == 1 ? 'checked' : '' }}>
                            <label for="buy_rent" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <input type="hidden" name="buy_rent" value="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Boost Social<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="boost_social" name="boost_social" value="1"
                                {{ old('boost_social', optional($settings)->boost_social ?? 1) == 1 ? 'checked' : '' }}>
                            <label for="boost_social" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <input type="hidden" name="boost_social" value="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Social Logs<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="social_logs" name="social_logs" value="1"
                                {{ old('social_logs', optional($settings)->social_logs ?? 1) == 1 ? 'checked' : '' }}>
                            <label for="social_logs" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <input type="hidden" name="social_logs" value="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">User Signup<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="user_registration" name="user_registration" value="1"
                                {{ old('user_registration', optional($settings)->user_registration ?? 0) == 1 ? 'checked' : '' }}>
                            <label for="user_registration" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <input type="hidden" name="user_registration" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Recaptcha<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="recaptcha" name="recaptcha" value="1"
                                {{ old('recaptcha', optional($settings)->recaptcha ?? 0) == 1 ? 'checked' : '' }}>
                            <label for="recaptcha" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <input type="hidden" name="recaptcha" value="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">User Email<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="user_email_notification" name="user_email_notification"
                                value="1"
                                {{ old('user_email_notification', optional($settings)->user_email_notification ?? 0) == 1 ? 'checked' : '' }}>
                            <label for="user_email_notification" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <input type="hidden" name="user_email_notification" value="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Admin Email<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="admin_email_notification" name="admin_email_notification"
                                value="1"
                                {{ old('admin_email_notification', optional($settings)->admin_email_notification ?? 0) == 1 ? 'checked' : '' }}>
                            <label for="admin_email_notification" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <input type="hidden" name="admin_email_notification" value="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">User Payment<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="user_payment" name="user_payment" value="1"
                                {{ old('user_payment', optional($settings)->user_payment ?? 0) == 1 ? 'checked' : '' }}>
                            <label for="user_payment" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <input type="hidden" name="user_payment" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">User Login<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="user_login" name="user_login" value="1"
                                {{ old('user_login', optional($settings)->user_login ?? 0) == 1 ? 'checked' : '' }}>
                            <label for="user_login" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <input type="hidden" name="user_login" value="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Verification<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="email_verification" name="email_verification" value="1"
                                {{ old('email_verification', optional($settings)->email_verification ?? 0) == 1 ? 'checked' : '' }}>
                            <label for="email_verification" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <input type="hidden" name="email_verification" value="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Referral Feature<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="referral_enabled" name="referral_enabled" value="1"
                                {{ old('referral_enabled', optional($settings)->referral_enabled ?? 0) == 1 ? 'checked' : '' }}>
                            <label for="referral_enabled" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <input type="hidden" name="referral_enabled" value="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Referral First Deposit<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="referral_first_deposit_only" name="referral_first_deposit_only"
                                value="1"
                                {{ old('referral_first_deposit_only', optional($settings)->referral_first_deposit_only ?? 1) == 1 ? 'checked' : '' }}>
                            <label for="referral_first_deposit_only" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                        <input type="hidden" name="referral_first_deposit_only" value="0">
                    </div>

                </div>

                <div class="form-group mb-25">
                    <label class="form-label">Seo description</label>
                    <textarea class="textarea" name="seo_description" placeholder="Enter SEO description">{{ old('seo_description', optional($settings)->seo_description ?? '') }}</textarea>
                </div>

                <div class="form-group mb-25">
                    <label class="form-label">Live Chat Code</label>
                    <textarea class="textarea" name="live_chat_code" placeholder="Enter live chat code">{{ old('live_chat_code', optional($settings)->live_chat_code ?? '') }}</textarea>
                </div>

                <div class="form-group mb-25">
                    <label class="form-label">Header Code</label>
                    <textarea class="textarea" name="header_code" placeholder="Paste TikTok, Facebook, Google Pixel or other tracking/verification code here">{{ old('header_code', optional($settings)->header_code ?? '') }}</textarea>
                    <small class="text-muted">This code is inserted into the &lt;head&gt; section of every page site-wide. Paste full &lt;script&gt; tags as provided by TikTok, Meta, Google, etc.</small>
                </div>

                <div class="row">
                    <div class="image-container">
                        <label class="form-label">Logo white</label>
                        <div class="image-wrapper">
                            <img id="logo_white_preview"
                                src="{{ optional($settings)->logo_white ? asset(optional($settings)->logo_white) : 'https://via.placeholder.com/300x200?text=Logo+White' }}"
                                alt="Logo White">
                            <input type="file" name="logo_white" id="logo_white_input" accept="image/*"
                                style="display: none;">
                            <button type="button" class="btn btn-primary"
                                onclick="document.getElementById('logo_white_input').click()"><i
                                    class="las la-upload"></i>Upload image</button>
                        </div>
                    </div>
                    <div class="image-container">
                        <label class="form-label">Logo dark</label>
                        <div class="image-wrapper">
                            <img id="logo_dark_preview"
                                src="{{ optional($settings)->logo_dark ? asset(optional($settings)->logo_dark) : 'https://via.placeholder.com/300x200?text=Logo+Dark' }}"
                                alt="Logo Dark">
                            <input type="file" name="logo_dark" id="logo_dark_input" accept="image/*"
                                style="display: none;">
                            <button type="button" class="btn btn-primary"
                                onclick="document.getElementById('logo_dark_input').click()"><i
                                    class="las la-upload"></i>Upload image</button>
                        </div>
                    </div>
                    <div class="image-container">
                        <label class="form-label">Favicon white</label>
                        <div class="image-wrapper">
                            <img id="favicon_white_preview"
                                src="{{ optional($settings)->favicon_white ? asset(optional($settings)->favicon_white) : 'https://via.placeholder.com/300x200?text=Favicon+White' }}"
                                alt="Favicon White">
                            <input type="file" name="favicon_white" id="favicon_white_input" accept="image/*"
                                style="display: none;">
                            <button type="button" class="btn btn-primary"
                                onclick="document.getElementById('favicon_white_input').click()"><i
                                    class="las la-upload"></i>Upload image</button>
                        </div>
                    </div>
                    <div class="image-container">
                        <label class="form-label">Favicon dark</label>
                        <div class="image-wrapper">
                            <img id="favicon_dark_preview"
                                src="{{ optional($settings)->favicon_dark ? asset(optional($settings)->favicon_dark) : 'https://via.placeholder.com/300x200?text=Favicon+Dark' }}"
                                alt="Favicon Dark">
                            <input type="file" name="favicon_dark" id="favicon_dark_input" accept="image/*"
                                style="display: none;">
                            <button type="button" class="btn btn-primary"
                                onclick="document.getElementById('favicon_dark_input').click()"><i
                                    class="las la-upload"></i>Upload image</button>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="las la-save"></i>Update settings</button>
            </form>
        </div>
    </div>

    @push('script')
        <script>
            // Image preview functionality
            function setupImagePreview(inputId, previewId) {
                const input = document.getElementById(inputId);
                const preview = document.getElementById(previewId);

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
            }

            // Setup all image previews
            setupImagePreview('logo_white_input', 'logo_white_preview');
            setupImagePreview('logo_dark_input', 'logo_dark_preview');
            setupImagePreview('favicon_white_input', 'favicon_white_preview');
            setupImagePreview('favicon_dark_input', 'favicon_dark_preview');

            // Color picker synchronization
            const colorPicker = document.getElementById('colorPicker');
            const colorValue = document.getElementById('colorValue');

            if (colorPicker && colorValue) {
                // Update text input when color picker changes
                colorPicker.addEventListener('input', function() {
                    colorValue.value = this.value;
                });

                // Update color picker when text input changes
                colorValue.addEventListener('input', function() {
                    if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                        colorPicker.value = this.value;
                    }
                });
            }

            // Handle checkbox toggle switches to ensure proper values are sent
            document.querySelectorAll('.toggle-switch input[type="checkbox"]').forEach(function(checkbox) {
                const hiddenInput = checkbox.parentElement.parentElement.querySelector('input[type="hidden"]');

                // When checkbox state changes, disable/enable hidden input
                checkbox.addEventListener('change', function() {
                    if (hiddenInput) {
                        hiddenInput.disabled = this.checked;
                    }
                });

                // Set initial state
                if (hiddenInput && checkbox.checked) {
                    hiddenInput.disabled = true;
                }
            });
        </script>
    @endpush
@endsection
