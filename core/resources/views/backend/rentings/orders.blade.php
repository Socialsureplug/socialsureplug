@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card table-card">
        <div class="table-header">
            <form method="get" action="{{ route('backend.rentings.orders') }}" class="table-search-form">
                @if(request('user_id'))
                    <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                @endif
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search by number, service, user..." value="{{ $search ?? '' }}">
                    <i class="las la-search"></i>
                </div>
                <button type="submit" class="btn btn-secondary search-btn">Search</button>
                @if(!empty($search) || request('user_id') || request('status'))
                    <a href="{{ route('backend.rentings.orders', array_filter(['user_id' => request('user_id'), 'status' => request('status')])) }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Number</th>
                        <th>Service</th>
                        <th>API</th>
                        <th>Country</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Expires</th>
                        <th>Rented</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $row)
                        <tr>
                            <td>{{ $orders->firstItem() + $loop->index }}</td>
                            <td>{{ $row->user?->email ?? 'N/A' }}</td>
                            <td>{{ $row->phone ?: '-' }}</td>
                            <td>{{ $row->service_name ?? $row->service_code }}</td>
                            <td>{{ $row->rentingApi?->name ?? $row->api_id }}</td>
                            <td>{{ $row->rentingCountry?->name ?? $row->country_id }}</td>
                            <td>{{ $row->dcount }} {{ $row->dtype }}</td>
                            <td>{{ number_format((float) $row->price, 2) }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($row->status)) }}</td>
                            <td>{{ $row->expires_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ $row->rented_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        @include('backend.partial.table-empty', ['colspan' => 11, 'message' => 'No renting orders found.'])
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links('vendor.pagination.default') }}
    </div>
</div>
@endsection
