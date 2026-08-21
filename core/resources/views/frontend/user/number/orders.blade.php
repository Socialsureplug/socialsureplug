@extends('frontend.layout.dash')

@section('content')
<div class="card mp-20">
    <h2 class="title">Number Log</h2>
    <div class="table-header">
        <form method="GET" action="{{ route('user.number.orders') }}" class="logs-search">
            <div class="logs-search-bar">
                <i class="ti ti-search logs-search-icon"></i>
                <input type="text" name="search" class="logs-search-input" placeholder="Search numbers..." value="{{ $search ?? '' }}">
                <button type="submit" class="logs-search-submit"><i class="ti ti-search"></i><span>Search</span></button>
            </div>
            @if(!empty($search))
                <a href="{{ route('user.number.orders') }}" class="logs-search-clear"><i class="ti ti-x"></i> Clear filters</a>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Number</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>SMS</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($numbers as $n)
                @php
                    $statusMap = [
                        'completed' => 'success',
                        'waiting_sms' => 'info',
                        'cancelled' => 'danger',
                        'expired' => 'warning',
                        'failed' => 'danger',
                    ];
                    $statusType = $statusMap[$n->status] ?? 'info';
                @endphp
                <tr>
                    <td>{{ $n->service_name }}</td>
                    <td>{{ $n->phone ? '+' . ltrim($n->phone, '+') : '-' }}</td>
                    <td>{{ number_format((float) $n->price, 2) }}</td>
                    <td><span class="status-badge {{ $statusType }}">{{ str_replace('_', ' ', ucfirst($n->status)) }}</span></td>
                    <td>{{ $n->sms_code ?: '-' }}</td>
                    <td>{{ $n->purchased_at?->format('Y-m-d H:i') ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty">
                            <div class="icon">
                                <i class="ti ti-inbox"></i>
                            </div>
                            <span>No numbers yet.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $numbers->links('vendor.pagination.default') }}
</div>
@endsection
