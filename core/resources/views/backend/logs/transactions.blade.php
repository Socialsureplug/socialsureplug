@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card table-card">
        <div class="table-header">
            <form method="get" action="{{ route('backend.logs.transactions') }}" class="table-search-form">
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search by reference, amount, user..." value="{{ $search ?? '' }}">
                    <i class="las la-search"></i>
                </div>
                <button type="submit" class="btn btn-secondary search-btn">Search</button>
                @if(!empty($search))
                    <a href="{{ route('backend.logs.transactions') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance Before</th>
                        <th>Balance After</th>
                        <th>Source</th>
                        <th>Reference</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td>{{ $transactions->firstItem() + $loop->index }}</td>
                            <td>{{ $tx->user?->email ?? 'N/A' }}</td>
                            <td>{{ ucfirst($tx->type) }}</td>
                            <td>{{ number_format($tx->amount, 2) }}</td>
                            <td>{{ number_format($tx->balance_before, 2) }}</td>
                            <td>{{ number_format($tx->balance_after, 2) }}</td>
                            <td>{{ $tx->source ?? '-' }}</td>
                            <td>{{ $tx->reference ?? '-' }}</td>
                            <td>{{ $tx->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="pagination-wrapper">
                {{ $transactions->links('vendor.pagination.default') }}
            </div>
        @endif
    </div>
</div>
@endsection

