@extends('backend.layout.app')

@section('content')
    <div class="container">
        <div class="container-header">
            <h1>{{ $title }}</h1>

            <div class="container-right">
                <a href="{{ route('backend.numbers.api') }}" class="btn btn-outline"><i class="las la-arrow-left"></i>Back to API</a>
                <button type="button" class="btn btn-primary" id="update-number-countries">
                    <i class="las la-sync"></i>Update Country
                </button>
                <button class="btn btn-primary" id="add-number-country"><i class="las la-plus"></i>Add Country</button>
            </div>
        </div>

        <div class="card table-card">
            <div class="filter-grid">
                <form method="GET" action="{{ route('backend.numbers.api.countries', $api->id) }}" class="table-search-form">
                    <div class="table-search">
                        <input type="text" name="search" class="search-input"
                            placeholder="Search by name, code or country id..." value="{{ $search ?? '' }}">
                        <i class="las la-search"></i>
                    </div>
                    <button type="submit" class="btn btn-secondary search-btn">Search</button>
                    @if (!empty($search))
                        <a href="{{ route('backend.numbers.api.countries', $api->id) }}" class="btn btn-outline clear-btn">Clear</a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Country ID</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($countries as $country)
                            <tr>
                                <td>{{ $countries->firstItem() + $loop->index }}</td>
                                <td>{{ $country->name }}</td>
                                <td>{{ $country->code }}</td>
                                <td>{{ $country->country_id }}</td>
                                <td>
                                    <div class="toggle-switch">
                                        <input type="checkbox" id="toggle-{{ $country->id }}"
                                            {{ $country->status == 1 ? 'checked' : '' }}>
                                        <label for="toggle-{{ $country->id }}" class="toggle-label">
                                            <span class="toggle-text">{{ $country->status == 1 ? 'On' : 'Off' }}</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="table-btn edit-number-country"
                                            data-id="{{ $country->id }}" data-name="{{ $country->name }}"
                                            data-code="{{ $country->code }}"
                                            data-country-id="{{ $country->country_id }}"
                                            data-status="{{ $country->status }}">
                                            <i class="las la-pen"></i>
                                        </button>
                                        <form action="{{ route('backend.numbers.countries.destroy', $country->id) }}"
                                            method="post" class="d-inline"
                                            data-confirm="Delete this country?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="table-btn"><i class="las la-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @include('backend.partial.table-empty', ['colspan' => 6])
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $countries->links('vendor.pagination.default') }}
        </div>
    </div>

    <div class="modal" id="add-number-country-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add Country</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>

            <form action="{{ route('backend.numbers.api.countries.store', $api->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. United States" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Code<span class="required">*</span></label>
                        <input type="text" class="form-control" name="code" placeholder="e.g. 12" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Country ID<span class="required">*</span></label>
                        <input type="text" class="form-control" name="country_id" placeholder="Provider country id, e.g. 12" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Status<span class="required">*</span></label>
                        <select class="form-control" name="status" required>
                            <option value="1" selected>On</option>
                            <option value="0">Off</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit"><i class="las la-save"></i>Save Country</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="edit-number-country-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Country</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.numbers.countries.update', ['id' => '__ID__']) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Code<span class="required">*</span></label>
                        <input type="text" class="form-control" name="code" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Country ID<span class="required">*</span></label>
                        <input type="text" class="form-control" name="country_id" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Status<span class="required">*</span></label>
                        <select class="form-control" name="status" required>
                            <option value="1">On</option>
                            <option value="0">Off</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit"><i class="las la-save"></i>Update Country</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateBtn = document.getElementById('update-number-countries');

    if (updateBtn) {
        updateBtn.addEventListener('click', async function() {
            if (!(await window.confirmModal('Update countries from API? New countries will be added; existing codes will be skipped.'))) {
                return;
            }

            const originalHtml = updateBtn.innerHTML;
            updateBtn.disabled = true;
            updateBtn.innerHTML = '<i class="las la-spinner"></i> Updating...';

            try {
                const response = await fetch('{{ route('backend.numbers.api.countries.import', $api->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    iziToast.success({
                        message: data.message,
                        position: 'topRight',
                    });
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    iziToast.error({
                        message: data.message || 'Update failed',
                        position: 'topRight',
                    });
                }
            } catch (error) {
                iziToast.error({
                    message: 'Request failed. Please try again.',
                    position: 'topRight',
                });
            } finally {
                updateBtn.disabled = false;
                updateBtn.innerHTML = originalHtml;
            }
        });
    }

    document.querySelectorAll('input[type="checkbox"][id^="toggle-"]').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const id = this.id.replace('toggle-', '');
            const status = this.checked ? 1 : 0;
            const toggleText = this.nextElementSibling.querySelector('.toggle-text');

            toggleText.textContent = status ? 'On' : 'Off';

            fetch('{{ route('backend.numbers.countries.toggle-status') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ id: id, status: status }),
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    iziToast.success({
                        message: data.message || 'Status updated successfully',
                        position: 'topRight',
                    });
                } else {
                    toggle.checked = !toggle.checked;
                    toggleText.textContent = toggle.checked ? 'On' : 'Off';
                    iziToast.error({
                        message: data.message || 'Failed to update status',
                        position: 'topRight',
                    });
                }
            })
            .catch(function() {
                toggle.checked = !toggle.checked;
                toggleText.textContent = toggle.checked ? 'On' : 'Off';
                iziToast.error({
                    message: 'Failed to update status',
                    position: 'topRight',
                });
            });
        });
    });

    document.querySelectorAll('.edit-number-country').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const modal = document.getElementById('edit-number-country-modal');
            modal.querySelector('input[name="name"]').value = this.getAttribute('data-name') || '';
            modal.querySelector('input[name="code"]').value = this.getAttribute('data-code') || '';
            modal.querySelector('input[name="country_id"]').value = this.getAttribute('data-country-id') || '';
            modal.querySelector('select[name="status"]').value = this.getAttribute('data-status') || '1';
            modal.querySelector('form').action = '{{ route("backend.numbers.countries.update", ["id" => "__ID__"]) }}'.replace('__ID__', this.getAttribute('data-id'));
            modal.classList.add('active');
        });
    });
});
</script>
@endpush
