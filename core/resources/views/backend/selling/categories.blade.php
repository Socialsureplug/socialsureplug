@extends('backend.layout.app')

@section('content')
    <div class="container">
        <div class="container-header">
            <h1>{{ $title }}</h1>
            <div class="container-right">
                <button type="button" class="btn btn-primary" id="import-selling-categories">Import Categories</button>
                <button type="button" class="btn btn-primary" id="add-selling-category">Add category</button>
            </div>
        </div>

        <div class="card table-card">
            <div class="table-header">
                <form method="GET" action="{{ route('backend.selling.categories') }}" class="table-search-form">
                    <div class="table-search">
                        <input type="text" name="search" class="search-input" placeholder="Search by name..." value="{{ $search ?? '' }}">
                        <i class="las la-search"></i>
                    </div>
                    <button type="submit" class="btn btn-secondary search-btn">Search</button>
                    @if(!empty($search))
                        <a href="{{ route('backend.selling.categories') }}" class="btn btn-outline">Clear</a>
                    @endif
                </form>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Category ID</th>
                            <th>API</th>
                            <th>Sort</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $categories->firstItem() + $loop->index }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->slug ?? '—' }}</td>
                                <td>{{ $category->category_id ?? '—' }}</td>
                                <td>{{ $category->sellingApi?->name ?? '—' }}</td>
                                <td>{{ $category->sort }}</td>
                                <td>
                                    <div class="table-actions">
                                        <button
                                            type="button"
                                            class="table-btn edit-selling-category"
                                            data-id="{{ $category->id }}"
                                            data-name="{{ e($category->name) }}"
                                            data-slug="{{ e($category->slug ?? '') }}"
                                            data-category-id="{{ $category->category_id ?? '' }}"
                                            data-sort="{{ $category->sort }}"
                                        ><i class="las la-pen"></i></button>
                                        <form action="{{ route('backend.selling.categories.destroy', $category->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="table-btn"><i class="las la-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No categories yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $categories->links('vendor.pagination.default') }}
        </div>
    </div>

    <div class="modal" id="import-selling-categories-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Import categories</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.selling.categories.import') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">API<span class="required">*</span></label>
                        <select name="api_id" class="form-control" required>
                            <option value="">Select API</option>
                            @foreach ($apis ?? [] as $api)
                                <option value="{{ $api->id }}">{{ $api->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="add-selling-category-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add category</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.selling.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Category name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sort</label>
                        <input type="number" class="form-control" name="sort" value="{{ old('sort', 0) }}" min="0" step="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Add category</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="edit-selling-category-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit category</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" placeholder="social-media">
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Category ID (API)</label>
                        <input type="number" class="form-control" name="category_id" min="0" step="1" placeholder="Remote category id">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sort</label>
                        <input type="number" class="form-control" name="sort" min="0" step="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update category</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const base = @json(url('/backend/selling/categories'));
    const modal = document.getElementById('edit-selling-category-modal');
    const form = modal?.querySelector('form');
    if (!form || !modal) return;

    for (const btn of document.querySelectorAll('.edit-selling-category')) {
        btn.addEventListener('click', () => {
            const { id, name, slug, categoryId, sort } = btn.dataset;
            form.action = `${base}/${id}`;
            const nameInput = form.querySelector('[name="name"]');
            const slugInput = form.querySelector('[name="slug"]');
            const categoryIdInput = form.querySelector('[name="category_id"]');
            const sortInput = form.querySelector('[name="sort"]');
            if (nameInput) nameInput.value = name ?? '';
            if (slugInput) slugInput.value = slug ?? '';
            if (categoryIdInput) categoryIdInput.value = categoryId ?? '';
            if (sortInput) sortInput.value = sort ?? '0';
            modal.classList.add('active');
        });
    }
});
</script>
@endpush
