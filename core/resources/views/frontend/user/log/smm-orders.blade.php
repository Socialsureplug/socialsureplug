@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="card mp-20">
        <h2 class="title">Order Log</h2>
        <div class="table-header">
            <form method="GET" action="{{ route('user.log.smm-orders') }}" class="table-search-form">
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search by link, service, status..." value="{{ $search ?? '' }}">
                    <i class="ti ti-search"></i>
                </div>
                <button type="submit" class="btn btn-secondary search-btn"><i class="ti ti-search"></i> Search</button>
                @if(!empty($search))
                    <a href="{{ route('user.log.smm-orders') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>ID</th>
                        <th>Service</th>
                        <th>Link</th>
                        <th>Quantity</th>
                        <th>Start count</th>
                        <th>Remains</th>
                        <th>Charge</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->smmService?->name ?? '—' }}</td>
                            <td title="{{ $order->link }}">
                                <a href="{{ $order->link }}" target="_blank" rel="noopener noreferrer" style="color: var(--primary-color);">
                                    {{ Str::limit($order->link, 35) }}
                                </a>
                            </td>
                            <td>{{ number_format($order->quantity) }}</td>
                            <td>{{ $order->start_counter !== null ? number_format($order->start_counter) : '—' }}</td>
                            <td>{{ $order->remains !== null ? number_format($order->remains) : '—' }}</td>
                            <td>{{ number_format($order->charge, 2) }}</td>
                            <td><span class="badge badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No orders yet.</td>
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
