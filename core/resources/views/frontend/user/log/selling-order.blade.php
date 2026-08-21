@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="card mp-20">
        <h2 class="title">Selling orders</h2>
        <div class="table-header">
            <form method="GET" action="{{ route('user.log.selling-orders') }}" class="table-search-form">
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search by order ID or product..." value="{{ $search ?? '' }}">
                    <i class="ti ti-search"></i>
                </div>
                <button type="submit" class="btn btn-secondary search-btn"><i class="ti ti-search"></i> Search</button>
                @if(!empty($search))
                    <a href="{{ route('user.log.selling-orders') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order ID</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->sellingProduct?->title ?? '—' }}</td>
                            <td>{{ number_format((int) $order->quantity) }}</td>
                            <td>{{ $currency ?? 'NGN' }} {{ number_format((float) $order->total_amount, 2) }}</td>
                            <td><span class="badge badge-{{ $order->status }}">{{ ucfirst((string) $order->status) }}</span></td>
                            <td>
                                <a href="{{ route('user.log.selling-orders.accounts', $order) }}" class="btn btn-secondary btn-sm">
                                    <i class="ti ti-users" aria-hidden="true"></i>
                                    View accounts
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No selling orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            {{ $orders->links('vendor.pagination.default') }}
        @endif
    </div>
@endsection
