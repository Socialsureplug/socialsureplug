@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="smm-page">
        <div class="server-card card mp-20 smm-card">
            <div class="smm-hero">
                <div class="smm-hero-icon">
                    <i class="ti ti-brand-instagram"></i>
                </div>
                <div class="smm-hero-text">
                    <h2 class="title">Boost Socials</h2>
                    <p class="desc">Order followers, likes, views and more. Choose a category, then a subcategory, then a service—then enter your link and quantity.</p>
                </div>
            </div>

            <div class="smm-tabs">
                <button type="button" class="smm-tab active" data-panel="smm-single"><i class="ti ti-shopping-cart"></i> Single order</button>
                <button type="button" class="smm-tab" data-panel="smm-mass"><i class="ti ti-list"></i> Mass order</button>
            </div>

            {{-- Single order panel --}}
            <div id="smm-single" class="smm-panel active">
                <form id="smm-order-form" class="smm-order-form">
                    @csrf
                    <input type="hidden" name="service_id" id="smm-service-id" value="">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                Category <span class="required">*</span>
                                <div class="tooltip-wrapper">
                                    <i class="ti ti-info-circle"></i>
                                    <span class="tooltip">Select the social media category (e.g. Instagram, TikTok).</span>
                                </div>
                            </label>
                            <div class="select" id="smm-select-category">
                                <button type="button" class="select-preview">
                                    <span>Choose category</span>
                                    <i class="ti ti-chevron-down select-icon"></i>
                                </button>
                                <div class="select-dropdown">
                                    <div class="select-search">
                                        <input type="search" placeholder="Search..." class="form-control" id="smm-category-search">
                                    </div>
                                    <div class="select-list" id="smm-category-list">
                                        @foreach($categories as $c)
                                        <div class="select-item" data-category-id="{{ $c->id }}" data-name="{{ $c->name }}">{{ $c->name }}</div>
                                        @endforeach
                                        @if($categories->isEmpty())
                                        <div class="select-item">No categories</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Subcategory <span class="required">*</span>
                                <div class="tooltip-wrapper">
                                    <i class="ti ti-info-circle"></i>
                                    <span class="tooltip">Pick a subcategory under the selected category (e.g. Followers, Likes).</span>
                                </div>
                            </label>
                            <div class="select" id="smm-select-subcategory">
                                <button type="button" class="select-preview">
                                    <span>Choose category</span>
                                    <i class="ti ti-chevron-down select-icon"></i>
                                </button>
                                <div class="select-dropdown">
                                    <div class="select-search">
                                        <input type="search" placeholder="Search..." class="form-control" id="smm-subcategory-search">
                                    </div>
                                    <div class="select-list" id="smm-subcategory-list">
                                        <div class="select-item">Choose category first</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Service <span class="required">*</span>
                                <div class="tooltip-wrapper">
                                    <i class="ti ti-info-circle"></i>
                                    <span class="tooltip">Select a service. The number before the name is the service ID (use it in Mass order). Price is per 1000.</span>
                                </div>
                            </label>
                            <div class="select" id="smm-select-service">
                                <button type="button" class="select-preview">
                                    <span>Choose subcategory first</span>
                                    <i class="ti ti-chevron-down select-icon"></i>
                                </button>
                                <div class="select-dropdown">
                                    <div class="select-search">
                                        <input type="search" placeholder="Search..." class="form-control" id="smm-service-search">
                                    </div>
                                    <div class="select-list select-price" id="smm-service-list">
                                        <div class="select-item">Choose subcategory first</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Link <span class="required">*</span></label>
                            <input type="text" class="form-control" name="link" id="smm-link" placeholder="https://instagram.com/username" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quantity <span class="required">*</span></label>
                            <input type="number" class="form-control" name="quantity" id="smm-quantity" min="1" placeholder="e.g. 1000" required>
                            <span class="hint" id="smm-min-max-hint">Min/max will show after selecting a service.</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Average time
                                <div class="tooltip-wrapper">
                                    <i class="ti ti-info-circle"></i>
                                    <span class="tooltip">Estimated completion time set by admin for this service.</span>
                                </div>
                            </label>
                            <input type="text" class="form-control" id="smm-average-time" value="—" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="smm-checkbox-wrap">
                            <input type="checkbox" name="agree" id="smm-agree" required>
                            I confirm the order and accept the terms.
                        </label>
                    </div>
                    <div class="smm-form-footer">
                        <div class="smm-summary-box">
                            <div class="smm-summary-label">Total charge</div>
                            <div class="smm-summary-amount" id="smm-summary-amount">{{ $currency }}0</div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="smm-place-order-btn">
                            <i class="ti ti-shopping-cart"></i> Place order
                        </button>
                    </div>
                </form>
            </div>

            {{-- Mass order panel --}}
            <div id="smm-mass" class="smm-panel">
                <div class="server-card card smm-mass-card">
                    <p class="smm-mass-desc">One line per order: <strong>service_id|quantity|link</strong>. The <strong>service_id</strong> is the number shown before each service name on the Single order tab after you choose category → subcategory → service (e.g. <code>3 - Instagram Followers</code> → use <code>3</code>).</p>
                    <p class="smm-mass-desc smm-mass-example">Example: <code>1|1000|https://instagram.com/username</code></p>
                    <form id="smm-mass-order-form">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Orders <span class="required">*</span></label>
                            <textarea class="form-control textarea" name="mass_order" id="smm-mass-order-text" rows="6" placeholder="1|1000|https://instagram.com/username&#10;2|500|https://..."></textarea>
                        </div>
                        <div class="form-group">
                            <label class="smm-checkbox-wrap">
                                <input type="checkbox" name="agree" id="smm-mass-agree" required>
                                I confirm the orders and accept the terms.
                            </label>
                        </div>
                        <div class="smm-form-footer">
                            <button type="submit" class="btn btn-primary" id="smm-mass-order-btn">
                                <i class="ti ti-list"></i> Place mass order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
<link rel="stylesheet" href="{{ asset('assets/frontend/css/smm-dash.css') }}">
@endpush

@push('script')
<script>
(function() {
    var currency = @json($currency ?? 'NGN');
    var servicesData = @json($servicesData);
    var subcategoriesData = @json($subcategoriesData ?? []);

    var subcategoriesByCategory = {};
    subcategoriesData.forEach(function(sub) {
        var cid = sub.smm_category_id;
        if (!subcategoriesByCategory[cid]) subcategoriesByCategory[cid] = [];
        subcategoriesByCategory[cid].push(sub);
    });

    var servicesBySubcategory = {};
    servicesData.forEach(function(s) {
        var sid = s.smm_subcategory_id;
        if (sid == null) return;
        if (!servicesBySubcategory[sid]) servicesBySubcategory[sid] = [];
        servicesBySubcategory[sid].push(s);
    });

    var serviceIdInput = document.getElementById('smm-service-id');
    var quantityInput = document.getElementById('smm-quantity');
    var averageTimeInput = document.getElementById('smm-average-time');
    var summaryAmount = document.getElementById('smm-summary-amount');
    var minMaxHint = document.getElementById('smm-min-max-hint');
    var categoryList = document.getElementById('smm-category-list');
    var subcategoryList = document.getElementById('smm-subcategory-list');
    var serviceList = document.getElementById('smm-service-list');
    var categoryPreview = document.querySelector('#smm-select-category .select-preview span');
    var subcategoryPreview = document.querySelector('#smm-select-subcategory .select-preview span');
    var servicePreview = document.querySelector('#smm-select-service .select-preview span');

    function getSelectedCategoryId() {
        var item = categoryList && categoryList.querySelector('.select-item.selected');
        return item && item.getAttribute('data-category-id');
    }

    function getSelectedSubcategoryId() {
        var item = subcategoryList && subcategoryList.querySelector('.select-item.selected');
        return item && item.getAttribute('data-subcategory-id');
    }

    function formatAverageTime(minutes) {
        var total = parseInt(minutes, 10);
        if (!total || total <= 0) return '—';
        var days = Math.floor(total / 1440);
        var remAfterDays = total % 1440;
        var hours = Math.floor(remAfterDays / 60);
        var mins = remAfterDays % 60;
        var parts = [];
        if (days > 0) parts.push(days + ' day' + (days === 1 ? '' : 's'));
        if (hours > 0) parts.push(hours + ' hour' + (hours === 1 ? '' : 's'));
        if (mins > 0 || parts.length === 0) parts.push(mins + ' minute' + (mins === 1 ? '' : 's'));
        return parts.join(' ');
    }

    function resetServiceListPlaceholder(text, previewText) {
        serviceList.innerHTML = '<div class="select-item">' + text + '</div>';
        if (servicePreview) servicePreview.textContent = previewText;
        serviceIdInput.value = '';
        serviceList.querySelectorAll('.select-item').forEach(function(i) { i.classList.remove('selected'); });
        updateSummary();
    }

    function populateSubcategoryList() {
        var cid = getSelectedCategoryId();
        subcategoryList.innerHTML = '';
        if (!cid) {
            subcategoryList.innerHTML = '<div class="select-item">Choose category first</div>';
            if (subcategoryPreview) subcategoryPreview.textContent = 'Choose category';
            resetServiceListPlaceholder('Choose category first', 'Choose category first');
            return;
        }
        var list = subcategoriesByCategory[cid] || [];
        list.forEach(function(sub) {
            var div = document.createElement('div');
            div.className = 'select-item';
            div.setAttribute('data-subcategory-id', sub.id);
            div.setAttribute('data-name', sub.name);
            div.innerHTML = '<span class="select-item-name">' + sub.name + '</span>';
            subcategoryList.appendChild(div);
        });
        if (list.length === 0) {
            subcategoryList.innerHTML = '<div class="select-item">No subcategories in this category</div>';
        }
        if (subcategoryPreview) subcategoryPreview.textContent = 'Choose subcategory';
        subcategoryList.querySelectorAll('.select-item').forEach(function(i) { i.classList.remove('selected'); });
        resetServiceListPlaceholder('Choose subcategory first', 'Choose subcategory first');
    }

    function populateServiceList() {
        var cid = getSelectedCategoryId();
        var sid = getSelectedSubcategoryId();
        serviceList.innerHTML = '';
        if (!cid) {
            resetServiceListPlaceholder('Choose category first', 'Choose category first');
            return;
        }
        if (!sid) {
            resetServiceListPlaceholder('Choose subcategory first', 'Choose subcategory first');
            return;
        }
        var list = servicesBySubcategory[sid] || [];
        list.forEach(function(s) {
            var div = document.createElement('div');
            div.className = 'select-item';
            div.setAttribute('data-service-id', s.id);
            div.setAttribute('data-name', s.id + ' ' + s.name);
            div.setAttribute('data-min', s.min);
            div.setAttribute('data-max', s.max);
            div.setAttribute('data-average-time-minutes', s.average_time_minutes || '');
            div.setAttribute('data-price', s.price);
            div.innerHTML = '<span class="select-item-name">' + s.id + ' - ' + s.name + '</span> <span class="price">' + currency + ' ' + Number(s.price).toFixed(2) + '/1k</span>';
            serviceList.appendChild(div);
        });
        if (list.length === 0) {
            serviceList.innerHTML = '<div class="select-item">No services in this subcategory</div>';
        }
        if (servicePreview) servicePreview.textContent = 'Choose service';
        serviceIdInput.value = '';
        updateSummary();
    }

    function updateSummary() {
        var item = serviceList && serviceList.querySelector('.select-item.selected[data-service-id]');
        if (!item || !item.getAttribute('data-service-id')) {
            summaryAmount.textContent = currency + '0';
            if (minMaxHint) minMaxHint.textContent = 'Select a service.';
            if (averageTimeInput) averageTimeInput.value = '—';
            return;
        }
        serviceIdInput.value = item.getAttribute('data-service-id');
        var min = parseInt(item.getAttribute('data-min'), 10) || 0;
        var max = parseInt(item.getAttribute('data-max'), 10) || 0;
        var avg = item.getAttribute('data-average-time-minutes');
        var price = parseFloat(item.getAttribute('data-price'), 10) || 0;
        if (minMaxHint) minMaxHint.textContent = 'Min: ' + min + ', Max: ' + max;
        if (averageTimeInput) averageTimeInput.value = formatAverageTime(avg);
        var qty = parseInt(quantityInput.value, 10) || 0;
        if (qty >= min && qty <= max) {
            var charge = (price * qty) / 1000;
            summaryAmount.textContent = currency + charge.toFixed(2);
        } else {
            summaryAmount.textContent = currency + '0';
        }
    }

    categoryList && categoryList.addEventListener('click', function(e) {
        var item = e.target.closest('.select-item');
        if (!item || !item.getAttribute('data-category-id')) return;
        setTimeout(populateSubcategoryList, 0);
    });

    subcategoryList && subcategoryList.addEventListener('click', function(e) {
        var item = e.target.closest('.select-item');
        if (!item || !item.getAttribute('data-subcategory-id')) return;
        setTimeout(populateServiceList, 0);
    });

    serviceList && serviceList.addEventListener('click', function(e) {
        var item = e.target.closest('.select-item');
        if (!item || !item.getAttribute('data-service-id')) return;
        setTimeout(updateSummary, 0);
    });

    quantityInput && quantityInput.addEventListener('input', updateSummary);

    document.querySelectorAll('.smm-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            var panelId = this.getAttribute('data-panel');
            document.querySelectorAll('.smm-tab').forEach(function(t) { t.classList.remove('active'); });
            document.querySelectorAll('.smm-panel').forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');
            var panel = document.getElementById(panelId);
            if (panel) panel.classList.add('active');
        });
    });

    document.getElementById('smm-order-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('smm-place-order-btn');
        if (!serviceIdInput.value) {
            window.iziToast && iziToast.error({ message: 'Please select a service', position: 'topRight' });
            return;
        }
        btn.disabled = true;
        var formData = new FormData(this);
        formData.set('service_id', serviceIdInput.value);
        fetch('{{ route("user.smm-order.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            if (data.status === 'success') {
                window.iziToast && iziToast.success({ message: data.message || 'Order placed.', position: 'topRight' });
                document.getElementById('smm-order-form').reset();
                serviceIdInput.value = '';
                categoryList.querySelectorAll('.select-item').forEach(function(i) { i.classList.remove('selected'); });
                if (categoryPreview) categoryPreview.textContent = 'Choose category';
                subcategoryList.innerHTML = '<div class="select-item">Choose category first</div>';
                if (subcategoryPreview) subcategoryPreview.textContent = 'Choose category';
                subcategoryList.querySelectorAll('.select-item').forEach(function(i) { i.classList.remove('selected'); });
                serviceList.querySelectorAll('.select-item').forEach(function(i) { i.classList.remove('selected'); });
                if (servicePreview) servicePreview.textContent = 'Choose subcategory first';
                serviceList.innerHTML = '<div class="select-item">Choose subcategory first</div>';
                summaryAmount.textContent = currency + '0';
                if (minMaxHint) minMaxHint.textContent = 'Select a service.';
                if (averageTimeInput) averageTimeInput.value = '—';
            } else {
                window.iziToast && iziToast.error({ message: data.message || 'Error', position: 'topRight' });
            }
        })
        .catch(function() {
            btn.disabled = false;
            window.iziToast && iziToast.error({ message: 'Request failed', position: 'topRight' });
        });
    });

    document.getElementById('smm-mass-order-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('smm-mass-order-btn');
        var formData = new FormData(this);
        formData.set('mass_order', document.getElementById('smm-mass-order-text').value);
        formData.set('agree', document.getElementById('smm-mass-agree').checked ? 'on' : '');
        btn.disabled = true;
        fetch('{{ route("user.smm-order.mass-order") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            if (data.status === 'success') {
                window.iziToast && iziToast.success({ message: data.message || 'Orders placed.', position: 'topRight' });
                document.getElementById('smm-mass-order-form').reset();
            } else {
                window.iziToast && iziToast.error({ message: data.message || 'Error', position: 'topRight' });
            }
        })
        .catch(function() {
            btn.disabled = false;
            window.iziToast && iziToast.error({ message: 'Request failed', position: 'topRight' });
        });
    });

    document.getElementById('smm-category-search') && document.getElementById('smm-category-search').addEventListener('input', function() {
        var q = (this.value || '').trim().toLowerCase();
        categoryList.querySelectorAll('.select-item').forEach(function(item) {
            var name = (item.getAttribute('data-name') || item.textContent || '').trim().toLowerCase();
            var isPlaceholder = !item.getAttribute('data-category-id');
            item.classList.toggle('hidden', isPlaceholder ? q !== '' : name.indexOf(q) === -1);
        });
    });

    document.getElementById('smm-subcategory-search') && document.getElementById('smm-subcategory-search').addEventListener('input', function() {
        var q = (this.value || '').trim().toLowerCase();
        subcategoryList.querySelectorAll('.select-item').forEach(function(item) {
            var name = (item.getAttribute('data-name') || item.textContent || '').trim().toLowerCase();
            var isPlaceholder = !item.getAttribute('data-subcategory-id');
            item.classList.toggle('hidden', isPlaceholder ? q !== '' : name.indexOf(q) === -1);
        });
    });

    document.getElementById('smm-service-search') && document.getElementById('smm-service-search').addEventListener('input', function() {
        var q = (this.value || '').trim().toLowerCase();
        serviceList.querySelectorAll('.select-item').forEach(function(item) {
            var name = (item.getAttribute('data-name') || item.textContent || '').trim().toLowerCase();
            var isPlaceholder = !item.getAttribute('data-service-id');
            item.classList.toggle('hidden', isPlaceholder ? q !== '' : name.indexOf(q) === -1);
        });
    });
})();
</script>
@endpush
