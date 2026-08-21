@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card table-card">
        <div class="table-header selling-order-accounts-toolbar">
            <a href="{{ route('backend.selling.orders') }}" class="btn btn-outline">&larr; Back to selling orders</a>
        </div>
        <p class="order-accounts-meta">
            <span><strong>User:</strong> {{ $order->user?->email ?? '—' }}</span>
            <span class="order-accounts-meta-sep">&middot;</span>
            <span><strong>Product:</strong> {{ $order->sellingProduct?->title ?? '—' }}</span>
            <span class="order-accounts-meta-sep">&middot;</span>
            <span><strong>Qty:</strong> {{ $order->quantity }}</span>
            <span class="order-accounts-meta-sep">&middot;</span>
            <span><strong>Total:</strong> {{ $currency ?? '' }}{{ number_format((float) $order->total_amount, 2) }}</span>
        </p>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->sellingAccounts as $acc)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $acc->name ?: '—' }}</td>
                            <td>{{ $acc->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary js-selling-cred-open" data-target="selling-cred-{{ $acc->id }}">Credential</button>
                            </td>
                        </tr>
                        <textarea id="selling-cred-{{ $acc->id }}" hidden readonly>{{ $acc->credentials }}</textarea>
                    @empty
                        <tr><td colspan="4" class="text-center">No accounts linked to this order yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="selling-credential-modal" class="selling-cred-overlay" aria-hidden="true">
    <div class="selling-cred-dialog card">
        <div class="card-body">
            <div class="selling-cred-dialog-header">
                <h3 class="selling-cred-dialog-title">Credential</h3>
                <button type="button" class="btn btn-sm btn-outline selling-cred-close" aria-label="Close">&times;</button>
            </div>
            <pre id="selling-credential-modal-body" class="selling-cred-pre"></pre>
            <button type="button" class="btn btn-primary selling-cred-copy-btn" id="selling-credential-copy-btn">Copy</button>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
(() => {
    const overlay = document.getElementById('selling-credential-modal');
    const bodyEl = document.getElementById('selling-credential-modal-body');
    const copyBtn = document.getElementById('selling-credential-copy-btn');
    if (!overlay || !bodyEl || !copyBtn) return;

    const close = () => {
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
    };

    document.querySelectorAll('.js-selling-cred-open').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-target');
            const ta = id ? document.getElementById(id) : null;
            bodyEl.textContent = ta ? ta.value : '';
            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
        });
    });

    overlay.querySelectorAll('.selling-cred-close').forEach((b) => b.addEventListener('click', close));
    overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });

    copyBtn.addEventListener('click', async () => {
        const text = bodyEl.textContent ?? '';
        try {
            await navigator.clipboard.writeText(text);
            window.iziToast?.success?.({ message: 'Copied', position: 'topRight' });
        } catch {
            window.iziToast?.error?.({ message: 'Copy failed', position: 'topRight' });
        }
    });
})();
</script>
@endpush
