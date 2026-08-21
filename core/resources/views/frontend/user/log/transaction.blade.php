@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="card mp-20">
        <h2 class="title">Transaction Log</h2>
        <div class="table-header">
            <form method="GET" action="{{ route('user.log.transaction') }}" class="table-search-form">
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search transactions..." value="{{ $search ?? '' }}">
                    <i class="ti ti-search"></i>
                </div>
                <button type="submit" class="btn btn-secondary search-btn"><i class="ti ti-search"></i> Search</button>
                @if(!empty($search))
                    <a href="{{ route('user.log.transaction') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance Before</th>
                        <th>Balance After</th>
                        <th>Source</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td>{{ $tx->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ ucfirst($tx->type) }}</td>
                            <td>{{ number_format($tx->amount, 2) }}</td>
                            <td>{{ number_format($tx->balance_before, 2) }}</td>
                            <td>{{ number_format($tx->balance_after, 2) }}</td>
                            <td>{{ $tx->source ?? '—' }}</td>
                            <td>{{ $tx->reference ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No transactions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            {{ $transactions->links('vendor.pagination.default') }}
        @endif
    </div>
@endsection