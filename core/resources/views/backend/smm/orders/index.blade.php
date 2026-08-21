@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card table-card">
        <div class="table-header">
            <form method="GET" action="{{ route('backend.smm-orders.index') }}" class="table-search-form">
                <div class="table-search">
                    <input type="text" name="q" class="search-input" placeholder="Search by order ID or link..." value="{{ $search ?? '' }}">
                    <i class="las la-search"></i>
                </div>
                <select name="status" class="form-control" style="max-width:180px;" onchange="this.form.submit()">
                    <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="awaiting" {{ ($status ?? '') === 'awaiting' ? 'selected' : '' }}>Awaiting</option>
                    <option value="inprogress" {{ ($status ?? '') === 'inprogress' ? 'selected' : '' }}>In progress</option>
                    <option value="completed" {{ ($status ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="partial" {{ ($status ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="canceled" {{ ($status ?? '') === 'canceled' ? 'selected' : '' }}>Canceled</option>
                    <option value="error" {{ ($status ?? '') === 'error' ? 'selected' : '' }}>Error</option>
                    <option value="fail" {{ ($status ?? '') === 'fail' ? 'selected' : '' }}>Fail</option>
                </select>
                <button type="submit" class="btn btn-secondary search-btn">Search</button>
                @if(!empty($search) || (!empty($status) && $status !== 'all'))
                    <a href="{{ route('backend.smm-orders.index') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Service</th>
                        <th>Link</th>
                        <th>Qty</th>
                        <th>Start count</th>
                        <th>Remains</th>
                        <th>Charge</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                    <tr>
                        <td>{{ $orders->firstItem() + $loop->index }}</td>
                        <td>{{ $o->id }}</td>
                        <td>{{ $o->user->email ?? $o->user_id }}</td>
                        <td>{{ $o->smmService->name ?? '-' }}</td>
                        <td><span class="text-ellipsis" title="{{ $o->link }}">{{ Str::limit($o->link, 30) }}</span></td>
                        <td>{{ $o->quantity }}</td>
                        <td>{{ $o->start_counter !== null ? number_format($o->start_counter) : '—' }}</td>
                        <td>{{ $o->remains !== null ? number_format($o->remains) : '—' }}</td>
                        <td>{{ number_format($o->charge, 2) }}</td>
                        <td>
                            @php
                                $statusClass = 'status';
                                if (in_array($o->status, ['completed'])) $statusClass = 'status success';
                                elseif (in_array($o->status, ['awaiting', 'inprogress'])) $statusClass = 'status pending';
                                elseif (in_array($o->status, ['error', 'fail', 'canceled'])) $statusClass = 'status failed';
                            @endphp
                            <span class="{{ $statusClass }}">{{ ucfirst($o->status) }}</span>
                            @if($o->refunded)
                                <span class="status success" style="margin-left:4px;">Refunded</span>
                            @endif
                        </td>
                        <td>{{ $o->created_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <div class="table-actions">
                                <button type="button" class="table-btn edit-smm-order" data-id="{{ $o->id }}" data-link="{{ $o->link }}" data-start-counter="{{ $o->start_counter ?? '' }}" data-remains="{{ $o->remains ?? '' }}" data-status="{{ $o->status }}" title="Edit"><i class="las la-pen"></i></button>
                                @if(in_array($o->status, ['error', 'fail']) && !$o->refunded)
                                <form action="{{ route('backend.smm-orders.resend', $o->id) }}" method="post" class="d-inline" onsubmit="return confirm('Resend this order to provider?');">
                                    @csrf
                                    <button type="submit" class="table-btn" title="Resend"><i class="las la-redo"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="12" class="text-center">No orders found.</td></tr>
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

<div class="modal" id="edit-smm-order-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit order</h3>
            <button class="close-modal"><i class="las la-times"></i></button>
        </div>
        <form action="{{ route('backend.smm-orders.store') }}" method="post" id="edit-smm-order-form">
            @csrf
            <input type="hidden" name="id" id="edit-order-id">
            <div class="modal-body">
                <div class="form-group mb-25">
                    <label class="form-label">Link</label>
                    <input type="text" class="form-control" name="link" id="edit-order-link" placeholder="https://">
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Start counter</label>
                    <input type="number" class="form-control" name="start_counter" id="edit-order-start-counter" min="0" placeholder="0">
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Remains</label>
                    <input type="number" class="form-control" name="remains" id="edit-order-remains" min="0" placeholder="0">
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Status<span class="required">*</span></label>
                    <select class="form-control" name="status" id="edit-order-status" required>
                        <option value="awaiting">Awaiting</option>
                        <option value="inprogress">In progress</option>
                        <option value="completed">Completed</option>
                        <option value="partial">Partial</option>
                        <option value="canceled">Canceled</option>
                        <option value="error">Error</option>
                        <option value="fail">Fail</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="submit">Update order</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
document.querySelectorAll('.edit-smm-order').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var modal = document.getElementById('edit-smm-order-modal');
        document.getElementById('edit-order-id').value = this.getAttribute('data-id') || '';
        document.getElementById('edit-order-link').value = this.getAttribute('data-link') || '';
        document.getElementById('edit-order-start-counter').value = this.getAttribute('data-start-counter') || '';
        document.getElementById('edit-order-remains').value = this.getAttribute('data-remains') || '';
        document.getElementById('edit-order-status').value = this.getAttribute('data-status') || 'awaiting';
        modal.classList.add('active');
    });
});
</script>
@endpush
