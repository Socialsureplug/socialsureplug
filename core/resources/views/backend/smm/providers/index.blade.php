@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card mb-25">
        <div class="card-body">
            <form action="{{ route('backend.smm-providers.store') }}" method="post">
                @csrf
                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. My SMM Panel" required>
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">API URL<span class="required">*</span></label>
                        <input type="url" class="form-control" name="url" placeholder="https://panel.example.com" required>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">API Key<span class="required">*</span></label>
                        <input type="text" class="form-control" name="key" placeholder="Your API key" required>
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status">
                            <option value="1">On</option>
                            <option value="0">Off</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Currency Rate<span class="required">*</span></label>
                        <input type="number" step="0.0001" min="0.0001" class="form-control" name="currency_rate" value="1" placeholder="e.g. 1500 for USD, 1 for same currency" required>
                        <small class="text-muted">Units of your site currency per 1 unit of this provider's billing currency. Use 1 if the provider already bills in your site currency.</small>
                    </div>
                </div>
                <div class="form-group width-100 mb-25">
                    <label class="form-label">Description</label>
                    <textarea class="form-control textarea" name="description" rows="2" placeholder="Optional notes"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add provider</button>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="table-header">
            <form method="get" action="{{ route('backend.smm-providers.index') }}" class="table-search-form">
                <div class="table-search-form__group">
                    <div class="table-search table-search--narrow">
                        <input type="text" name="search" class="search-input" placeholder="Search providers..." value="{{ $search ?? '' }}">
                        <i class="las la-search"></i>
                    </div>
                    <button type="submit" class="btn btn-secondary search-btn">Search</button>
                    @if(!empty($search))
                        <a href="{{ route('backend.smm-providers.index') }}" class="btn btn-outline">Clear</a>
                    @endif
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Balance</th>
                        <th>Currency Rate</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($providers as $p)
                    <tr>
                        <td>{{ $providers->firstItem() + $loop->index }}</td>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->balance !== null ? number_format($p->balance, 2) : '-' }}</td>
                        <td>{{ number_format($p->currency_rate, 4) }}</td>
                        <td>{{ $p->status ? 'On' : 'Off' }}</td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('backend.smm-providers.services', $p->id) }}" class="table-btn" title="Services"><i class="las la-list"></i></a>
                                <form action="{{ route('backend.smm-providers.balance', $p->id) }}" method="post" class="d-inline" onsubmit="return confirm('Refresh balance from API?');">
                                    @csrf
                                    <button type="submit" class="table-btn" title="Refresh balance"><i class="las la-sync"></i></button>
                                </form>
                                <button type="button" class="table-btn edit-provider" data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-url="{{ $p->url }}" data-key="{{ $p->key }}" data-status="{{ $p->status }}" data-description="{{ $p->description ?? '' }}" data-currency-rate="{{ $p->currency_rate }}" title="Edit"><i class="las la-pen"></i></button>
                                <form action="{{ route('backend.smm-providers.destroy', $p->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this provider and its SMM services?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-btn" title="Delete"><i class="las la-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center">No providers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($providers instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="pagination-wrapper">
            {{ $providers->links('vendor.pagination.default') }}
        </div>
        @endif
    </div>
</div>

<div class="modal" id="edit-provider-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit provider</h3>
            <button class="close-modal"><i class="las la-times"></i></button>
        </div>
        <form action="{{ route('backend.smm-providers.store') }}" method="post" id="edit-provider-form">
            @csrf
            <input type="hidden" name="id" id="edit-provider-id" value="">
            <div class="modal-body">
                <div class="form-group mb-25">
                    <label class="form-label">Name<span class="required">*</span></label>
                    <input type="text" class="form-control" name="name" id="edit-provider-name" placeholder="e.g. My SMM Panel" required>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">API URL<span class="required">*</span></label>
                    <input type="url" class="form-control" name="url" id="edit-provider-url" placeholder="https://panel.example.com" required>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">API Key<span class="required">*</span></label>
                    <input type="text" class="form-control" name="key" id="edit-provider-key" placeholder="Your API key" required>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status" id="edit-provider-status">
                        <option value="1">On</option>
                        <option value="0">Off</option>
                    </select>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Currency Rate<span class="required">*</span></label>
                    <input type="number" step="0.0001" min="0.0001" class="form-control" name="currency_rate" id="edit-provider-currency-rate" placeholder="e.g. 1500 for USD, 1 for same currency" required>
                    <small class="text-muted">Units of your site currency per 1 unit of this provider's billing currency. Use 1 if the provider already bills in your site currency.</small>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Description</label>
                    <textarea class="form-control textarea" name="description" id="edit-provider-description" rows="2" placeholder="Optional notes"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update provider</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
document.querySelectorAll('.edit-provider').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var modal = document.getElementById('edit-provider-modal');
        document.getElementById('edit-provider-id').value = this.getAttribute('data-id') || '';
        document.getElementById('edit-provider-name').value = this.getAttribute('data-name') || '';
        document.getElementById('edit-provider-url').value = this.getAttribute('data-url') || '';
        document.getElementById('edit-provider-key').value = this.getAttribute('data-key') || '';
        document.getElementById('edit-provider-status').value = this.getAttribute('data-status') ?? '1';
        document.getElementById('edit-provider-currency-rate').value = this.getAttribute('data-currency-rate') || '1';
        document.getElementById('edit-provider-description').value = this.getAttribute('data-description') || '';
        modal.classList.add('active');
    });
});
</script>
@endpush
