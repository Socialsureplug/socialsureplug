@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="card number-card">
        <h2 class="title">Buy Number</h2>
        <p class="desc">Select country and service below</p>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">
                    Select Server <span class="required">*</span>
                </label>
                <div class="select" id="select-server">
                    <button type="button" class="select-preview">
                        <span>Choose Server</span>
                        <i class="ti ti-chevron-down select-icon"></i>
                    </button>
                    <div class="select-dropdown">
                        <div class="select-search">
                            <input type="search" placeholder="Search..." class="form-control">
                        </div>
                        <div class="select-list">
                            @forelse($apis as $api)
                                <div class="select-item" data-api-id="{{ $api->id }}" data-name="{{ $api->name }}">{{ $api->name }}</div>
                            @empty
                                <div class="select-item">No API providers</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Select Country <span class="required">*</span>
                </label>
                <div class="select" id="select-country">
                    <button type="button" class="select-preview">
                        <span>Please select server first</span>
                        <i class="ti ti-chevron-down select-icon"></i>
                    </button>
                    <div class="select-dropdown">
                        <div class="select-search">
                            <input type="search" placeholder="Search..." class="form-control">
                        </div>
                        <div class="select-list" id="country-list">
                            <div class="select-item">Please select server first</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Select Service <span class="required">*</span>
                </label>
                <div class="select" id="select-service">
                    <button type="button" class="select-preview">
                        <span>Please select country first</span>
                        <i class="ti ti-chevron-down select-icon"></i>
                    </button>
                    <div class="select-dropdown">
                        <div class="select-search">
                            <input type="search" placeholder="Search..." class="form-control">
                        </div>
                        <div class="select-list select-price" id="service-list">
                            <div class="select-item">Please select country first</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <div class="summary">
                <span id="show_name">??</span><br>
                <strong id="show_amount">??</strong>
            </div>

            <button type="button" class="btn btn-primary" id="buy-numbers"><i class="ti ti-shopping-cart"></i> Buy
                Number</button>
        </div>
    </div>

    <div id="card-container" class="activation-grid"></div>
@endsection

@push('script')
    <script>
        (function() {
            const baseUrl = '{{ url('/') }}';
            const currency = '{{ optional($settings)->website_currency ?? 'NGN' }}';
            const fmtMoney = (amount) => currency + String(amount ?? '0');

            let apiId = '';
            let countryId = '';
            let serviceId = '';

            const countryList = document.getElementById('country-list');
            const serviceList = document.getElementById('service-list');

            function setPreview(selectId, text) {
                const span = document.querySelector('#' + selectId + ' .select-preview span');
                if (span) span.textContent = text;
            }

            function resetSummary() {
                document.getElementById('show_name').textContent = '??';
                document.getElementById('show_amount').textContent = currency + ' ??';
            }

            function clearSelectSearch(selectId) {
                const input = document.querySelector('#' + selectId + ' .select-search input');
                if (input) input.value = '';
                document.querySelectorAll('#' + selectId + ' .select-item').forEach(function(el) {
                    el.classList.remove('hidden');
                });
            }

            function clearService() {
                serviceId = '';
                setPreview('select-service', 'Please select country first');
                if (serviceList) {
                    serviceList.innerHTML = '<div class="select-item">Please select country first</div>';
                }
                clearSelectSearch('select-service');

                resetSummary();
            }

            document.getElementById('select-server')?.querySelector('.select-list')?.addEventListener('click', function(
                e) {
                const item = e.target.closest('.select-item');
                if (!item || !item.dataset.apiId) return;

                apiId = item.dataset.apiId;
                countryId = '';
                clearService();

                setPreview('select-country', 'Loading...');
                countryList.innerHTML = '<div class="select-item">Loading...</div>';
                clearSelectSearch('select-country');

                fetch(baseUrl + '/number/countries?api=' + apiId)
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {

                        const countries = data.countries || [];

                        countryList.innerHTML = '';
                        if (!countries.length) {
                            countryList.innerHTML = '<div class="select-item">No countries</div>';
                            setPreview('select-country', 'No countries');
                            iziToast.info({
                                message: 'No countries for this server',
                                position: 'topRight'
                            });
                            return;
                        }
                        setPreview('select-country', 'Choose Country');
                        clearSelectSearch('select-country');
                        countries.forEach(function(c) {
                            const div = document.createElement('div');
                            div.className = 'select-item';
                            div.dataset.countryId = c.id;
                            div.dataset.name = c.name;
                            div.textContent = c.name;
                            countryList.appendChild(div);
                        });
                    })
                    .catch(function() {
                        countryList.innerHTML = '<div class="select-item">Error</div>';
                        setPreview('select-country', 'Error');
                        iziToast.error({
                            message: 'Failed to load countries',
                            position: 'topRight'
                        });
                    });
            });

            document.getElementById('select-country')?.querySelector('.select-list')?.addEventListener('click',
                function(e) {
                    const item = e.target.closest('.select-item');
                    if (!item || !item.dataset.countryId) return;
                    if (!apiId) {
                        iziToast.error({
                            message: 'Please select server first',
                            position: 'topRight'
                        });
                        return;
                    }
                    countryId = item.dataset.countryId;
                    clearService();
                    setPreview('select-service', 'Loading...');
                    serviceList.innerHTML = '<div class="select-item">Loading...</div>';
                    clearSelectSearch('select-service');
                    fetch(baseUrl + '/number/services?api=' + apiId + '&country=' + countryId)
                        .then(function(res) {
                            return res.json();
                        })
                        .then(function(data) {
                            const services = data.service || [];
                            serviceList.innerHTML = '';
                            if (!services.length) {
                                serviceList.innerHTML = '<div class="select-item">No services</div>';
                                setPreview('select-service', 'No services');
                                iziToast.info({
                                    message: 'No services for this country',
                                    position: 'topRight'
                                });
                                return;
                            }
                            setPreview('select-service', 'Choose Service');
                            clearSelectSearch('select-service');
                            services.forEach(function(s) {
                                const div = document.createElement('div');
                                div.className = 'select-item';
                                div.dataset.serviceId = s.id;
                                div.dataset.name = s.service_name;
                                div.dataset.amount = fmtMoney(s.service_price);
                                div.innerHTML = s.service_name + ' <span class="price">' + fmtMoney(s.service_price) + '</span>';
                                serviceList.appendChild(div);
                            });
                        })
                        .catch(function() {
                            serviceList.innerHTML = '<div class="select-item">Error</div>';
                            setPreview('select-service', 'Error');
                            iziToast.error({
                                message: 'Failed to load services',
                                position: 'topRight'
                            });
                        });
                });

            const defaultLogo = 'https://i.ibb.co/ySRhxqh/default.png';
            const TIMER_SECONDS = 20 * 60;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            function fmtTime(sec) {
                if (sec <= 0) return '00:00';
                const m = Math.floor(sec / 60);
                const s = sec % 60;
                return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
            }

            function remainingSeconds(buyTimeIso) {
                const buy = new Date(buyTimeIso).getTime();
                const elapsed = Math.floor((Date.now() - buy) / 1000);
                return Math.max(0, TIMER_SECONDS - elapsed);
            }

            function addCard(order) {
                const orderId = order.order_id;
                const fullNumber = '+' + (order.number || '').replace(/^\+/, '');
                const serviceName = order.service_name || 'Service';
                const serviceAmount = fmtMoney(order.service_price || '0');
                const statusText = order.sms_text ? ('Code: ' + order.sms_text) : 'Waiting for SMS.';
                const isCode = !!order.sms_text;
                let remaining = remainingSeconds(order.buy_time || new Date().toISOString());
                const safeId = orderId.replace(/[^a-zA-Z0-9-_]/g, '_');
                const statusId = 'act-sms-' + safeId;
                const timerId = 'act-timer-' + safeId;
                const container = document.getElementById('card-container');

                const cardHtml =
                    '<div class="activation-card card" data-order-id="' + orderId.replace(/"/g, '&quot;') + '">' +
                    '  <div class="activation-header">' +
                    '    <div class="activation-image"><img src="' + defaultLogo + '" alt=""></div>' +
                    '    <div class="activation-info">' +
                    '      <div class="activation-number"><span>' + fullNumber.replace(/</g, '&lt;') + '</span>' +
                    '        <button type="button" class="copy-number" title="Copy number"><i class="ti ti-copy"></i></button></div>' +
                    '      <div class="activation-meta">' + serviceName.replace(/</g, '&lt;') + ' - ' + serviceAmount
                    .replace(/</g, '&lt;') + '</div>' +
                    '    </div></div>' +
                    '  <div class="activation-status">' +
                    '    <p class="activation-status-text' + (isCode ? ' code-received' : '') + '" id="' + statusId +
                    '">' + statusText.replace(/</g, '&lt;') + '</p>' +
                    '    <div class="activation-timer" id="' + timerId +
                    '"><i class="ti ti-clock"></i> <span class="timer-value">' + fmtTime(remaining) + '</span></div>' +
                    '  </div>' +
                    '  <div class="activation-actions">' +
                    '    <button type="button" class="btn btn-done done-btn"' + (isCode ? '' : ' disabled') +
                    '><i class="ti ti-check"></i> Done</button>' +
                    '    <button type="button" class="btn btn-copy copy-btn"><i class="ti ti-copy"></i> Copy</button>' +
                    '    <button type="button" class="btn btn-cancel cancel-btn"' + (isCode ? ' disabled' : '') +
                    '><i class="ti ti-trash"></i> Cancel</button>' +
                    '  </div></div>';

                container.insertAdjacentHTML('beforeend', cardHtml);
                const card = container.lastElementChild;
                const statusEl = document.getElementById(statusId);
                const timerValEl = document.getElementById(timerId)?.querySelector('.timer-value');
                let pollInterval = null;
                let timerInterval = null;

                function stopCard() {
                    if (pollInterval) clearInterval(pollInterval);
                    if (timerInterval) clearInterval(timerInterval);
                    pollInterval = timerInterval = null;
                }

                if (remaining > 0 && !isCode) {
                    timerInterval = setInterval(function() {
                        remaining--;
                        if (timerValEl) timerValEl.textContent = fmtTime(remaining);
                        if (remaining <= 0) {
                            stopCard();
                            fetch(baseUrl + '/number/message?order_id=' + encodeURIComponent(orderId))
                                .then(r => r.json())
                                .then(function(msg) {
                                    if (msg.status === '500') card.remove();
                                });
                        }
                    }, 1000);
                    pollInterval = setInterval(function() {
                        fetch(baseUrl + '/number/message?order_id=' + encodeURIComponent(orderId))
                            .then(r => r.json())
                            .then(function(msg) {
                                if (msg.status === '200' && msg.sms) {
                                    if (statusEl) {
                                        statusEl.textContent = 'Code: ' + msg.sms;
                                        statusEl.classList.add('code-received');
                                    }
                                    card.querySelector('.cancel-btn').disabled = true;
                                    card.querySelector('.done-btn').disabled = false;
                                    stopCard();
                                } else if (msg.status === '500') {
                                    stopCard();
                                    card.remove();
                                }
                            });
                    }, 3000);
                }

                function copyCard() {
                    const text = (statusEl && statusEl.classList.contains('code-received')) ?
                        statusEl.textContent.replace(/^Code:\s*/, '') :
                        fullNumber;
                    navigator.clipboard.writeText(text).then(function() {
                        iziToast.success({
                            message: 'Copied',
                            position: 'topRight'
                        });
                    });
                }

                card.querySelector('.copy-number').addEventListener('click', copyCard);
                card.querySelector('.copy-btn').addEventListener('click', copyCard);

                card.querySelector('.done-btn').addEventListener('click', function() {
                    if (!statusEl?.classList.contains('code-received')) {
                        iziToast.error({
                            message: 'Wait for SMS code first',
                            position: 'topRight'
                        });
                        return;
                    }
                    const btn = this;
                    btn.disabled = true;
                    fetch(baseUrl + '/number/dismiss', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            order_id: orderId
                        })
                    }).then(r => r.json()).then(function(res) {
                        if (res.status === '200') {
                            stopCard();
                            card.remove();
                            iziToast.success({
                                message: res.message || 'Done',
                                position: 'topRight'
                            });
                        } else {
                            btn.disabled = false;
                            iziToast.error({
                                message: res.message || 'Failed',
                                position: 'topRight'
                            });
                        }
                    });
                });

                card.querySelector('.cancel-btn').addEventListener('click', function() {
                    if (statusEl?.classList.contains('code-received')) {
                        iziToast.error({
                            message: 'Cannot cancel after SMS received',
                            position: 'topRight'
                        });
                        return;
                    }
                    const btn = this;
                    btn.disabled = true;
                    fetch(baseUrl + '/number/cancel', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            order_id: orderId
                        })
                    }).then(r => r.json()).then(function(res) {
                        if (res.status === '200') {
                            stopCard();
                            card.remove();
                            iziToast.success({
                                message: res.message,
                                position: 'topRight'
                            });
                        } else {
                            btn.disabled = false;
                            iziToast.error({
                                message: res.message || 'Cancel failed',
                                position: 'topRight'
                            });
                        }
                    });
                });

            }

            fetch(baseUrl + '/number/active-orders')
                .then(r => r.json())
                .then(function(res) {
                    if (res.status === '200' && res.data?.length) {
                        res.data.forEach(addCard);
                    }
                });

            document.getElementById('select-service')?.querySelector('.select-list')?.addEventListener('click',
                function(e) {
                    const item = e.target.closest('.select-item');
                    if (!item || !item.dataset.serviceId) return;
                    serviceId = item.dataset.serviceId;
                    document.getElementById('show_name').textContent = item.dataset.name || '??';
                    document.getElementById('show_amount').textContent = item.dataset.amount || (currency + ' ??');
                });

            document.getElementById('buy-numbers')?.addEventListener('click', function() {
                if (!apiId) {
                    iziToast.error({
                        message: 'Please select server',
                        position: 'topRight'
                    });
                    return;
                }
                if (!countryId) {
                    iziToast.error({
                        message: 'Please select country',
                        position: 'topRight'
                    });
                    return;
                }
                if (!serviceId) {
                    iziToast.error({
                        message: 'Please select service',
                        position: 'topRight'
                    });
                    return;
                }
                const btn = this;
                btn.disabled = true;
                fetch(baseUrl + '/number/buy', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            api_id: parseInt(apiId, 10),
                            country_id: parseInt(countryId, 10),
                            service_id: parseInt(serviceId, 10)
                        })
                    })
                    .then(r => r.json())
                    .then(function(res) {
                        btn.disabled = false;
                        if (res.status === '200' && res.data) {
                            addCard(res.data);
                            iziToast.success({
                                message: res.message,
                                position: 'topRight'
                            });
                        } else {
                            iziToast.error({
                                message: res.message || 'Purchase failed',
                                position: 'topRight'
                            });
                        }
                    })
                    .catch(function() {
                        btn.disabled = false;
                        iziToast.error({
                            message: 'Request failed',
                            position: 'topRight'
                        });
                    });
            });


        })();
    </script>
@endpush
