@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card mb-25">
        <div class="card-body">
            <form action="{{ route('backend.tutorials.store') }}" method="post">
                @csrf
                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Title<span class="required">*</span></label>
                        <input type="text" class="form-control" name="title" placeholder="e.g. How to Fund Your Wallet With Paystack" required>
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">URL</label>
                        <input type="text" class="form-control" name="url" placeholder="https://... or #">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group width-25">
                        <label class="form-label">Sort order</label>
                        <input type="number" class="form-control" name="sort_order" value="0" min="0" placeholder="0">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add tutorial</button>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="table-header">
            <form method="GET" action="{{ route('backend.tutorials.index') }}" class="table-search-form">
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search tutorials..." value="{{ $search ?? '' }}">
                    <i class="las la-search"></i>
                </div>
                <button type="submit" class="btn btn-secondary search-btn">Search</button>
                @if(!empty($search))
                    <a href="{{ route('backend.tutorials.index') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>URL</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tutorials as $t)
                    <tr>
                        <td>{{ $tutorials->firstItem() + $loop->index }}</td>
                        <td>{{ $t->title }}</td>
                        <td><a href="{{ $t->url }}" target="_blank" rel="noopener">{{ Str::limit($t->url, 40) }}</a></td>
                        <td>{{ $t->sort_order }}</td>
                        <td>
                            <div class="toggle-switch">
                                <input type="checkbox" id="toggle-tutorial-{{ $t->id }}" {{ $t->status ? 'checked' : '' }}>
                                <label for="toggle-tutorial-{{ $t->id }}" class="toggle-label">
                                    <span class="toggle-text">{{ $t->status ? 'On' : 'Off' }}</span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="table-actions">
                                <button type="button" class="table-btn edit-tutorial" data-id="{{ $t->id }}" data-title="{{ $t->title }}" data-url="{{ $t->url }}" data-sort="{{ $t->sort_order }}" title="Edit"><i class="las la-pen"></i></button>
                                <form action="{{ route('backend.tutorials.destroy', $t->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this tutorial?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-btn" title="Delete"><i class="las la-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center">No tutorials yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $tutorials->links('vendor.pagination.default') }}
    </div>
</div>

<div class="modal" id="edit-tutorial-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit tutorial</h3>
            <button class="close-modal"><i class="las la-times"></i></button>
        </div>
        <form action="{{ route('backend.tutorials.update', ['id' => '__ID__']) }}" method="post" id="edit-tutorial-form">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group mb-25">
                    <label class="form-label">Title<span class="required">*</span></label>
                    <input type="text" class="form-control" name="title" id="edit-tutorial-title" required>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">URL</label>
                    <input type="text" class="form-control" name="url" id="edit-tutorial-url" placeholder="https://... or #">
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Sort order</label>
                    <input type="number" class="form-control" name="sort_order" id="edit-tutorial-sort" value="0" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="submit">Update tutorial</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleSwitches = document.querySelectorAll('input[type="checkbox"][id^="toggle-tutorial-"]');
    toggleSwitches.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const id = this.id.replace('toggle-tutorial-', '');
            const toggleText = this.nextElementSibling.querySelector('.toggle-text');
            const checked = this.checked;
            toggleText.textContent = checked ? 'On' : 'Off';

            fetch('{{ route("backend.tutorials.toggle-status", ["id" => "__ID__"]) }}'.replace('__ID__', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof iziToast !== 'undefined') {
                        iziToast.success({ message: data.message || 'Status updated', position: 'topRight' });
                    }
                } else {
                    this.checked = !this.checked;
                    toggleText.textContent = this.checked ? 'On' : 'Off';
                    if (typeof iziToast !== 'undefined') {
                        iziToast.error({ message: data.message || 'Failed to update status', position: 'topRight' });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !this.checked;
                toggleText.textContent = this.checked ? 'On' : 'Off';
                if (typeof iziToast !== 'undefined') {
                    iziToast.error({ message: 'Failed to update status', position: 'topRight' });
                }
            });
        });
    });

    document.querySelectorAll('.edit-tutorial').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var modal = document.getElementById('edit-tutorial-modal');
            var form = document.getElementById('edit-tutorial-form');
            form.action = '{{ route("backend.tutorials.update", ["id" => "__ID__"]) }}'.replace('__ID__', this.getAttribute('data-id'));
            document.getElementById('edit-tutorial-title').value = this.getAttribute('data-title') || '';
            document.getElementById('edit-tutorial-url').value = this.getAttribute('data-url') || '#';
            document.getElementById('edit-tutorial-sort').value = this.getAttribute('data-sort') || '0';
            modal.classList.add('active');
        });
    });
});
</script>
@endpush
