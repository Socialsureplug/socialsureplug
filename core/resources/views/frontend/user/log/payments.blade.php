@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="card mp-20">
        <h2 class="title">Payment Log</h2>
        <div class="table-header">
            <form method="GET" action="{{ route('user.log.payments') }}" class="table-search-form">
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search payments..." value="{{ $search ?? '' }}">
                    <i class="ti ti-search"></i>
                </div>
                <button type="submit" class="btn btn-secondary search-btn"><i class="ti ti-search"></i> Search</button>
                @if(!empty($search))
                    <a href="{{ route('user.log.payments') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No payments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            {{ $payments->links('vendor.pagination.default') }}
        @endif
    </div>
@endsection