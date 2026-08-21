@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card table-card">
        <div class="table-header">
            <form method="get" action="{{ route('backend.payments.index') }}" class="table-search-form">
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search by reference, amount, user..." value="{{ $search ?? '' }}">
                    <i class="las la-search"></i>
                </div>
                <button type="submit" class="btn btn-secondary search-btn">Search</button>
                @if(!empty($search))
                    <a href="{{ route('backend.payments.index') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payments->firstItem() + $loop->index }}</td>
                            <td>{{ $payment->user?->email ?? 'N/A' }}</td>
                            <td>{{ ucfirst($payment->method) }}</td>
                            <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->reference }}</td>
                            <td>
                                @php
                                    $class = match ($payment->status) {
                                        'success'  => 'status success',
                                        'pending'  => 'status pending',
                                        'failed'   => 'status failed',
                                        'cancelled'=> 'status failed',
                                        default    => 'status',
                                    };
                                @endphp
                                <span class="{{ $class }}">{{ ucfirst($payment->status) }}</span>
                            </td>
                            <td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                    
                            <td>
                                
                            
                                <div class="table-actions">
                                    <a href="{{ route('backend.payments.show', $payment) }}" class="table-btn">
                                        <i class="las la-eye"></i>
                                    </a>
                                    @if($payment->method === 'bank' && $payment->status === 'pending')
                                        <form action="{{ route('backend.payments.approve-bank', $payment) }}" method="post" class="d-inline" onsubmit="return confirm('Approve this bank payment?');">
                                            @csrf
                                            <button type="submit" class="table-btn"><i class="las la-check"></i></button>
                                        </form>
                                        <form action="{{ route('backend.payments.reject-bank', $payment) }}" method="post" class="d-inline" onsubmit="return confirm('Reject this bank payment?');">
                                            @csrf
                                            <button type="submit" class="table-btn"><i class="las la-times"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">No payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="pagination-wrapper">
                {{ $payments->links('vendor.pagination.default') }}
            </div>
        @endif
    </div>
</div>
@endsection