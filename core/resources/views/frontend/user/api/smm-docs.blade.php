@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="card mp-20 api-key-card">
        <h2 class="title">Your API Key</h2>
        <div class="form-group">
            <input
                type="text"
                class="form-control api-key-field"
                value="{{ $apiKey }}"
                readonly
                aria-label="API Key"
            >
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
        <h2 class="title">SMM API Documentation</h2>
        <p class="desc">Use your API key above. All SMM endpoints use a single URL with <code>key</code> and <code>action</code>. Method: GET or POST.</p>
        <p class="desc"><strong>Base URL:</strong> <code>{{ $smmApiUrl }}</code></p>
    </div>

    <div class="card mp-20 api-docs-card">
        <h2 class="title">Endpoints</h2>

        {{-- Balance --}}
        <div class="accordion-card">
            <button type="button" class="accordion-header" aria-expanded="false">
                <span class="accordion-header-left">
                    <span class="accordion-icon"><i class="ti ti-wallet"></i></span>
                    <span class="accordion-title">Balance</span>
                </span>
                <i class="ti ti-chevron-down accordion-caret"></i>
            </button>
            <div class="accordion-body">
                <div class="api-doc-block">
                    <div class="api-doc-method">GET or POST</div>
                    <pre class="api-doc-url">{{ $smmApiUrl }}?key=YOUR_API_KEY&action=balance</pre>
                    <p class="api-doc-subtitle">Parameters</p>
                    <ul class="api-doc-list">
                        <li><span class="key">key</span><span class="text">Your API key. (Required)</span></li>
                        <li><span class="key">action</span><span class="text">balance</span></li>
                    </ul>
                    <p class="api-doc-subtitle">Example response</p>
                    <pre class="api-doc-url">{"status":"success","balance":"100.00","currency":"NGN"}</pre>
                </div>
            </div>
        </div>

        {{-- Services list --}}
        <div class="accordion-card">
            <button type="button" class="accordion-header" aria-expanded="false">
                <span class="accordion-header-left">
                    <span class="accordion-icon"><i class="ti ti-list-details"></i></span>
                    <span class="accordion-title">Services list</span>
                </span>
                <i class="ti ti-chevron-down accordion-caret"></i>
            </button>
            <div class="accordion-body">
                <div class="api-doc-block">
                    <div class="api-doc-method">GET or POST</div>
                    <pre class="api-doc-url">{{ $smmApiUrl }}?key=YOUR_API_KEY&action=services</pre>
                    <p class="api-doc-subtitle">Parameters</p>
                    <ul class="api-doc-list">
                        <li><span class="key">key</span><span class="text">Your API key. (Required)</span></li>
                        <li><span class="key">action</span><span class="text">services</span></li>
                    </ul>
                    <p class="api-doc-subtitle">Example response</p>
                    <pre class="api-doc-url">[{"service":"1","name":"Instagram Followers","category":"Instagram","rate":1.5,"min":100,"max":10000,"type":"default","dripfeed":0}]</pre>
                </div>
            </div>
        </div>

        {{-- Place order --}}
        <div class="accordion-card">
            <button type="button" class="accordion-header" aria-expanded="false">
                <span class="accordion-header-left">
                    <span class="accordion-icon"><i class="ti ti-shopping-cart"></i></span>
                    <span class="accordion-title">Place new order</span>
                </span>
                <i class="ti ti-chevron-down accordion-caret"></i>
            </button>
            <div class="accordion-body">
                <div class="api-doc-block">
                    <div class="api-doc-method">GET or POST</div>
                    <pre class="api-doc-url">{{ $smmApiUrl }}?key=YOUR_API_KEY&action=add&service=SERVICE_ID&link=LINK&quantity=QUANTITY</pre>
                    <p class="api-doc-subtitle">Parameters</p>
                    <ul class="api-doc-list">
                        <li><span class="key">key</span><span class="text">Your API key. (Required)</span></li>
                        <li><span class="key">action</span><span class="text">add</span></li>
                        <li><span class="key">service</span><span class="text">Service ID from the services list. (Required)</span></li>
                        <li><span class="key">link</span><span class="text">Link to page (e.g. profile URL). (Required)</span></li>
                        <li><span class="key">quantity</span><span class="text">Quantity (within min/max of service). (Required)</span></li>
                    </ul>
                    <p class="api-doc-subtitle">Example response</p>
                    <pre class="api-doc-url">{"status":"success","order":123}</pre>
                    <p class="api-doc-subtitle">Possible errors</p>
                    <ul class="api-doc-list errors">
                        <li><span class="key">error</span><span class="text">Missing/invalid params, service not found, insufficient balance</span></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Order status (single) --}}
        <div class="accordion-card">
            <button type="button" class="accordion-header" aria-expanded="false">
                <span class="accordion-header-left">
                    <span class="accordion-icon"><i class="ti ti-message-2"></i></span>
                    <span class="accordion-title">Order status (single)</span>
                </span>
                <i class="ti ti-chevron-down accordion-caret"></i>
            </button>
            <div class="accordion-body">
                <div class="api-doc-block">
                    <div class="api-doc-method">GET or POST</div>
                    <pre class="api-doc-url">{{ $smmApiUrl }}?key=YOUR_API_KEY&action=status&order=ORDER_ID</pre>
                    <p class="api-doc-subtitle">Parameters</p>
                    <ul class="api-doc-list">
                        <li><span class="key">key</span><span class="text">Your API key. (Required)</span></li>
                        <li><span class="key">action</span><span class="text">status</span></li>
                        <li><span class="key">order</span><span class="text">Order ID. (Required for single)</span></li>
                    </ul>
                    <p class="api-doc-subtitle">Example response</p>
                    <pre class="api-doc-url">{"order":123,"status":"In progress","charge":1.50,"start_count":0,"remains":1000}</pre>
                </div>
            </div>
        </div>

        {{-- Order status (multiple) --}}
        <div class="accordion-card">
            <button type="button" class="accordion-header" aria-expanded="false">
                <span class="accordion-header-left">
                    <span class="accordion-icon"><i class="ti ti-list"></i></span>
                    <span class="accordion-title">Order status (multiple)</span>
                </span>
                <i class="ti ti-chevron-down accordion-caret"></i>
            </button>
            <div class="accordion-body">
                <div class="api-doc-block">
                    <div class="api-doc-method">GET or POST</div>
                    <pre class="api-doc-url">{{ $smmApiUrl }}?key=YOUR_API_KEY&action=status&orders=ORDER_ID1,ORDER_ID2,ORDER_ID3</pre>
                    <p class="api-doc-subtitle">Parameters</p>
                    <ul class="api-doc-list">
                        <li><span class="key">key</span><span class="text">Your API key. (Required)</span></li>
                        <li><span class="key">action</span><span class="text">status</span></li>
                        <li><span class="key">orders</span><span class="text">Comma-separated order IDs. (Required for multiple)</span></li>
                    </ul>
                    <p class="api-doc-subtitle">Example response</p>
                    <pre class="api-doc-url">{"123":{"order":123,"status":"Completed","charge":1.5,"start_count":1000,"remains":0},"124":"Incorrect order ID"}</pre>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const keyInput = document.querySelector('.api-key-field');
    const copyBtn = document.querySelector('.btn-copy');
    const changeBtn = document.querySelector('.btn-change-key');

    if (copyBtn && keyInput) {
        copyBtn.addEventListener('click', function () {
            keyInput.select();
            navigator.clipboard.writeText(keyInput.value).then(function () {
                if (window.iziToast) {
                    iziToast.success({ message: 'API key copied', position: 'topRight' });
                }
            }).catch(function () {
                if (window.iziToast) {
                    iziToast.error({ message: 'Copy failed', position: 'topRight' });
                }
            });
        });
    }

    if (changeBtn && keyInput) {
        changeBtn.addEventListener('click', function () {
            const btn = this;
            btn.disabled = true;

            fetch('{{ route('user.api.regenerate-key') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                btn.disabled = false;
                if (json.status === '200') {
                    keyInput.value = json.api_key;
                    if (window.iziToast) {
                        iziToast.success({ message: json.message || 'API key changed', position: 'topRight' });
                    }
                } else if (window.iziToast) {
                    iziToast.error({ message: json.message || 'Failed to change key', position: 'topRight' });
                }
            })
            .catch(function () {
                btn.disabled = false;
                if (window.iziToast) {
                    iziToast.error({ message: 'Request failed', position: 'topRight' });
                }
            });
        });
    }
});
</script>
@endpush
