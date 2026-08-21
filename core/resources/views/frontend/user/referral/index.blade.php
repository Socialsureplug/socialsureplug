@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="card mp-20 api-key-card">
        <h2 class="title">Referral</h2>
        <div class="form-group">
            <input
                type="text"
                class="form-control api-key-field"
                id="referral-url"
                value="{{ $referralUrl }}"
                readonly
                aria-label="Referral URL"
            >
        </div>
        <div class="api-key-actions">
            <button type="button" class="btn btn-copy" id="copy-referral-url">
                <i class="ti ti-copy"></i>
                <span>Copy</span>
            </button>
        </div>
        <p class="text-muted mb-25">Total referral commission: {{ number_format($totalCommission, 2) }}</p>

        <div class="table-header">
            <form method="GET" action="{{ route('user.referral.index') }}" class="table-search-form">
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search referral commissions..." value="{{ $search ?? '' }}">
                    <i class="ti ti-search"></i>
                </div>
                <button type="submit" class="btn btn-secondary search-btn"><i class="ti ti-search"></i> Search</button>
                @if(!empty($search))
                    <a href="{{ route('user.referral.index') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Referred User</th>
                        <th>Deposit</th>
                        <th>Rate</th>
                        <th>Commission</th>
                        <th>Reference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $item)
                        <tr>
                            <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ trim(($item->referred->fname ?? '').' '.($item->referred->lname ?? '')) ?: ($item->referred->email ?? '-') }}</td>
                            <td>{{ number_format($item->deposit_amount, 2) }}</td>
                            <td>{{ number_format($item->commission_percent, 2) }}%</td>
                            <td>{{ number_format($item->commission_amount, 2) }}</td>
                            <td>{{ $item->reference ?? '-' }}</td>
                            <td><span class="status {{ $item->status === 'paid' ? 'success' : 'failed' }}">{{ ucfirst($item->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No referral commissions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($commissions->hasPages())
            {{ $commissions->links('vendor.pagination.default') }}
        @endif
    </div>

    @push('script')
    <script>
        document.getElementById('copy-referral-url')?.addEventListener('click', async function() {
            var input = document.getElementById('referral-url');
            if (!input) return;

            input.select();
            try {
                await navigator.clipboard.writeText(input.value);
                if (window.iziToast) {
                    iziToast.success({ message: 'Referral URL copied', position: 'topRight' });
                }
            } catch (e) {
                document.execCommand('copy');
                if (window.iziToast) {
                    iziToast.success({ message: 'Referral URL copied', position: 'topRight' });
                }
            }
        });
    </script>
    @endpush
@endsection
