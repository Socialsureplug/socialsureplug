@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="grid">
        @if(($paystackEnabled ?? false) || ($flutterwaveEnabled ?? false) || ($korapayEnabled ?? false) || ($bachsEnabled ?? false) || ($bankEnabled ?? false))
        <div class="card gateway-card">
            <h2 class="title">Fund Wallet</h2>
            <div class="gateway-list">
                @if($paystackEnabled ?? false)
                {{-- Paystack --}}
                <div class="gateway-item" data-method="paystack">
                    <div class="gateway-left">
                        <span class="gateway-icon">
                            <i class="ti ti-credit-card"></i>
                        </span>
                        <div class="gateway-info">
                            <span class="gateway-title">Paystack</span>
                            <span class="gateway-desc">{{ $paystackCurrency ?? 'NGN' }} · Card / Bank / USSD</span>
                        </div>
                    </div>
                    <i class="ti ti-chevron-right gateway-arrow"></i>
                </div>
                @endif

                @if($flutterwaveEnabled ?? false)
                {{-- Flutterwave --}}
                <div class="gateway-item" data-method="flutterwave">
                    <div class="gateway-left">
                        <span class="gateway-icon">
                            <i class="ti ti-wave-square"></i>
                        </span>
                        <div class="gateway-info">
                            <span class="gateway-title">Flutterwave</span>
                            <span class="gateway-desc">{{ $flutterwaveCurrency ?? 'NGN' }} · Card / Transfer / USSD</span>
                        </div>
                    </div>
                    <i class="ti ti-chevron-right gateway-arrow"></i>
                </div>
                @endif

                @if($korapayEnabled ?? false)
                {{-- KoraPay --}}
                <div class="gateway-item" data-method="korapay">
                    <div class="gateway-left">
                        <span class="gateway-icon">
                            <i class="ti ti-wallet"></i>
                        </span>
                        <div class="gateway-info">
                            <span class="gateway-title">KoraPay</span>
                            <span class="gateway-desc">{{ $korapayCurrency ?? 'NGN' }} · Card / Bank transfer</span>
                        </div>
                    </div>
                    <i class="ti ti-chevron-right gateway-arrow"></i>
                </div>
                @endif

                @if($bachsEnabled ?? false)
                {{-- Bachs --}}
                <div class="gateway-item" data-method="bachs">
                    <div class="gateway-left">
                        <span class="gateway-icon">
                            <i class="ti ti-world"></i>
                        </span>
                        <div class="gateway-info">
                            <span class="gateway-title">Bachs</span>
                            <span class="gateway-desc">{{ $bachsCurrency ?? 'NGN' }} · Card / Bank / Mobile money</span>
                        </div>
                    </div>
                    <i class="ti ti-chevron-right gateway-arrow"></i>
                </div>
                @endif

                @if($bankEnabled ?? false)
                {{-- Bank transfer (manual) --}}
                <div class="gateway-item" data-method="bank">
                    <div class="gateway-left">
                        <span class="gateway-icon">
                            <i class="ti ti-building-bank"></i>
                        </span>
                        <div class="gateway-info">
                            <span class="gateway-title">Bank Transfer</span>
                            <span class="gateway-desc">{{ $bankCurrency ?? 'NGN' }} · Manual approval</span>
                        </div>
                    </div>
                    <i class="ti ti-chevron-right gateway-arrow"></i>
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="card gateway-card">
            <h2 class="title">Fund Wallet</h2>
            <p class="text-muted mb-0">No payment method is currently available. Please try again later.</p>
        </div>
        @endif

        <div class="card amount-card">
            <h2 class="title">Enter Amount</h2>
            <form class="amount-form" id="fund-form" method="post">
                @csrf
                <input type="hidden" name="method" id="selected-method">
                <div class="form-group">
                    <label class="form-label">Amount</label>
                    <input type="text"
                           class="form-control"
                           name="amount"
                           id="fund-amount"
                           placeholder="Enter amount"
                           inputmode="decimal"
                           aria-label="Amount">
                </div>
                <p class="hint" id="payment-hint">Select a payment method to continue.</p>
                <button type="submit" class="btn btn-primary">
                    <span>Proceed to Payment</span>
                    <i class="ti ti-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
@endsection

@push('script')
    {{-- Paystack & Flutterwave SDKs --}}
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script src="https://checkout.flutterwave.com/v3.js"></script>

    <script>
        (function () {
            'use strict';

            const gateways = document.querySelectorAll('.gateway-item');
            const methodInput = document.getElementById('selected-method');
            const hintEl = document.getElementById('payment-hint');
            const form = document.getElementById('fund-form');
            const amountInput = document.getElementById('fund-amount');
            const gatewayLimits = @json($gatewayLimits ?? []);

            let selectedMethod = null;

            gateways.forEach(function (item) {
                item.addEventListener('click', function () {
                    gateways.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    selectedMethod = this.getAttribute('data-method');
                    methodInput.value = selectedMethod;
                    updateHint();
                });
            });

            amountInput.addEventListener('input', updateHint);

            function formatLimit(min, max) {
                const minFormatted = Number(min || 0).toFixed(2);
                if (max === null || max === undefined || Number.isNaN(Number(max))) {
                    return `Min: ${minFormatted}`;
                }

                return `Min: ${minFormatted}, Max: ${Number(max).toFixed(2)}`;
            }

            function updateHint() {
                if (!selectedMethod) {
                    hintEl.textContent = 'Select a payment method to continue.';
                    return;
                }

                const limits = gatewayLimits[selectedMethod] || { min: 0, max: null };
                const limitText = formatLimit(limits.min, limits.max);

                if (selectedMethod === 'paystack') {
                    hintEl.textContent = `You will be redirected to Paystack secure popup. ${limitText}`;
                } else if (selectedMethod === 'flutterwave') {
                    hintEl.textContent = `You will be redirected to Flutterwave secure checkout. ${limitText}`;
                } else if (selectedMethod === 'bank') {
                    hintEl.textContent = `You will see bank details and upload your payment proof. ${limitText}`;
                } else if (selectedMethod === 'korapay') {
                    hintEl.textContent = `You will be redirected to KoraPay secure checkout. ${limitText}`;
                } else if (selectedMethod === 'bachs') {
                    hintEl.textContent = `You will be redirected to Bachs secure checkout. ${limitText}`;
                }
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const rawAmount = (amountInput.value || '').trim();
                const amount = parseFloat(rawAmount);

                if (!selectedMethod) {
                    window.iziToast && iziToast.error({message: 'Please select a payment method', position: 'topRight'});
                    return;
                }

                if (!rawAmount || isNaN(amount) || amount <= 0) {
                    window.iziToast && iziToast.error({message: 'Please enter a valid amount', position: 'topRight'});
                    return;
                }

                const limits = gatewayLimits[selectedMethod] || { min: 0, max: null };
                const min = parseFloat(limits.min ?? 0);
                const max = limits.max === null || limits.max === undefined ? null : parseFloat(limits.max);

                if (!isNaN(min) && amount < min) {
                    window.iziToast && iziToast.error({message: `Minimum amount is ${min.toFixed(2)}`, position: 'topRight'});
                    return;
                }

                if (max !== null && !isNaN(max) && amount > max) {
                    window.iziToast && iziToast.error({message: `Maximum amount is ${max.toFixed(2)}`, position: 'topRight'});
                    return;
                }

                if (selectedMethod === 'bank') {
                    // Normal POST to Laravel for bank transfer
                    form.action = '{{ route('user.payment.bank.initiate') }}';
                    form.method = 'POST';
                    form.submit();
                    return;
                }

                if (selectedMethod === 'paystack') {
                    payWithPaystack(amount);
                } else if (selectedMethod === 'flutterwave') {
                    payWithFlutterwave(amount);
                } else if (selectedMethod === 'korapay') {
                    payWithKorapay(amount);
                } else if (selectedMethod === 'bachs') {
                    payWithBachs(amount);
                }
            });

            function payWithPaystack(amount) {
                const key = @json($paystackPublicKey);
                if (!key) {
                    window.iziToast && iziToast.error({message: 'Paystack is not available.', position: 'topRight'});
                    return;
                }

                const email = @json(auth()->user()->email);
                const currency = @json($paystackCurrency ?? 'NGN');
                const ref = 'PSK-' + Date.now();

                const handler = PaystackPop.setup({
                    key: key,
                    email: email,
                    amount: Math.round(amount * 100), // kobo
                    currency: currency,
                    ref: ref,
                    callback: function (response) {
                        if (response && response.reference) {
                            window.location = '{{ route('user.payment.paystack.callback') }}' + '?reference=' + encodeURIComponent(response.reference);
                        } else {
                            window.iziToast && iziToast.error({message: 'No reference returned from Paystack.', position: 'topRight'});
                        }
                    },
                    onClose: function () {
                        window.iziToast && iziToast.info({message: 'Payment window closed.', position: 'topRight'});
                    }
                });

                handler.openIframe();
            }

            function payWithFlutterwave(amount) {
                const publicKey = @json($flutterwavePublicKey);
                if (!publicKey) {
                    window.iziToast && iziToast.error({message: 'Flutterwave is not available.', position: 'topRight'});
                    return;
                }

                const txRef = 'FLW-' + Date.now();
                const currency = @json($flutterwaveCurrency ?? 'NGN');
                const user = @json([
                    'email' => auth()->user()->email,
                    'name'  => trim((auth()->user()->fname ?? '') . ' ' . (auth()->user()->lname ?? '')),
                ]);

                FlutterwaveCheckout({
                    public_key: publicKey,
                    tx_ref: txRef,
                    amount: amount,
                    currency: currency,
                    payment_options: "card, banktransfer, ussd",
                    redirect_url: "{{ route('user.payment.flutterwave.callback') }}",
                    customer: {
                        email: user.email,
                        name: user.name || user.email,
                    },
                    customizations: {
                        title: "{{ config('app.name', 'Website') }}",
                        description: "Wallet funding",
                    },
                });
            }

            function payWithKorapay(amount) {
                fetch('{{ route('user.payment.korapay.initiate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: amount
                    })
                })
                .then(function (res) {
                    return res.json().then(function (json) {
                        return { ok: res.ok, data: json };
                    });
                })
                .then(function (result) {
                    if (!result.ok || !result.data.checkout_url) {
                        throw new Error(result.data.message || 'Unable to initialize KoraPay payment.');
                    }
                    window.location = result.data.checkout_url;
                })
                .catch(function (err) {
                    window.iziToast && iziToast.error({message: err.message || 'KoraPay initialization failed.', position: 'topRight'});
                });
            }
            function payWithBachs(amount) {
                fetch('{{ route('user.payment.bachs.initiate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: amount
                    })
                })
                .then(function (res) {
                    return res.json().then(function (json) {
                        return { ok: res.ok, data: json };
                    });
                })
                .then(function (result) {
                    if (!result.ok || !result.data.checkout_url) {
                        throw new Error(result.data.message || 'Unable to initialize Bachs payment.');
                    }
                    window.location = result.data.checkout_url;
                })
                .catch(function (err) {
                    window.iziToast && iziToast.error({message: err.message || 'Bachs initialization failed.', position: 'topRight'});
                });
            }
        })();
    </script>
@endpush