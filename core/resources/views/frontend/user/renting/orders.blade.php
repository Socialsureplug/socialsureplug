@extends('frontend.layout.dash')

@section('content')
<div class="card mp-20">
    <h2 class="title">Renting Log</h2>
    <div class="table-header">
        <form method="GET" action="{{ route('user.renting.orders') }}" class="logs-search">
            <div class="logs-search-bar">
                <i class="ti ti-search logs-search-icon"></i>
                <input type="text" name="search" class="logs-search-input" placeholder="Search rentals..." value="{{ $search ?? '' }}">
                <button type="submit" class="logs-search-submit"><i class="ti ti-search"></i><span>Search</span></button>
            </div>
            @if(!empty($search))
                <a href="{{ route('user.renting.orders') }}" class="logs-search-clear"><i class="ti ti-x"></i> Clear filters</a>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Number</th>
                    <th>Operator</th>
                    <th>Duration</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>SMS</th>
                    <th>Rented</th>
                    <th>Expires</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php
                    $statusMap = [
                        'active' => 'success',
                        'completed' => 'success',
                        'expired' => 'warning',
                        'cancelled' => 'danger',
                        'failed' => 'danger',
                        'restored' => 'info',
                    ];
                    $statusType = $statusMap[$order->status] ?? 'info';
                    $smsMessages = is_array($order->sms_messages) ? $order->sms_messages : [];
                    $smsCount = count($smsMessages['SmsList'] ?? []) + count($smsMessages['OtherSms'] ?? []);
                    $canProlong = $order->isActive() && $order->provider_order_id;
                    $canRestore = !$order->isActive()
                        && $order->provider_order_id
                        && !in_array($order->status, ['cancelled', 'failed'], true);
                    $durationLabel = $order->dcount . ' ' . $order->dtype;
                @endphp
                <tr>
                    <td>{{ $order->service_name }}</td>
                    <td>{{ $order->phone ? '+' . ltrim($order->phone, '+') : '-' }}</td>
                    <td>{{ $order->provider ?: '-' }}</td>
                    <td>{{ $durationLabel }}</td>
                    <td>{{ number_format((float) $order->price, 2) }}</td>
                    <td><span class="status-badge {{ $statusType }}">{{ str_replace('_', ' ', ucfirst($order->status)) }}</span></td>
                    <td>{{ $smsCount > 0 ? $smsCount . ' msg' : '-' }}</td>
                    <td>{{ $order->rented_at?->format('Y-m-d H:i') ?? '-' }}</td>
                    <td>{{ $order->expires_at?->format('Y-m-d H:i') ?? '-' }}</td>
                    <td>
                        @if($canProlong)
                            <button type="button"
                                class="btn btn-outline btn-sm rent-prolong-btn"
                                data-order-id="{{ $order->order_id }}"
                                data-duration="{{ $durationLabel }}"
                                data-price="{{ number_format((float) ($order->prolong_price ?? $order->price), 2, '.', '') }}">
                                Prolong
                            </button>
                        @elseif($canRestore)
                            <button type="button"
                                class="btn btn-outline btn-sm rent-restore-btn"
                                data-order-id="{{ $order->order_id }}"
                                data-service="{{ $order->service_name }}">
                                Restore
                            </button>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="empty">
                            <div class="icon">
                                <i class="ti ti-inbox"></i>
                            </div>
                            <span>No rentals yet.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links('vendor.pagination.default') }}
</div>

<div class="modal" id="rent-restore-modal" role="dialog" aria-modal="true" aria-labelledby="rent-restore-modal-title">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="rent-restore-modal-title">Restore rental</h3>
            <button type="button" class="close-modal" id="rent-restore-close" aria-label="Close"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            <div id="rent-restore-step-warning">
                <p class="modal-lead"><strong>Important</strong></p>
                <p>Restore does not guarantee the same phone number.</p>
                <p>Your rental may be reactivated with a new number for the same service.</p>
                <p>A fee will be charged from your wallet if you continue.</p>
            </div>
            <div id="rent-restore-step-confirm" class="hidden">
                <p>Restore <strong id="rent-restore-service-name"></strong>?</p>
                <p>Fee: <strong id="rent-restore-price"></strong></p>
                <p class="modal-note" id="rent-restore-duration"></p>
            </div>
            <p id="rent-restore-error" class="modal-error hidden"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline btn-sm" id="rent-restore-cancel">Cancel</button>
            <button type="button" class="btn btn-primary btn-sm" id="rent-restore-continue">Continue</button>
            <button type="button" class="btn btn-primary btn-sm hidden" id="rent-restore-confirm">Confirm restore</button>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
(function() {
    const baseUrl = '{{ url('/') }}';
    const currency = '{{ optional($settings)->website_currency ?? 'NGN' }}';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const fmtMoney = (amount) => currency + String(amount ?? '0');

    const modal = document.getElementById('rent-restore-modal');
    const stepWarning = document.getElementById('rent-restore-step-warning');
    const stepConfirm = document.getElementById('rent-restore-step-confirm');
    const errorEl = document.getElementById('rent-restore-error');
    const serviceNameEl = document.getElementById('rent-restore-service-name');
    const priceEl = document.getElementById('rent-restore-price');
    const durationEl = document.getElementById('rent-restore-duration');
    const btnCancel = document.getElementById('rent-restore-cancel');
    const btnClose = document.getElementById('rent-restore-close');
    const btnContinue = document.getElementById('rent-restore-continue');
    const btnConfirm = document.getElementById('rent-restore-confirm');

    let restoreOrderId = '';
    let restoreReady = false;

    function openModal() {
        modal?.classList.add('active');
    }

    function closeModal() {
        modal?.classList.remove('active');
        restoreOrderId = '';
        restoreReady = false;
        stepWarning?.classList.remove('hidden');
        stepConfirm?.classList.add('hidden');
        btnContinue?.classList.remove('hidden');
        btnConfirm?.classList.add('hidden');
        errorEl?.classList.add('hidden');
        if (errorEl) errorEl.textContent = '';
    }

    function showError(message) {
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }
    }

    document.querySelectorAll('.rent-prolong-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            const duration = this.dataset.duration || 'same period';
            const price = this.dataset.price || '0';
            const message = 'Extend this rental by ' + duration + ' for ' + fmtMoney(price) + '?';

            if (!confirm(message)) {
                return;
            }

            btn.disabled = true;
            fetch(baseUrl + '/renting/prolong', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ order_id: orderId })
            })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    btn.disabled = false;
                    if (res.status === '200') {
                        iziToast.success({ message: res.message || 'Rental prolonged', position: 'topRight' });
                        setTimeout(function() { window.location.reload(); }, 800);
                    } else {
                        iziToast.error({ message: res.message || 'Prolong failed', position: 'topRight' });
                    }
                })
                .catch(function() {
                    btn.disabled = false;
                    iziToast.error({ message: 'Request failed', position: 'topRight' });
                });
        });
    });

    document.querySelectorAll('.rent-restore-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            restoreOrderId = this.dataset.orderId || '';
            restoreReady = false;
            if (serviceNameEl) serviceNameEl.textContent = this.dataset.service || 'this service';
            stepWarning?.classList.remove('hidden');
            stepConfirm?.classList.add('hidden');
            btnContinue?.classList.remove('hidden');
            btnConfirm?.classList.add('hidden');
            errorEl?.classList.add('hidden');
            if (errorEl) errorEl.textContent = '';
            openModal();
        });
    });

    btnCancel?.addEventListener('click', closeModal);
    btnClose?.addEventListener('click', closeModal);

    modal?.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    btnContinue?.addEventListener('click', function() {
        if (!restoreOrderId) return;

        btnContinue.disabled = true;
        errorEl?.classList.add('hidden');

        fetch(baseUrl + '/renting/restore-precalc', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order_id: restoreOrderId })
        })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btnContinue.disabled = false;
                if (res.status === '200' && res.data) {
                    restoreReady = true;
                    if (priceEl) priceEl.textContent = fmtMoney(res.data.price);
                    if (durationEl) {
                        durationEl.textContent = res.data.duration_days
                            ? 'Rental period: ' + res.data.duration_days + ' days'
                            : '';
                    }
                    stepWarning?.classList.add('hidden');
                    stepConfirm?.classList.remove('hidden');
                    btnContinue?.classList.add('hidden');
                    btnConfirm?.classList.remove('hidden');
                } else {
                    showError(res.message || 'Restore is not available');
                }
            })
            .catch(function() {
                btnContinue.disabled = false;
                showError('Request failed');
            });
    });

    btnConfirm?.addEventListener('click', function() {
        if (!restoreOrderId || !restoreReady) return;

        btnConfirm.disabled = true;
        fetch(baseUrl + '/renting/restore', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order_id: restoreOrderId })
        })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btnConfirm.disabled = false;
                if (res.status === '200') {
                    closeModal();
                    iziToast.success({ message: res.message || 'Rental restored', position: 'topRight' });
                    setTimeout(function() { window.location.reload(); }, 800);
                } else {
                    showError(res.message || 'Restore failed');
                }
            })
            .catch(function() {
                btnConfirm.disabled = false;
                showError('Request failed');
            });
    });
})();
</script>
@endpush
