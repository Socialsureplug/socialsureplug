@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <style>
        .rn-wrap{display:flex;flex-direction:column;gap:18px}

        .rn-select-row{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
        @media (max-width:760px){.rn-select-row{grid-template-columns:1fr}}

        .rn-footer{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,.08);flex-wrap:wrap}
        .rn-footer .summary{display:flex;flex-direction:column;gap:2px}
        .rn-footer .summary span{font-size:12px;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.04em}
        .rn-footer .summary strong{font-size:18px}

        .service-panel{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:14px;overflow:hidden}
        .service-toolbar{display:flex;gap:10px;padding:14px;border-bottom:1px solid rgba(255,255,255,.08);flex-wrap:wrap}
        .service-toolbar .service-search{flex:1;min-width:160px;position:relative}
        .service-toolbar .service-search input{width:100%;padding:9px 12px 9px 34px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);color:inherit}
        .service-toolbar .service-search i{position:absolute;left:11px;top:50%;transform:translateY(-50%);opacity:.5;font-size:15px}
        .fav-toggle{display:flex;align-items:center;gap:6px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);color:inherit;border-radius:8px;padding:9px 14px;font-size:13px;white-space:nowrap;cursor:pointer}
        .fav-toggle i{color:#f4c542}
        .fav-toggle.active{background:#f4c542;color:#1a1a1a;border-color:#f4c542}
        .fav-toggle.active i{color:#1a1a1a}

        .service-list{max-height:520px;overflow-y:auto}
        .service-empty{padding:28px 16px;text-align:center;color:rgba(255,255,255,.5);font-size:13px}

        .service-row{border-bottom:1px solid rgba(255,255,255,.06)}
        .service-row:last-child{border-bottom:none}
        .service-row-main{display:flex;align-items:center;gap:12px;padding:12px 14px;cursor:pointer}
        .service-row-main:hover{background:rgba(255,255,255,.03)}
        .service-row.selected .service-row-main{background:rgba(46,204,160,.08)}

        .service-avatar{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:14px;color:#fff;flex-shrink:0}
        .service-body{flex:1;min-width:0}
        .service-name{font-size:14px;font-weight:500}
        .service-meta{font-size:12px;color:rgba(255,255,255,.5);margin-top:2px}
        .service-row.selected .service-name{color:#2ecca0}

        .service-star{background:none;border:none;cursor:pointer;color:rgba(255,255,255,.35);font-size:17px;flex-shrink:0;padding:4px}
        .service-star.active{color:#f4c542}
        .service-chevron{opacity:.4;font-size:16px;transition:transform .15s ease;flex-shrink:0}
        .service-row.expanded .service-chevron{transform:rotate(180deg)}

        .service-operators{display:none;padding:4px 14px 14px 62px;flex-wrap:wrap;gap:8px}
        .service-row.expanded .service-operators{display:flex}
        .operator-chip{border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.04);border-radius:20px;padding:7px 13px;font-size:12.5px;cursor:pointer;display:flex;align-items:center;gap:6px}
        .operator-chip .op-price{color:#2ecca0;font-weight:600}
        .operator-chip.selected{border-color:#2ecca0;background:rgba(46,204,160,.12)}
        .operator-chip.disabled{opacity:.4;cursor:not-allowed}
        .operator-loading,.operator-error{padding:4px 14px 14px 62px;font-size:12.5px;color:rgba(255,255,255,.5)}
    </style>

    <div class="info-panel success">
        <h3 class="title">How it works</h3>
        <ul class="list">
            <li>Server, country (United States) and duration (1 week) are pre-selected — change them anytime</li>
            <li>Browse the list below, tap a service to see operators & price, then pick one</li>
            <li>Charged immediately on confirmation — no cancellations or refunds after rent</li>
            <li>SMS updates automatically on your card, number expires at the end of your rental period</li>
            <li>Click Done to dismiss a card when you're finished</li>
        </ul>
    </div>

    <div class="card number-card">
        <h2 class="title">Rent Number</h2>
        <p class="desc">Defaults are applied below — adjust server, country or duration if you need to</p>

        <div class="rn-wrap">
            <div class="rn-select-row">
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
                        Rental Duration <span class="required">*</span>
                    </label>
                    <div class="select" id="select-duration">
                        <button type="button" class="select-preview">
                            <span>Please select country first</span>
                            <i class="ti ti-chevron-down select-icon"></i>
                        </button>
                        <div class="select-dropdown">
                            <div class="select-list" id="duration-list">
                                <div class="select-item">Please select country first</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rn-footer">
                <div class="summary">
                    <span id="show_name">??</span>
                    <strong id="show_amount">??</strong>
                </div>

                <button type="button" class="btn btn-primary" id="rent-number">
                    <i class="ti ti-phone"></i> Rent Number
                </button>
            </div>

            <div class="service-panel">
                <div class="service-toolbar">
                    <div class="service-search">
                        <i class="ti ti-search"></i>
                        <input type="search" id="service-search-input" placeholder="Enter service name" autocomplete="off">
                    </div>
                    <button type="button" class="fav-toggle" id="fav-toggle">
                        <i class="ti ti-star-filled"></i> Show only favorites
                    </button>
                </div>
                <div class="service-list" id="service-list-panel">
                    <div class="service-empty">Select server, country and duration to load services</div>
                </div>
            </div>
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
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const dismissedKey = 'renting_dismissed_cards';

            function getDismissedCards() {
                try {
                    return JSON.parse(sessionStorage.getItem(dismissedKey) || '[]');
                } catch (e) {
                    return [];
                }
            }

            function dismissCard(orderId) {
                const dismissed = getDismissedCards();
                if (!dismissed.includes(orderId)) {
                    dismissed.push(orderId);
                    sessionStorage.setItem(dismissedKey, JSON.stringify(dismissed));
                }
            }

            let apiId = '';
            let countryId = '';
            let dtype = '';
            let dcount = '';
            let serviceId = '';
            let serviceName = '';
            let serviceOperatorId = '';

            // Cache of operators already fetched per service id, so re-expanding a row is instant.
            const operatorsCache = {};
            let favoritesOnly = false;
            let serviceSearchTerm = '';
            let currentServices = [];

            const countryList = document.getElementById('country-list');
            const durationList = document.getElementById('duration-list');
            const serviceListPanel = document.getElementById('service-list-panel');
            const favToggleBtn = document.getElementById('fav-toggle');
            const serviceSearchInput = document.getElementById('service-search-input');

            function setPreview(selectId, text) {
                const span = document.querySelector('#' + selectId + ' .select-preview span');
                if (span) span.textContent = text;
            }

            function resetSummary() {
                serviceOperatorId = '';
                document.getElementById('show_name').textContent = '??';
                document.getElementById('show_amount').textContent = currency + ' ??';
                document.querySelectorAll('.service-row.selected').forEach(function(el) { el.classList.remove('selected'); });
                document.querySelectorAll('.operator-chip.selected').forEach(function(el) { el.classList.remove('selected'); });
            }

            function clearSelectSearch(selectId) {
                const input = document.querySelector('#' + selectId + ' .select-search input');
                if (input) input.value = '';
                document.querySelectorAll('#' + selectId + ' .select-item').forEach(function(el) {
                    el.classList.remove('hidden');
                });
            }

            function durationLabel(dtypeVal, dcountVal) {
                const n = parseInt(dcountVal, 10) || 1;
                if (dtypeVal === 'day') return n + (n === 1 ? ' Day' : ' Days');
                if (dtypeVal === 'week') return n + (n === 1 ? ' Week' : ' Weeks');
                if (dtypeVal === 'month') return n + (n === 1 ? ' Month' : ' Months');
                return n + ' ' + dtypeVal;
            }

            // ---------- Favorites (per server, stored locally on this device) ----------
            function favoritesKey() {
                return 'rn_favorite_services_' + (apiId || 'default');
            }

            function getFavorites() {
                try {
                    return new Set(JSON.parse(localStorage.getItem(favoritesKey()) || '[]'));
                } catch (e) {
                    return new Set();
                }
            }

            function toggleFavorite(svcId) {
                const favs = getFavorites();
                const key = String(svcId);
                if (favs.has(key)) {
                    favs.delete(key);
                } else {
                    favs.add(key);
                }
                localStorage.setItem(favoritesKey(), JSON.stringify(Array.from(favs)));
                return favs.has(key);
            }

            // ---------- Service list rendering ----------
            const avatarPalette = ['#e0588b', '#5b8def', '#2ecca0', '#f4a542', '#9b6bf2', '#e0735b', '#3fbfd6', '#d6c23f'];

            function avatarColor(name) {
                let hash = 0;
                for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
                return avatarPalette[Math.abs(hash) % avatarPalette.length];
            }

            function clearService() {
                serviceId = '';
                serviceName = '';
                resetSummary();
                currentServices = [];
                serviceListPanel.innerHTML = '<div class="service-empty">Select server, country and duration to load services</div>';
            }

            function buildServiceRow(svc) {
                const row = document.createElement('div');
                row.className = 'service-row';
                row.dataset.serviceId = svc.id;
                row.dataset.name = (svc.service_name || '').toLowerCase();

                const favs = getFavorites();
                const isFav = favs.has(String(svc.id));
                const initial = (svc.service_name || '?').trim().charAt(0).toUpperCase();

                row.innerHTML =
                    '<div class="service-row-main">' +
                    '  <div class="service-avatar" style="background:' + avatarColor(svc.service_name || '') + '">' + initial + '</div>' +
                    '  <div class="service-body">' +
                    '    <div class="service-name">' + escapeHtml(svc.service_name) + '</div>' +
                    '    <div class="service-meta">Tap to view operators &amp; price</div>' +
                    '  </div>' +
                    '  <button type="button" class="service-star' + (isFav ? ' active' : '') + '" title="Favorite">' +
                    '    <i class="ti ti-star' + (isFav ? '-filled' : '') + '"></i>' +
                    '  </button>' +
                    '  <i class="ti ti-chevron-down service-chevron"></i>' +
                    '</div>' +
                    '<div class="service-operators-wrap"></div>';

                row.querySelector('.service-star').addEventListener('click', function(e) {
                    e.stopPropagation();
                    const nowFav = toggleFavorite(svc.id);
                    this.classList.toggle('active', nowFav);
                    this.querySelector('i').className = 'ti ti-star' + (nowFav ? '-filled' : '');
                    if (favoritesOnly && !nowFav) {
                        applyServiceFilter();
                    }
                });

                row.querySelector('.service-row-main').addEventListener('click', function() {
                    toggleRowExpand(row, svc);
                });

                return row;
            }

            function toggleRowExpand(row, svc) {
                const wasExpanded = row.classList.contains('expanded');
                document.querySelectorAll('.service-row.expanded').forEach(function(el) {
                    if (el !== row) el.classList.remove('expanded');
                });
                row.classList.toggle('expanded', !wasExpanded);
                if (!wasExpanded) {
                    loadOperatorsForRow(row, svc);
                }
            }

            function loadOperatorsForRow(row, svc) {
                const wrap = row.querySelector('.service-operators-wrap');

                if (operatorsCache[svc.id]) {
                    renderOperatorChips(wrap, row, svc, operatorsCache[svc.id]);
                    return;
                }

                wrap.innerHTML = '<div class="operator-loading">Loading operators...</div>';

                fetch(baseUrl + '/renting/operators?service_id=' + svc.id)
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        const operators = data.operators || [];
                        operatorsCache[svc.id] = operators;
                        renderOperatorChips(wrap, row, svc, operators);
                    })
                    .catch(function() {
                        wrap.innerHTML = '<div class="operator-error">Failed to load operators</div>';
                    });
            }

            function renderOperatorChips(wrap, row, svc, operators) {
                if (!operators.length) {
                    wrap.innerHTML = '<div class="operator-error">No operators in stock for this service</div>';
                    return;
                }
                const container = document.createElement('div');
                container.className = 'service-operators';
                operators.forEach(function(op) {
                    const chip = document.createElement('button');
                    chip.type = 'button';
                    const inStock = (op.stock || 0) > 0;
                    chip.className = 'operator-chip' + (inStock ? '' : ' disabled');
                    chip.disabled = !inStock;
                    chip.dataset.operatorId = op.id;
                    chip.innerHTML = escapeHtml(op.operator_name) + ' <span class="op-price">' + fmtMoney(op.price) + '</span> · ' + op.stock + ' left';

                    chip.addEventListener('click', function(e) {
                        e.stopPropagation();
                        document.querySelectorAll('.operator-chip.selected').forEach(function(el) { el.classList.remove('selected'); });
                        document.querySelectorAll('.service-row.selected').forEach(function(el) { el.classList.remove('selected'); });

                        chip.classList.add('selected');
                        row.classList.add('selected');

                        serviceId = svc.id;
                        serviceName = svc.service_name;
                        serviceOperatorId = op.id;

                        document.getElementById('show_name').textContent = serviceName + ' · ' + op.operator_name;
                        document.getElementById('show_amount').textContent = fmtMoney(op.price);
                    });

                    container.appendChild(chip);
                });
                wrap.innerHTML = '';
                wrap.appendChild(container);
            }

            function applyServiceFilter() {
                const favs = getFavorites();
                serviceListPanel.innerHTML = '';

                const filtered = currentServices.filter(function(svc) {
                    const matchesSearch = !serviceSearchTerm || (svc.service_name || '').toLowerCase().includes(serviceSearchTerm);
                    const matchesFav = !favoritesOnly || favs.has(String(svc.id));
                    return matchesSearch && matchesFav;
                });

                if (!filtered.length) {
                    serviceListPanel.innerHTML = '<div class="service-empty">No services match your filters</div>';
                    return;
                }

                filtered.forEach(function(svc) {
                    serviceListPanel.appendChild(buildServiceRow(svc));
                });
            }

            favToggleBtn.addEventListener('click', function() {
                favoritesOnly = !favoritesOnly;
                this.classList.toggle('active', favoritesOnly);
                applyServiceFilter();
            });

            serviceSearchInput.addEventListener('input', function() {
                serviceSearchTerm = this.value.trim().toLowerCase();
                applyServiceFilter();
            });

            // ---------- Cascading loaders ----------
            function clearDuration() {
                dtype = '';
                dcount = '';
                setPreview('select-duration', 'Please select country first');
                if (durationList) {
                    durationList.innerHTML = '<div class="select-item">Please select country first</div>';
                }
                clearSelectSearch('select-duration');
            }

            function loadDurations() {
                if (!apiId || !countryId) {
                    clearDuration();
                    clearService();
                    return;
                }

                dtype = '';
                dcount = '';
                clearService();
                setPreview('select-duration', 'Loading...');
                durationList.innerHTML = '<div class="select-item">Loading...</div>';
                clearSelectSearch('select-duration');

                fetch(baseUrl + '/renting/durations?api=' + apiId + '&country=' + countryId)
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        const durations = data.durations || [];
                        durationList.innerHTML = '';
                        if (!durations.length) {
                            durationList.innerHTML = '<div class="select-item">No durations</div>';
                            setPreview('select-duration', 'No durations');
                            iziToast.info({ message: 'No rental durations for this country', position: 'topRight' });
                            return;
                        }
                        setPreview('select-duration', 'Choose Duration');
                        durations.forEach(function(d) {
                            const div = document.createElement('div');
                            div.className = 'select-item';
                            div.dataset.dtype = d.dtype;
                            div.dataset.dcount = d.dcount;
                            div.dataset.label = d.label;
                            div.textContent = d.label;
                            durationList.appendChild(div);
                        });
                        autoSelectDuration();
                    })
                    .catch(function() {
                        durationList.innerHTML = '<div class="select-item">Error</div>';
                        setPreview('select-duration', 'Error');
                        iziToast.error({ message: 'Failed to load durations', position: 'topRight' });
                    });
            }

            function autoSelectDuration() {
                const items = Array.from(durationList.querySelectorAll('.select-item[data-dtype]'));
                if (!items.length) return;
                const oneWeek = items.find(function(it) {
                    return it.dataset.dtype === 'week' && parseInt(it.dataset.dcount, 10) === 1;
                });
                (oneWeek || items[0]).click();
            }

            function loadServices() {
                if (!apiId || !countryId || !dtype || !dcount) {
                    clearService();
                    return;
                }

                serviceId = '';
                serviceName = '';
                resetSummary();
                serviceListPanel.innerHTML = '<div class="service-empty">Loading services...</div>';

                const qs = 'api=' + apiId + '&country=' + countryId + '&dtype=' + encodeURIComponent(dtype) + '&dcount=' + dcount;
                fetch(baseUrl + '/renting/services?' + qs)
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        currentServices = data.services || [];
                        if (!currentServices.length) {
                            serviceListPanel.innerHTML = '<div class="service-empty">No services for this selection</div>';
                            iziToast.info({ message: 'No services for this selection', position: 'topRight' });
                            return;
                        }
                        applyServiceFilter();
                    })
                    .catch(function() {
                        serviceListPanel.innerHTML = '<div class="service-empty">Failed to load services</div>';
                        iziToast.error({ message: 'Failed to load services', position: 'topRight' });
                    });
            }

            document.getElementById('select-server')?.querySelector('.select-list')?.addEventListener('click', function(e) {
                const item = e.target.closest('.select-item');
                if (!item || !item.dataset.apiId) return;

                apiId = item.dataset.apiId;
                setPreview('select-server', item.dataset.name || 'Server');
                countryId = '';
                clearDuration();
                clearService();

                setPreview('select-country', 'Loading...');
                countryList.innerHTML = '<div class="select-item">Loading...</div>';
                clearSelectSearch('select-country');

                fetch(baseUrl + '/renting/countries?api=' + apiId)
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        const countries = data.countries || [];
                        countryList.innerHTML = '';
                        if (!countries.length) {
                            countryList.innerHTML = '<div class="select-item">No countries</div>';
                            setPreview('select-country', 'No countries');
                            iziToast.info({ message: 'No countries for this server', position: 'topRight' });
                            return;
                        }
                        setPreview('select-country', 'Choose Country');
                        countries.forEach(function(c) {
                            const div = document.createElement('div');
                            div.className = 'select-item';
                            div.dataset.countryId = c.id;
                            div.dataset.name = c.name;
                            div.textContent = c.name;
                            countryList.appendChild(div);
                        });
                        autoSelectCountry();
                    })
                    .catch(function() {
                        countryList.innerHTML = '<div class="select-item">Error</div>';
                        setPreview('select-country', 'Error');
                        iziToast.error({ message: 'Failed to load countries', position: 'topRight' });
                    });
            });

            function autoSelectCountry() {
                const items = Array.from(countryList.querySelectorAll('.select-item[data-country-id]'));
                if (!items.length) return;
                const us = items.find(function(it) {
                    return /united\s*states|^u\.?s\.?a?\.?$/i.test((it.dataset.name || '').trim());
                });
                (us || items[0]).click();
            }

            document.getElementById('select-country')?.querySelector('.select-list')?.addEventListener('click', function(e) {
                const item = e.target.closest('.select-item');
                if (!item || !item.dataset.countryId) return;
                if (!apiId) {
                    iziToast.error({ message: 'Please select server first', position: 'topRight' });
                    return;
                }
                countryId = item.dataset.countryId;
                setPreview('select-country', item.dataset.name || 'Country');
                loadDurations();
            });

            document.getElementById('select-duration')?.querySelector('.select-list')?.addEventListener('click', function(e) {
                const item = e.target.closest('.select-item');
                if (!item || !item.dataset.dtype) return;
                dtype = item.dataset.dtype;
                dcount = item.dataset.dcount;
                setPreview('select-duration', item.dataset.label || durationLabel(dtype, dcount));
                loadServices();
            });

            // ---------- Active rental cards (unchanged) ----------
            const defaultLogo = 'https://i.ibb.co/ySRhxqh/default.png';

            function fmtTime(sec) {
                if (sec <= 0) return '00:00:00';
                const h = Math.floor(sec / 3600);
                const m = Math.floor((sec % 3600) / 60);
                const s = sec % 60;
                return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
            }

            function remainingUntil(iso) {
                if (!iso) return 0;
                const end = new Date(iso).getTime();
                return Math.max(0, Math.floor((end - Date.now()) / 1000));
            }

            function escapeHtml(str) {
                return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            function smsText(item) {
                if (item == null) return '';
                if (typeof item === 'string') return item;
                if (typeof item === 'object') {
                    return item.text || item.message || item.sms || item.code || item.body || JSON.stringify(item);
                }
                return String(item);
            }

            function renderSmsHtml(sms) {
                const lists = [
                    ...(sms?.SmsList || []),
                    ...(sms?.OtherSms || []),
                ];
                if (!lists.length) {
                    return '<p class="activation-status-text">Waiting for SMS...</p>';
                }
                return '<ul class="rental-sms-list">' + lists.map(function(msg) {
                    return '<li>' + escapeHtml(smsText(msg)) + '</li>';
                }).join('') + '</ul>';
            }

            function addCard(order) {
                const orderId = order.order_id;
                const fullNumber = '+' + (order.number || '').replace(/^\+/, '');
                const svcName = order.service_name || 'Service';
                const provider = order.provider ? ' · ' + order.provider : '';
                const serviceAmount = fmtMoney(order.service_price || '0');
                const duration = durationLabel(order.dtype, order.dcount);
                const safeId = orderId.replace(/[^a-zA-Z0-9-_]/g, '_');
                const smsId = 'rent-sms-' + safeId;
                const timerId = 'rent-timer-' + safeId;
                const container = document.getElementById('card-container');
                let remaining = remainingUntil(order.expires_at);

                const cardHtml =
                    '<div class="activation-card card rental-card" data-order-id="' + escapeHtml(orderId) + '" data-dtype="' + escapeHtml(order.dtype) + '" data-dcount="' + escapeHtml(order.dcount) + '">' +
                    '  <div class="activation-header">' +
                    '    <div class="activation-image"><img src="' + defaultLogo + '" alt=""></div>' +
                    '    <div class="activation-info">' +
                    '      <div class="activation-number"><span>' + escapeHtml(fullNumber) + '</span>' +
                    '        <button type="button" class="copy-number" title="Copy number"><i class="ti ti-copy"></i></button></div>' +
                    '      <div class="activation-meta">' + escapeHtml(svcName + provider) + ' - ' + escapeHtml(serviceAmount) + '</div>' +
                    '      <div class="activation-meta">Duration: ' + escapeHtml(duration) + '</div>' +
                    '    </div></div>' +
                    '  <div class="activation-status" id="' + smsId + '">' + renderSmsHtml(order.sms || {}) + '</div>' +
                    '  <div class="activation-timer rental-expiry" id="' + timerId + '">' +
                    '    <i class="ti ti-clock"></i> Expires in <span class="timer-value">' + fmtTime(remaining) + '</span>' +
                    '  </div>' +
                    '  <div class="activation-actions">' +
                    '    <button type="button" class="btn btn-done done-btn"><i class="ti ti-check"></i> Done</button>' +
                    '    <button type="button" class="btn btn-copy copy-btn"><i class="ti ti-copy"></i> Copy</button>' +
                    '  </div></div>';

                container.insertAdjacentHTML('beforeend', cardHtml);
                const card = container.lastElementChild;
                const smsEl = document.getElementById(smsId);
                const timerValEl = document.getElementById(timerId)?.querySelector('.timer-value');
                let pollInterval = null;
                let timerInterval = null;

                function stopCard() {
                    if (pollInterval) clearInterval(pollInterval);
                    if (timerInterval) clearInterval(timerInterval);
                    pollInterval = timerInterval = null;
                }

                function refreshSms(sms) {
                    if (smsEl) smsEl.innerHTML = renderSmsHtml(sms);
                }

                if (remaining > 0) {
                    timerInterval = setInterval(function() {
                        remaining--;
                        if (timerValEl) timerValEl.textContent = fmtTime(remaining);
                        if (remaining <= 0) {
                            stopCard();
                            card.classList.add('rental-expired');
                            if (timerValEl) timerValEl.textContent = 'Expired';
                        }
                    }, 1000);

                    pollInterval = setInterval(function() {
                        fetch(baseUrl + '/renting/poll-sms?order_id=' + encodeURIComponent(orderId))
                            .then(function(r) { return r.json(); })
                            .then(function(res) {
                                if (res.status === '200' && res.sms) {
                                    refreshSms(res.sms);
                                } else if (res.status === '500') {
                                    stopCard();
                                    card.classList.add('rental-expired');
                                }
                            });
                    }, 5000);
                } else {
                    card.classList.add('rental-expired');
                }

                function copyNumber() {
                    navigator.clipboard.writeText(fullNumber).then(function() {
                        iziToast.success({ message: 'Number copied', position: 'topRight' });
                    });
                }

                card.querySelector('.copy-number')?.addEventListener('click', copyNumber);
                card.querySelector('.copy-btn')?.addEventListener('click', copyNumber);

                card.querySelector('.done-btn')?.addEventListener('click', function() {
                    stopCard();
                    dismissCard(orderId);
                    card.remove();
                });
            }

            fetch(baseUrl + '/renting/active-orders')
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.status === '200' && res.data?.length) {
                        const dismissed = getDismissedCards();
                        res.data
                            .filter(function(order) { return !dismissed.includes(order.order_id); })
                            .forEach(addCard);
                    }
                });

            document.getElementById('rent-number')?.addEventListener('click', function() {
                if (!apiId) {
                    iziToast.error({ message: 'Please select server', position: 'topRight' });
                    return;
                }
                if (!countryId) {
                    iziToast.error({ message: 'Please select country', position: 'topRight' });
                    return;
                }
                if (!dtype || !dcount) {
                    iziToast.error({ message: 'Please select duration', position: 'topRight' });
                    return;
                }
                if (!serviceId || !serviceOperatorId) {
                    iziToast.error({ message: 'Please select a service and operator from the list', position: 'topRight' });
                    return;
                }

                const btn = this;
                btn.disabled = true;
                fetch(baseUrl + '/renting/rent', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ service_operator_id: parseInt(serviceOperatorId, 10) })
                })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        btn.disabled = false;
                        if (res.status === '200' && res.data) {
                            addCard(res.data);
                            iziToast.success({ message: res.message, position: 'topRight' });
                        } else {
                            iziToast.error({ message: res.message || 'Rent failed', position: 'topRight' });
                        }
                    })
                    .catch(function() {
                        btn.disabled = false;
                        iziToast.error({ message: 'Request failed', position: 'topRight' });
                    });
            });

            // ---------- Kick off default selection: Server 1 -> United States -> 1 week -> full service list ----------
            const firstServerItem = document.querySelector('#select-server .select-item[data-api-id]');
            if (firstServerItem) {
                firstServerItem.click();
            }
        })();
    </script>
@endpush