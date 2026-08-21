@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card table-card">
        <div class="table-header">
            <form method="get" action="{{ route('backend.selling.orders') }}" class="table-search-form">
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search by order ID, product, user email…" value="{{ $search ?? '' }}">
                    <i class="las la-search"></i>
                </div>
                <button type="submit" class="btn btn-secondary search-btn">Search</button>
                @if(!empty($search))
                    <a href="{{ route('backend.selling.orders') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Order</th>
                        <th>User</th>
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
                            <td>{{ $orders->firstItem() + $loop->index }}</td>
                            <td>{{ $order->paid_at?->format('Y-m-d H:i') ?? $order->created_at?->format('Y-m-d H:i') }}</td>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->user?->email ?? '—' }}</td>
                            <td>{{ $order->sellingProduct?->title ?? '—' }}</td>
                            <td>{{ $order->quantity }}</td>
                            <td>{{ $currency ?? '' }}{{ number_format((float) $order->total_amount, 2) }}</td>
                            <td>{{ $order->status ?? '—' }}</td>
                            <td>
                                <a href="{{ route('backend.selling.orders.accounts', $order->id) }}" class="btn btn-sm btn-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">No selling orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="pagination-wrapper">
                {{ $orders->links('vendor.pagination.default') }}
            </div>
        @endif
    </div>
</div>
@endsection
