@extends('backend.layout.app')

@section('content')
    <div class="container">
        <div class="container-header">
            <h1>{{ $title }}</h1>

            <div class="container-right">
                <a href="{{ route('backend.numbers.api') }}" class="btn btn-outline"><i class="las la-arrow-left"></i>Back to
                    API</a>
                <button type="button" class="btn btn-primary" id="update-number-services">
                    <i class="las la-sync"></i>Update Service
                </button>
                <button class="btn btn-primary" id="add-number-service"><i class="las la-plus"></i>Add Service</button>
            </div>
        </div>

        <div class="card table-card">
            <div class="filter-grid">
                <form method="GET" action="{{ route('backend.numbers.api.services', $api->id) }}"
                    class="table-search-form">
                    <div class="table-search">
                        <input type="text" name="search" class="search-input"
                            placeholder="Search by name, code or country..." value="{{ $search ?? '' }}">
                        <i class="las la-search"></i>
                    </div>
                    <button type="submit" class="btn btn-secondary search-btn">Search</button>
                    @if (!empty($search))
                        <a href="{{ route('backend.numbers.api.services', $api->id) }}"
                            class="btn btn-outline clear-btn">Clear</a>
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
                            <th>Country</th>
                            <th>Provider Price</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($services as $service)
                            <tr>
                                <td>{{ $services->firstItem() + $loop->index }}</td>
                                <td>{{ $service->name }}</td>
                                <td>{{ $service->code }}</td>
                                <td>{{ $service->numberCountry?->name ?? '—' }}</td>
                                <td>{{ number_format($service->provider_price, 2) }}</td>
                                <td>{{ number_format($service->price, 2) }}</td>
                                <td>
                                    <div class="toggle-switch">
                                        <input type="checkbox" id="toggle-{{ $service->id }}"
                                            {{ $service->status == 1 ? 'checked' : '' }}>
                                        <label for="toggle-{{ $service->id }}" class="toggle-label">
                                            <span class="toggle-text">{{ $service->status == 1 ? 'On' : 'Off' }}</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="table-btn edit-number-service"
                                            data-id="{{ $service->id }}" data-name="{{ $service->name }}"
                                            data-code="{{ $service->code }}" data-country-id="{{ $service->country_id }}"
                                            data-provider-price="{{ $service->provider_price }}"
                                            data-price="{{ $service->price }}" data-status="{{ $service->status }}">
                                            <i class="las la-pen"></i>
                                        </button>
                                        <form action="{{ route('backend.numbers.services.destroy', $service->id) }}"
                                            method="post" class="d-inline"
                                            data-confirm="Delete this service?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="table-btn"><i class="las la-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @include('backend.partial.table-empty', ['colspan' => 8])
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $services->links('vendor.pagination.default') }}
        </div>
    </div>

    <div class="modal" id="add-number-service-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add Service</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>

            <form action="{{ route('backend.numbers.api.services.store', $api->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. WhatsApp" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Code<span class="required">*</span></label>
                        <input type="text" class="form-control" name="code" placeholder="e.g. wa" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Country<span class="required">*</span></label>
                        <select class="form-control" name="country_id" required>
                            <option value="">Select country</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }} ({{ $country->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Provider price<span class="required">*</span></label>
                        <input type="number" class="form-control" name="provider_price" step="0.01" min="0"
                            placeholder="0.00" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Price<span class="required">*</span></label>
                        <input type="number" class="form-control" name="price" step="0.01" min="0"
                            placeholder="0.00" required>
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
                    <button class="btn btn-primary" type="submit"><i class="las la-save"></i>Save Service</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="edit-number-service-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Service</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.numbers.services.update', ['id' => '__ID__']) }}" method="POST">
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
                        <label class="form-label">Country<span class="required">*</span></label>
                        <select class="form-control" name="country_id" required>
                            <option value="">Select country</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }} ({{ $country->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Provider price<span class="required">*</span></label>
                        <input type="number" class="form-control" name="provider_price" step="0.01" min="0"
                            required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Price<span class="required">*</span></label>
                        <input type="number" class="form-control" name="price" step="0.01" min="0"
                            required>
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
                    <button class="btn btn-primary" type="submit"><i class="las la-save"></i>Update Service</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const updateBtn = document.getElementById('update-number-services');
            if (updateBtn) {
                let pollTimer = null;
                const syncStatusUrlTemplate =
                    '{{ route('backend.numbers.sync-runs.show', ['id' => '__ID__']) }}';
                const defaultBtnHtml = updateBtn.innerHTML;

                const stopPoll = () => {
                    if (pollTimer) {
                        clearInterval(pollTimer);
                        pollTimer = null;
                    }
                };

                const handleSyncResponse = (data, originalHtml) => {
                    updateBtn.innerHTML =
                        '<i class="las la-spinner la-spin"></i> ' +
                        (data.progress || '0/0') +
                        ' (' + (data.percent || 0) + '%)';

                    if (!data.finished) {
                        return;
                    }

                    stopPoll();
                    updateBtn.disabled = false;
                    updateBtn.innerHTML = originalHtml;

                    if (data.status === 'completed') {
                        iziToast.success({
                            message: 'Done. Imported ' + (data.imported || 0) +
                                ', skipped ' + (data.skipped || 0) + '.',
                            position: 'topRight',
                        });
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        iziToast.error({
                            message: data.error || 'Import failed',
                            position: 'topRight',
                        });
                    }
                };

                const pollSync = (syncRunId, originalHtml) => {
                    const statusUrl = syncStatusUrlTemplate.replace('__ID__', syncRunId);

                    const tick = async () => {
                        try {
                            const res = await fetch(statusUrl, {
                                headers: { 'Accept': 'application/json' },
                            });
                            const data = await res.json();
                            handleSyncResponse(data, originalHtml);
                        } catch (e) {
                            stopPoll();
                            updateBtn.disabled = false;
                            updateBtn.innerHTML = originalHtml;
                            iziToast.error({
                                message: 'Progress check failed',
                                position: 'topRight',
                            });
                        }
                    };

                    stopPoll();
                    updateBtn.disabled = true;
                    tick();
                    pollTimer = setInterval(tick, 2000);
                };

                @if($activeSyncRun ?? null)
                    pollSync({{ $activeSyncRun->id }}, defaultBtnHtml);
                @endif

                updateBtn.addEventListener('click', async function() {
                    if (!(await window.confirmModal(
                        'Update services from API? New service/country pairs will be added; existing ones will be skipped.'
                    ))) {
                        return;
                    }

                    if (updateBtn.disabled) {
                        return;
                    }

                    updateBtn.disabled = true;
                    updateBtn.innerHTML = '<i class="las la-spinner la-spin"></i> Queuing...';

                    try {
                        const response = await fetch(
                            '{{ route('backend.numbers.api.services.import', $api->id) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content,
                                },
                            });
                        const data = await response.json();

                        if (response.ok && data.success && data.sync_run_id) {
                            pollSync(data.sync_run_id, defaultBtnHtml);
                        } else {
                            updateBtn.disabled = false;
                            updateBtn.innerHTML = defaultBtnHtml;
                            iziToast.error({
                                message: data.message || 'Update failed',
                                position: 'topRight',
                            });
                        }
                    } catch (error) {
                        updateBtn.disabled = false;
                        updateBtn.innerHTML = defaultBtnHtml;
                        iziToast.error({
                            message: 'Request failed. Please try again.',
                            position: 'topRight',
                        });
                    }
                });
            }

            document.querySelectorAll('input[type="checkbox"][id^="toggle-"]').forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    const id = this.id.replace('toggle-', '');
                    const status = this.checked ? 1 : 0;
                    const toggleText = this.nextElementSibling.querySelector('.toggle-text');

                    toggleText.textContent = status ? 'On' : 'Off';

                    fetch('{{ route('backend.numbers.services.toggle-status') }}', {
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

            document.querySelectorAll('.edit-number-service').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const modal = document.getElementById('edit-number-service-modal');
                    modal.querySelector('input[name="name"]').value = this.getAttribute(
                        'data-name') || '';
                    modal.querySelector('input[name="code"]').value = this.getAttribute(
                        'data-code') || '';
                    modal.querySelector('select[name="country_id"]').value = this.getAttribute(
                        'data-country-id') || '';
                    modal.querySelector('input[name="provider_price"]').value = this.getAttribute(
                        'data-provider-price') || '';
                    modal.querySelector('input[name="price"]').value = this.getAttribute(
                        'data-price') || '';
                    modal.querySelector('select[name="status"]').value = this.getAttribute(
                            'data-status') ||
                        '1';
                    modal.querySelector('form').action =
                        '{{ route('backend.numbers.services.update', ['id' => '__ID__']) }}'
                        .replace('__ID__',
                            this.getAttribute('data-id'));
                    modal.classList.add('active');
                });
            });
        });
    </script>
@endpush
