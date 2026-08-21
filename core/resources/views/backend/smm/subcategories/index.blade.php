@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card mb-25">
        <div class="card-body">
            <form action="{{ route('backend.smm-subcategories.store') }}" method="post">
                @csrf
                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Category<span class="required">*</span></label>
                        <select class="form-control" name="smm_category_id" required>
                            <option value="">Select category</option>
                            @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">Subcategory name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Followers" required>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Sort order</label>
                        <input type="number" class="form-control" name="sort" value="0" min="0">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add subcategory</button>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="table-header">
            <form method="GET" action="{{ route('backend.smm-subcategories.index') }}" class="table-search-form">
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search..." value="{{ $search ?? '' }}">
                    <i class="las la-search"></i>
                </div>
                <select name="category_id" class="form-control" style="max-width:200px;">
                    <option value="">All categories</option>
                    @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-secondary search-btn">Filter</button>
                @if(!empty($search) || request('category_id'))
                    <a href="{{ route('backend.smm-subcategories.index') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th>Sort</th>
                        <th>Services</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subcategories as $s)
                    <tr>
                        <td>{{ $subcategories->firstItem() + $loop->index }}</td>
                        <td>{{ $s->smmCategory->name ?? '-' }}</td>
                        <td>{{ $s->name }}</td>
                        <td>{{ $s->sort }}</td>
                        <td>{{ $s->smm_services_count ?? 0 }}</td>
                        <td>
                            <div class="table-actions">
                                <button type="button" class="table-btn edit-smm-subcategory"
                                    data-id="{{ $s->id }}"
                                    data-category-id="{{ $s->smm_category_id }}"
                                    data-name="{{ $s->name }}"
                                    data-sort="{{ $s->sort }}"
                                    title="Edit"><i class="las la-pen"></i></button>
                                <form action="{{ route('backend.smm-subcategories.destroy', $s->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete? Services may lose subcategory.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-btn" title="Delete"><i class="las la-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center">No subcategories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $subcategories->links('vendor.pagination.default') }}
    </div>
</div>

<div class="modal" id="edit-smm-subcategory-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit subcategory</h3>
            <button class="close-modal"><i class="las la-times"></i></button>
        </div>
        <form action="{{ route('backend.smm-subcategories.update', ['id' => '__ID__']) }}" method="post" id="edit-smm-subcategory-form">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group mb-25">
                    <label class="form-label">Category<span class="required">*</span></label>
                    <select class="form-control" name="smm_category_id" id="edit-subcategory-category-id" required>
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Name<span class="required">*</span></label>
                    <input type="text" class="form-control" name="name" id="edit-subcategory-name" required>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Sort</label>
                    <input type="number" class="form-control" name="sort" id="edit-subcategory-sort" value="0" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="submit">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
document.querySelectorAll('.edit-smm-subcategory').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var modal = document.getElementById('edit-smm-subcategory-modal');
        var form = document.getElementById('edit-smm-subcategory-form');
        form.action = '{{ route("backend.smm-subcategories.update", ["id" => "__ID__"]) }}'.replace('__ID__', this.getAttribute('data-id'));
        document.getElementById('edit-subcategory-category-id').value = this.getAttribute('data-category-id') || '';
        document.getElementById('edit-subcategory-name').value = this.getAttribute('data-name') || '';
        document.getElementById('edit-subcategory-sort').value = this.getAttribute('data-sort') || '0';
        modal.classList.add('active');
    });
});
</script>
@endpush