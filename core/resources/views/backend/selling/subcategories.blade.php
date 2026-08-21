@extends('backend.layout.app')

@section('content')
    <div class="container">
        <div class="container-header">
            <h1>{{ $title }}</h1>
            <div class="container-right">
                <button type="button" class="btn btn-primary" id="import-selling-subcategories">Import subcategories</button>
                <button type="button" class="btn btn-primary" id="add-selling-subcategory">Add subcategory</button>
            </div>
        </div>

        <div class="card table-card">
            <div class="table-header">
                <form method="GET" action="{{ route('backend.selling.subcategories') }}" class="table-search-form">
                    <div class="table-search">
                        <input type="text" name="search" class="search-input" placeholder="Search by name..." value="{{ $search ?? '' }}">
                        <i class="las la-search"></i>
                    </div>
                    <select name="selling_category_id" class="form-control" style="max-width:220px;" onchange="this.form.submit()">
                        <option value="">All categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) ($selling_category_id ?? '') === (string) $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-secondary search-btn">Search</button>
                    @if(!empty($search) || !empty($selling_category_id))
                        <a href="{{ route('backend.selling.subcategories') }}" class="btn btn-outline">Clear</a>
                    @endif
                </form>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Category</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Subcategory ID</th>
                            <th>API</th>
                            <th>Sort</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subcategories as $sub)
                            <tr>
                                <td>{{ $subcategories->firstItem() + $loop->index }}</td>
                                <td>{{ $sub->sellingCategory?->name ?? '—' }}</td>
                                <td>{{ $sub->name }}</td>
                                <td>{{ $sub->slug ?? '—' }}</td>
                                <td>{{ $sub->subcategory_id ?? '—' }}</td>
                                <td>{{ $sub->sellingApi?->name ?? '—' }}</td>
                                <td>{{ $sub->sort }}</td>
                                <td>
                                    <div class="table-actions">
                                        <button
                                            type="button"
                                            class="table-btn edit-selling-subcategory"
                                            data-id="{{ $sub->id }}"
                                            data-selling-category-id="{{ $sub->selling_category_id }}"
                                            data-name="{{ e($sub->name) }}"
                                            data-slug="{{ e($sub->slug ?? '') }}"
                                            data-subcategory-id="{{ $sub->subcategory_id ?? '' }}"
                                            data-sort="{{ $sub->sort }}"
                                        ><i class="las la-pen"></i></button>
                                        <form action="{{ route('backend.selling.subcategories.destroy', $sub->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this subcategory?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="table-btn"><i class="las la-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No subcategories yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $subcategories->links('vendor.pagination.default') }}
        </div>
    </div>

    <div class="modal" id="import-selling-subcategories-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Import subcategories</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.selling.subcategories.import') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="mb-20">Imports subcategories for all categories already linked to this API.</p>
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

    <div class="modal" id="add-selling-subcategory-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add subcategory</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.selling.subcategories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Category<span class="required">*</span></label>
                        <select name="selling_category_id" class="form-control" required>
                            <option value="">Select category</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sort</label>
                        <input type="number" class="form-control" name="sort" value="0" min="0" step="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add subcategory</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="edit-selling-subcategory-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit subcategory</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Category<span class="required">*</span></label>
                        <select name="selling_category_id" class="form-control" required>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug">
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Subcategory ID (API)</label>
                        <input type="number" class="form-control" name="subcategory_id" min="0" step="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sort</label>
                        <input type="number" class="form-control" name="sort" min="0" step="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update subcategory</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const base = @json(url('/backend/selling/subcategories'));
    const modal = document.getElementById('edit-selling-subcategory-modal');
    const form = modal?.querySelector('form');
    if (!form || !modal) return;

    for (const btn of document.querySelectorAll('.edit-selling-subcategory')) {
        btn.addEventListener('click', () => {
            const { id, name, slug, subcategoryId, sellingCategoryId, sort } = btn.dataset;
            form.action = `${base}/${id}`;
            const catSelect = form.querySelector('[name="selling_category_id"]');
            const nameInput = form.querySelector('[name="name"]');
            const slugInput = form.querySelector('[name="slug"]');
            const subIdInput = form.querySelector('[name="subcategory_id"]');
            const sortInput = form.querySelector('[name="sort"]');
            if (catSelect) catSelect.value = sellingCategoryId ?? '';
            if (nameInput) nameInput.value = name ?? '';
            if (slugInput) slugInput.value = slug ?? '';
            if (subIdInput) subIdInput.value = subcategoryId ?? '';
            if (sortInput) sortInput.value = sort ?? '0';
            modal.classList.add('active');
        });
    }
});
</script>
@endpush
