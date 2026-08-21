@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card mb-25">
        <div class="card-body">
            <form action="{{ route('backend.smm-categories.store') }}" method="post">
                @csrf
                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Category name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Instagram Followers" required>
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">Sort order</label>
                        <input type="number" class="form-control" name="sort" value="0" min="0" placeholder="0">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add category</button>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="table-header">
            <form method="GET" action="{{ route('backend.smm-categories.index') }}" class="table-search-form">
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search categories..." value="{{ $search ?? '' }}">
                    <i class="las la-search"></i>
                </div>
                <button type="submit" class="btn btn-secondary search-btn">Search</button>
                @if(!empty($search))
                    <a href="{{ route('backend.smm-categories.index') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Sort</th>
                        <th>Services</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $c)
                    <tr>
                        <td>{{ $categories->firstItem() + $loop->index }}</td>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->sort }}</td>
                        <td>{{ $c->smm_services_count ?? 0 }}</td>
                        <td>
                            <div class="table-actions">
                                <button type="button" class="table-btn edit-smm-category" data-id="{{ $c->id }}" data-name="{{ $c->name }}" data-sort="{{ $c->sort }}" title="Edit"><i class="las la-pen"></i></button>
                                <form action="{{ route('backend.smm-categories.destroy', $c->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-btn" title="Delete"><i class="las la-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center">No categories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $categories->links('vendor.pagination.default') }}
    </div>
</div>

<div class="modal" id="edit-smm-category-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit category</h3>
            <button class="close-modal"><i class="las la-times"></i></button>
        </div>
        <form action="{{ route('backend.smm-categories.update', ['id' => '__ID__']) }}" method="post" id="edit-smm-category-form">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group mb-25">
                    <label class="form-label">Category name<span class="required">*</span></label>
                    <input type="text" class="form-control" name="name" id="edit-category-name" required>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Sort order</label>
                    <input type="number" class="form-control" name="sort" id="edit-category-sort" value="0" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="submit">Update category</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
document.querySelectorAll('.edit-smm-category').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var modal = document.getElementById('edit-smm-category-modal');
        var form = document.getElementById('edit-smm-category-form');
        form.action = '{{ route("backend.smm-categories.update", ["id" => "__ID__"]) }}'.replace('__ID__', this.getAttribute('data-id'));
        document.getElementById('edit-category-name').value = this.getAttribute('data-name') || '';
        document.getElementById('edit-category-sort').value = this.getAttribute('data-sort') || '0';
        modal.classList.add('active');
    });
});
</script>
@endpush
