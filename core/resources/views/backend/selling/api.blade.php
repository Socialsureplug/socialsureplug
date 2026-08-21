@extends('backend.layout.app')

@section('content')
    <div class="container">
        <div class="container-header">
            <h1>{{ $title }}</h1>
            <button type="button" class="btn btn-primary" id="add-selling-api">Add API</button>
        </div>

        <div class="card table-card">
            <div class="table-header">
                <form method="GET" action="{{ route('backend.selling.api') }}" class="table-search-form">
                    <div class="table-search">
                        <input type="text" name="search" class="search-input" placeholder="Search by name, label, or base URL..." value="{{ $search ?? '' }}">
                        <i class="las la-search"></i>
                    </div>
                    <button type="submit" class="btn btn-secondary search-btn">Search</button>
                    @if(!empty($search))
                        <a href="{{ route('backend.selling.api') }}" class="btn btn-outline">Clear</a>
                    @endif
                </form>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($apis as $api)
                            <tr>
                                <td>{{ $apis->firstItem() + $loop->index }}</td>
                                <td>{{ $api->name }}</td>
                                <td>{{ $balances[$api->id] ?? '—' }}</td>
                                <td>
                                    <div class="toggle-switch">
                                        <input type="checkbox" id="toggle-selling-api-{{ $api->id }}"
                                            class="selling-api-status-toggle"
                                            {{ $api->status == 1 ? 'checked' : '' }}>
                                        <label for="toggle-selling-api-{{ $api->id }}" class="toggle-label">
                                            <span class="toggle-text">{{ $api->status == 1 ? 'On' : 'Off' }}</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button
                                            type="button"
                                            class="table-btn edit-selling-api"
                                            data-id="{{ $api->id }}"
                                            data-name="{{ e($api->name) }}"
                                            data-label="{{ e($api->label) }}"
                                            data-base-url="{{ e($api->base_url) }}"
                                            data-api-key="{{ e($api->api_key) }}"
                                            data-status="{{ $api->status }}"
                                        ><i class="las la-pen"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No Selling API yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $apis->links('vendor.pagination.default') }}
        </div>
    </div>


    <div class="modal" id="add-selling-api-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add API</h3>
                <button class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.selling.api.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Enter api name" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Label<span class="required">*</span></label>
                        <input type="text" class="form-control" name="label" value="{{ old('label') }}" placeholder="Enter api label" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Base URL<span class="required">*</span></label>
                        <input type="url" class="form-control" name="base_url" value="{{ old('base_url') }}" placeholder="Enter base url" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">API Key<span class="required">*</span></label>
                        <input type="text" class="form-control" name="api_key" value="{{ old('api_key') }}" placeholder="Enter api key" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status<span class="required">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="1" @selected(old('status', '1') == '1')>Active</option>
                            <option value="0" @selected(old('status', '1') == '0')>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Add API</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="edit-selling-api-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit API</h3>
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
                        <label class="form-label">Label</label>
                        <input type="text" class="form-control" id="edit-selling-api-label" readonly tabindex="-1">
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Base URL<span class="required">*</span></label>
                        <input type="url" class="form-control" name="base_url" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">API Key<span class="required">*</span></label>
                        <input type="text" class="form-control" name="api_key" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status<span class="required">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update API</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const apiBase = @json(url('/backend/selling/api'));
    const toggleUrl = @json(route('backend.selling.api.toggle-status'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    const editModal = document.getElementById('edit-selling-api-modal');
    const labelField = document.getElementById('edit-selling-api-label');
    const form = editModal?.querySelector('form');

    for (const btn of document.querySelectorAll('.edit-selling-api')) {
        btn.addEventListener('click', () => {
            if (!form || !editModal) return;
            const { id, name, label, baseUrl, apiKey, status } = btn.dataset;
            form.action = `${apiBase}/${id}`;
            const nameInput = form.querySelector('[name="name"]');
            const baseInput = form.querySelector('[name="base_url"]');
            const keyInput = form.querySelector('[name="api_key"]');
            const statusSelect = form.querySelector('[name="status"]');
            if (nameInput) nameInput.value = name ?? '';
            if (labelField) labelField.value = label ?? '';
            if (baseInput) baseInput.value = baseUrl ?? '';
            if (keyInput) keyInput.value = apiKey ?? '';
            if (statusSelect) statusSelect.value = status ?? '1';
            editModal.classList.add('active');
        });
    }

    for (const toggle of document.querySelectorAll('.selling-api-status-toggle')) {
        toggle.addEventListener('change', async () => {
            const id = toggle.id.replace('toggle-selling-api-', '');
            const status = toggle.checked ? 1 : 0;
            const textEl = toggle.nextElementSibling?.querySelector('.toggle-text');
            if (textEl) textEl.textContent = status ? 'On' : 'Off';

            const revert = () => {
                toggle.checked = !toggle.checked;
                if (textEl) textEl.textContent = toggle.checked ? 'On' : 'Off';
            };

            try {
                const res = await fetch(toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ id: Number(id), status }),
                });
                const data = await res.json().catch(() => ({}));
                if (data.success) {
                    iziToast.success({ message: 'Status updated successfully', position: 'topRight' });
                } else {
                    revert();
                    iziToast.error({ message: data.message ?? 'Failed to update status', position: 'topRight' });
                }
            } catch {
                revert();
                iziToast.error({ message: 'Failed to update status', position: 'topRight' });
            }
        });
    }
});
</script>
@endpush
