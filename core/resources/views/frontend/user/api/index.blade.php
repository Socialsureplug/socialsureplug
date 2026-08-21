@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="card mp-20 api-key-card">
        <h2 class="title">Your Api Key</h2>
        <div class="form-group">
            <input type="text" class="form-control api-key-field" value="{{ $apiKey }}" readonly aria-label="API Key">
        </div>
        <div class="api-key-actions">
            <button type="button" class="btn btn-copy">
                <i class="ti ti-copy"></i>
                <span>Copy</span>
            </button>
            <button type="button" class="btn btn-change-key">
                <i class="ti ti-key"></i>
                <span>Change Key</span>
            </button>
        </div>
    </div>


    <div class="card mp-20 api-docs-card">
        <h2 class="title">API Documentation</h2>
        <p class="desc">HTTP API for virtual numbers on your {{ optional($settings)->website_name ?? config('app.name') }}
            account. Use your API key above.</p>
        <p class="desc">Flow: <code>servers</code> → <code>countries?server_id=</code> →
            <code>services?server_id=&amp;country=</code> → <code>get-number</code></p>

        {{-- Balance --}}
        <div class="accordion-card">
            <button type="button" class="accordion-header" aria-expanded="false">
                <span class="accordion-header-left">
                    <span class="accordion-icon">
                        <i class="ti ti-wallet"></i>
                    </span>
                    <span class="accordion-title">Balance Check</span>
                </span>
                <i class="ti ti-chevron-down accordion-caret"></i>
            </button>

            <div class="accordion-body">
                <div class="api-doc-block">
                    <div class="api-doc-method">Request Method → GET</div>
                    <pre class="api-doc-url">
{{ $baseUrl }}/api/v1/balance?api_key=$api_key
                    </pre>

                    <p class="api-doc-subtitle">Parameters</p>
                    <ul class="api-doc-list">
                        <li><span class="key">$api_key</span><span class="text">Your API key. (Required)</span></li>
                    </ul>

                    <p class="api-doc-subtitle">Possible errors</p>
                    <ul class="api-doc-list errors">
                        <li><span class="key">BAD_KEY</span><span class="text">Incorrect API key</span></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Purchase Number --}}
        <div class="accordion-card">
            <button type="button" class="accordion-header" aria-expanded="false">
                <span class="accordion-header-left">
                    <span class="accordion-icon">
                        <i class="ti ti-device-mobile"></i>
                    </span>
                    <span class="accordion-title">Purchase Number</span>
                </span>
                <i class="ti ti-chevron-down accordion-caret"></i>
            </button>

            <div class="accordion-body">
                <div class="api-doc-block">
                    <div class="api-doc-method">Request Method → GET</div>
                    <pre class="api-doc-url">
{{ $baseUrl }}/api/v1/get-number?api_key=$api_key&service=$service&country=$country
                    </pre>

                    <p class="api-doc-subtitle">Parameters</p>
                    <ul class="api-doc-list">
                        <li><span class="key">$api_key</span><span class="text">Your API key. (Required)</span></li>
                        <li><span class="key">$service</span><span class="text">Service code from the services list.
                                (Required)</span></li>
                        <li><span class="key">$country</span><span class="text">Country ID from the countries list.
                                (Required)</span></li>
                    </ul>

                    <p class="api-doc-subtitle">Possible errors</p>
                    <ul class="api-doc-list errors">
                        <li><span class="key">BAD_KEY</span><span class="text">Incorrect API key</span></li>
                        <li><span class="key">NO_NUMBERS</span><span class="text">No numbers, try again later</span>
                        </li>
                        <li><span class="key">NO_BALANCE</span><span class="text">Not enough balance</span></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Activation Status --}}
        <div class="accordion-card">
            <button type="button" class="accordion-header" aria-expanded="false">
                <span class="accordion-header-left">
                    <span class="accordion-icon">
                        <i class="ti ti-message-2"></i>
                    </span>
                    <span class="accordion-title">Activation Status</span>
                </span>
                <i class="ti ti-chevron-down accordion-caret"></i>
            </button>

            <div class="accordion-body">
                <div class="api-doc-block">
                    <div class="api-doc-method">Request Method → GET</div>
                    <pre class="api-doc-url">
{{ $baseUrl }}/api/v1/status?api_key=$api_key&id=$id
                    </pre>

                    <p class="api-doc-subtitle">Parameters</p>
                    <ul class="api-doc-list">
                        <li><span class="key">$api_key</span><span class="text">Your API key. (Required)</span></li>
                        <li><span class="key">$id</span><span class="text">Activation ID. (Required)</span></li>
                    </ul>

                    <p class="api-doc-subtitle">Possible errors</p>
                    <ul class="api-doc-list errors">
                        <li><span class="key">BAD_KEY</span><span class="text">Incorrect API key</span></li>
                        <li><span class="key">NO_ACTIVATION</span><span class="text">There is no activation</span></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Change Activation Status --}}
        <div class="accordion-card">
            <button type="button" class="accordion-header" aria-expanded="false">
                <span class="accordion-header-left">
                    <span class="accordion-icon">
                        <i class="ti ti-adjustments"></i>
                    </span>
                    <span class="accordion-title">Change Activation Status</span>
                </span>
                <i class="ti ti-chevron-down accordion-caret"></i>
            </button>

            <div class="accordion-body">
                <div class="api-doc-block">
                    <div class="api-doc-method">Request Method → GET</div>
                    <pre class="api-doc-url">
{{ $baseUrl }}/api/v1/set-status?api_key=$api_key&id=$id&status=$status
                    </pre>

                    <p class="api-doc-subtitle">Parameters</p>
                    <ul class="api-doc-list">
                        <li><span class="key">$api_key</span><span class="text">Your API key. (Required)</span></li>
                        <li><span class="key">$id</span><span class="text">Activation ID. (Required)</span></li>
                        <li><span class="key">$status</span><span class="text">8 – cancel, 3 – request another SMS.
                                (Required)</span></li>
                    </ul>

                    <p class="api-doc-subtitle">Possible errors</p>
                    <ul class="api-doc-list errors">
                        <li><span class="key">BAD_KEY</span><span class="text">Incorrect API key</span></li>
                        <li><span class="key">NO_ACTIVATION</span><span class="text">There is no activation</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Servers --}}
        <div class="accordion-card">
            <button type="button" class="accordion-header" aria-expanded="false">
                <span class="accordion-header-left">
                    <span class="accordion-icon">
                        <i class="ti ti-server"></i>
                    </span>
                    <span class="accordion-title">List of Servers</span>
                </span>
                <i class="ti ti-chevron-down accordion-caret"></i>
            </button>

            <div class="accordion-body">
                <div class="api-doc-block">
                    <div class="api-doc-method">Request Method → GET</div>
                    <pre class="api-doc-url">
{{ $baseUrl }}/api/v1/servers?api_key=$api_key
                    </pre>

                    <p class="api-doc-subtitle">Parameters</p>
                    <ul class="api-doc-list">
                        <li><span class="key">$api_key</span><span class="text">Your API key. (Required)</span></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Countries / Servers --}}
        <div class="accordion-card">
            <button type="button" class="accordion-header" aria-expanded="false">
                <span class="accordion-header-left">
                    <span class="accordion-icon">
                        <i class="ti ti-world"></i>
                    </span>
                    <span class="accordion-title">List of Countries</span>
                </span>
                <i class="ti ti-chevron-down accordion-caret"></i>
            </button>

            <div class="accordion-body">
                <div class="api-doc-block">
                    <div class="api-doc-method">Request Method → GET</div>
                    <pre class="api-doc-url">
{{ $baseUrl }}/api/v1/countries?api_key=$api_key&amp;server_id=$server_id
                    </pre>

                    <p class="api-doc-subtitle">Parameters</p>
                    <ul class="api-doc-list">
                        <li><span class="key">$api_key</span><span class="text">Your API key. (Required)</span></li>
                        <li><span class="key">$server_id</span><span class="text">Server ID from the servers list.
                                (Required)</span></li>
                    </ul>

                    <p class="api-doc-subtitle">Possible errors</p>
                    <ul class="api-doc-list errors">
                        <li><span class="key">BAD_KEY</span><span class="text">Incorrect API key</span></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Services --}}
        <div class="accordion-card">
            <button type="button" class="accordion-header" aria-expanded="false">
                <span class="accordion-header-left">
                    <span class="accordion-icon">
                        <i class="ti ti-list-details"></i>
                    </span>
                    <span class="accordion-title">List of Services</span>
                </span>
                <i class="ti ti-chevron-down accordion-caret"></i>
            </button>

            <div class="accordion-body">
                <div class="api-doc-block">
                    <div class="api-doc-method">Request Method → GET</div>
                    <pre class="api-doc-url">
{{ $baseUrl }}/api/v1/services?api_key=$api_key&amp;server_id=$server_id&amp;country=$country
                    </pre>

                    <p class="api-doc-subtitle">Parameters</p>
                    <ul class="api-doc-list">
                        <li><span class="key">$api_key</span><span class="text">Your API key. (Required)</span></li>
                        <li><span class="key">$server_id</span><span class="text">Server ID from the servers list.
                                (Required)</span></li>
                        <li><span class="key">$country</span><span class="text">Country ID from the countries list.
                                (Required)</span></li>
                    </ul>

                    <p class="api-doc-subtitle">Possible errors</p>
                    <ul class="api-doc-list errors">
                        <li><span class="key">BAD_KEY</span><span class="text">Incorrect API key</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const keyInput = document.querySelector('.api-key-field');
            const copyBtn = document.querySelector('.btn-copy');
            const changeBtn = document.querySelector('.btn-change-key');

            if (copyBtn && keyInput) {
                copyBtn.addEventListener('click', function() {
                    keyInput.select();
                    navigator.clipboard.writeText(keyInput.value).then(function() {
                        if (window.iziToast) {
                            iziToast.success({
                                message: 'API key copied',
                                position: 'topRight'
                            });
                        }
                    }).catch(function() {
                        if (window.iziToast) {
                            iziToast.error({
                                message: 'Copy failed',
                                position: 'topRight'
                            });
                        }
                    });
                });
            }

            if (changeBtn && keyInput) {
                changeBtn.addEventListener('click', function() {
                    const btn = this;
                    btn.disabled = true;

                    fetch('{{ route('user.api.regenerate-key') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document
                                    .querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json',
                            },
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(json) {
                            btn.disabled = false;

                            if (json.status === '200') {
                                keyInput.value = json.api_key;
                                if (window.iziToast) {
                                    iziToast.success({
                                        message: json.message || 'API key changed',
                                        position: 'topRight'
                                    });
                                }
                            } else if (window.iziToast) {
                                iziToast.error({
                                    message: json.message || 'Failed to change key',
                                    position: 'topRight'
                                });
                            }
                        })
                        .catch(function() {
                            btn.disabled = false;
                            if (window.iziToast) {
                                iziToast.error({
                                    message: 'Request failed',
                                    position: 'topRight'
                                });
                            }
                        });
                });
            }
        });
    </script>
@endpush
