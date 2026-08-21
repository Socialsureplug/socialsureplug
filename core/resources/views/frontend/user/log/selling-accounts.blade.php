@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="card mp-20">
        <div class="table-header log-back-row">
            <a href="{{ route('user.log.selling-orders') }}" class="btn btn-outline">&larr; Back to orders</a>
        </div>
        <h2 class="title">Order #{{ $order->id }} — Accounts</h2>
        <p class="log-subtitle">
            {{ $order->sellingProduct?->title ?? 'Product' }}
            · {{ $currency ?? 'NGN' }} {{ number_format((float) $order->total_amount, 2) }}
        </p>

        <div class="table-responsive log-table-block">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Account</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->sellingAccounts as $account)
                        @php
                            $raw = $account->credentials;
                            if (is_string($raw)) {
                                $decoded = json_decode($raw, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $credentialPlain = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                } else {
                                    $credentialPlain = $raw;
                                }
                            } else {
                                $credentialPlain = $raw !== null ? (string) $raw : '';
                            }
                        @endphp
                        <tr>
                            <td>{{ $account->id }}</td>
                            <td>{{ $account->name ?: '—' }}</td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-secondary btn-sm js-open-credentials"
                                    aria-haspopup="dialog"
                                    data-account-name="{{ e($account->name) }}"
                                    data-account-id="{{ $account->id }}"
                                    data-b64="{{ base64_encode($credentialPlain) }}"
                                >
                                    <i class="ti ti-key" aria-hidden="true"></i>
                                    View credentials
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No accounts for this order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div
        id="cred-modal-overlay"
        class="cred-modal-overlay"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cred-modal-title"
    >
        <div class="cred-modal-panel">
            <div class="cred-modal-header" id="cred-modal-title">Credentials</div>
            <div class="cred-modal-body">
                <pre id="cred-modal-pre"></pre>
            </div>
            <div class="cred-modal-footer">
                <button type="button" class="btn btn-outline btn-sm" id="cred-modal-close">Close</button>
                <button type="button" class="btn btn-secondary btn-sm" id="cred-modal-download">Download</button>
                <button type="button" class="btn btn-primary btn-sm" id="cred-modal-copy">Copy</button>
            </div>
        </div>
    </div>

    <script>
        {
            const decodeBase64Utf8 = (b64) => {
                try {
                    const bin = atob(b64);
                    const bytes = new Uint8Array(bin.length);
                    for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
                    return new TextDecoder('utf-8').decode(bytes);
                } catch {
                    return '';
                }
            };

            const notify = {
                success(message) {
                    if (typeof globalThis.iziToast !== 'undefined') {
                        globalThis.iziToast.success({ message, position: 'topRight' });
                    } else {
                        globalThis.alert(message);
                    }
                },
                error(message) {
                    if (typeof globalThis.iziToast !== 'undefined') {
                        globalThis.iziToast.error({ message, position: 'topRight' });
                    } else {
                        globalThis.alert(message);
                    }
                },
            };

            const overlay = document.getElementById('cred-modal-overlay');
            const pre = document.getElementById('cred-modal-pre');
            const titleEl = document.getElementById('cred-modal-title');
            const closeBtn = document.getElementById('cred-modal-close');
            const downloadBtn = document.getElementById('cred-modal-download');
            const copyBtn = document.getElementById('cred-modal-copy');

            const state = { text: '', fileName: 'credentials.txt' };

            const openModal = (text, accountName, accountId) => {
                state.text = text ?? '';
                state.fileName = `account-${accountId ?? 'credentials'}-credentials.txt`;
                pre.textContent = state.text;
                titleEl.textContent = accountName ? `Credentials — ${accountName}` : 'Credentials';
                overlay.classList.add('is-open');
            };

            const closeModal = () => overlay.classList.remove('is-open');

            for (const btn of document.querySelectorAll('.js-open-credentials')) {
                btn.addEventListener('click', () => {
                    const b64 = btn.dataset.b64 ?? '';
                    const name = btn.dataset.accountName ?? '';
                    const id = btn.dataset.accountId ?? '';
                    openModal(decodeBase64Utf8(b64), name, id);
                });
            }

            closeBtn?.addEventListener('click', closeModal);
            overlay?.addEventListener('click', (event) => {
                if (event.target === overlay) closeModal();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && overlay?.classList.contains('is-open')) closeModal();
            });

            copyBtn?.addEventListener('click', async () => {
                if (!state.text) return;
                try {
                    await navigator.clipboard.writeText(state.text);
                    notify.success('Copied to clipboard');
                } catch {
                    notify.error('Could not copy');
                }
            });

            downloadBtn?.addEventListener('click', () => {
                if (!state.text) return;
                const blob = new Blob([state.text], { type: 'text/plain;charset=utf-8' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = state.fileName;
                a.rel = 'noopener';
                document.body.append(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
                notify.success('Download started');
            });
        }
    </script>
@endsection
