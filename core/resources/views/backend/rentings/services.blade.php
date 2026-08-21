@extends('backend.layout.app')

@section('content')
    <div class="container">
        <div class="container-header">
            <h1>{{ $title }}</h1>

            <div class="container-right">
                <a href="{{ route('backend.rentings.api') }}" class="btn btn-outline"><i class="las la-arrow-left"></i>Back to API</a>
                <button type="button" class="btn btn-primary" id="update-renting-services">
                    <i class="las la-sync"></i>Update Service
                </button>
                <button class="btn btn-primary" id="add-renting-service"><i class="las la-plus"></i>Add Service</button>
            </div>
        </div>

        <div class="card table-card">
            <div class="filter-grid">
                <form method="GET" action="{{ route('backend.rentings.api.services', $api->id) }}" class="table-search-form">
                    <div class="table-search">
                        <input type="text" name="search" class="search-input"
                            placeholder="Search by name, code, country or operator..." value="{{ $search ?? '' }}">
                        <i class="las la-search"></i>
                    </div>
                    <button type="submit" class="btn btn-secondary search-btn">Search</button>
                    @if (!empty($search))
                        <a href="{{ route('backend.rentings.api.services', $api->id) }}" class="btn btn-outline clear-btn">Clear</a>
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
                            <th>Duration</th>
                            <th>Operators</th>
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
                                <td>{{ $service->rentingCountry?->name ?? '—' }}</td>
                                <td>{{ $service->dcount }} {{ $service->dtype }}</td>
                                <td>{{ $service->service_operators_count }}</td>
                                <td>
                                    <div class="toggle-switch">
                                        <input type="checkbox" id="toggle-service-{{ $service->id }}"
                                            {{ $service->status == 1 ? 'checked' : '' }}>
                                        <label for="toggle-service-{{ $service->id }}" class="toggle-label">
                                            <span class="toggle-text">{{ $service->status == 1 ? 'On' : 'Off' }}</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('backend.rentings.services.service-operators', $service->id) }}"
                                            class="table-btn" title="View operators">
                                            <i class="las la-list"></i>
                                        </a>
                                        <button type="button" class="table-btn edit-renting-service"
                                            data-id="{{ $service->id }}"
                                            data-name="{{ $service->name }}"
                                            data-code="{{ $service->code }}"
                                            data-country-id="{{ $service->country_id }}"
                                            data-dtype="{{ $service->dtype }}"
                                            data-dcount="{{ $service->dcount }}"
                                            data-status="{{ $service->status }}"
                                            title="Edit service">
                                            <i class="las la-pen"></i>
                                        </button>
                                        <form action="{{ route('backend.rentings.services.destroy', $service->id) }}"
                                            method="post" class="d-inline" data-confirm="Delete this service and all its operators?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="table-btn" title="Delete service"><i class="las la-trash"></i></button>
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

    <div class="modal" id="add-renting-service-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add Service</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>

            <form action="{{ route('backend.rentings.api.services.store', $api->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. WhatsApp" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Code<span class="required">*</span></label>
                        <input type="text" class="form-control" name="code" placeholder="e.g. opt6" required>
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
                        <label class="form-label">Duration type<span class="required">*</span></label>
                        <select class="form-control" name="dtype" required>
                            <option value="week" selected>Week</option>
                            <option value="month">Month</option>
                            <option value="day">Day</option>
                        </select>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Duration count<span class="required">*</span></label>
                        <input type="number" class="form-control" name="dcount" value="1" min="1" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Status<span class="required">*</span></label>
                        <select class="form-control" name="status" required>
                            <option value="1" selected>On</option>
                            <option value="0">Off</option>
                        </select>
                    </div>
                    <p class="text-muted" style="font-size: 13px;">Operators and prices are added via API import or the operators view.</p>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit"><i class="las la-save"></i>Save Service</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="edit-renting-service-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Service</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.rentings.services.update', ['id' => '__ID__']) }}" method="POST">
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
                        <label class="form-label">Duration type<span class="required">*</span></label>
                        <select class="form-control" name="dtype" required>
                            <option value="week">Week</option>
                            <option value="month">Month</option>
                            <option value="day">Day</option>
                        </select>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Duration count<span class="required">*</span></label>
                        <input type="number" class="form-control" name="dcount" min="1" required>
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

    <div class="modal" id="import-renting-services-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Import Services</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-25">
                    <label class="form-label">Duration type</label>
                    <select class="form-control" id="import-dtype">
                        <option value="week" selected>Week</option>
                        <option value="month">Month</option>
                        <option value="day">Day</option>
                    </select>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Duration count</label>
                    <input type="number" class="form-control" id="import-dcount" value="1" min="1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirm-import-renting-services">
                    <i class="las la-sync"></i>Start Import
                </button>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.getElementById('add-renting-service')?.addEventListener('click', function() {
            document.getElementById('add-renting-service-modal')?.classList.add('active');
        });

        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const serviceToggleUrl = '{{ route('backend.rentings.services.toggle-status') }}';

            const updateBtn = document.getElementById('update-renting-services');
            const importModal = document.getElementById('import-renting-services-modal');
            const confirmImportBtn = document.getElementById('confirm-import-renting-services');

            if (updateBtn) {
                let pollTimer = null;
                const syncStatusUrlTemplate = '{{ route('backend.rentings.sync-runs.show', ['id' => '__ID__']) }}';
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
                            message: 'Done. Imported ' + (data.imported || 0) + ', skipped ' + (data.skipped || 0) + '.',
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
                            const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                            const data = await res.json();
                            handleSyncResponse(data, originalHtml);
                        } catch (e) {
                            stopPoll();
                            updateBtn.disabled = false;
                            updateBtn.innerHTML = originalHtml;
                            iziToast.error({ message: 'Progress check failed', position: 'topRight' });
                        }
                    };

                    stopPoll();
                    updateBtn.disabled = true;
                    tick();
                    pollTimer = setInterval(tick, 2000);
                };

                const startImport = async (dtype, dcount) => {
                    if (updateBtn.disabled) {
                        return;
                    }

                    updateBtn.disabled = true;
                    updateBtn.innerHTML = '<i class="las la-spinner la-spin"></i> Queuing...';

                    try {
                        const response = await fetch('{{ route('backend.rentings.api.services.import', $api->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ dtype: dtype, dcount: dcount }),
                        });
                        const data = await response.json();

                        if (response.ok && data.success && data.sync_run_id) {
                            importModal?.classList.remove('active');
                            pollSync(data.sync_run_id, defaultBtnHtml);
                        } else {
                            updateBtn.disabled = false;
                            updateBtn.innerHTML = defaultBtnHtml;
                            iziToast.error({ message: data.message || 'Update failed', position: 'topRight' });
                        }
                    } catch (error) {
                        updateBtn.disabled = false;
                        updateBtn.innerHTML = defaultBtnHtml;
                        iziToast.error({ message: 'Request failed. Please try again.', position: 'topRight' });
                    }
                };

                @if($activeSyncRun ?? null)
                    pollSync({{ $activeSyncRun->id }}, defaultBtnHtml);
                @endif

                updateBtn.addEventListener('click', async function() {
                    if (!(await window.confirmModal('Update services from API for the selected duration?'))) {
                        return;
                    }
                    importModal?.classList.add('active');
                });

                confirmImportBtn?.addEventListener('click', function() {
                    const dtype = document.getElementById('import-dtype')?.value || 'week';
                    const dcount = parseInt(document.getElementById('import-dcount')?.value || '1', 10);
                    startImport(dtype, dcount);
                });
            }

            document.querySelectorAll('input[type="checkbox"][id^="toggle-service-"]').forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    const id = this.id.replace('toggle-service-', '');
                    const status = this.checked ? 1 : 0;
                    const toggleText = this.nextElementSibling.querySelector('.toggle-text');

                    toggleText.textContent = status ? 'On' : 'Off';

                    fetch(serviceToggleUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ id: id, status: status }),
                    })
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (!data.success) {
                            toggle.checked = !toggle.checked;
                            toggleText.textContent = toggle.checked ? 'On' : 'Off';
                            iziToast.error({ message: data.message || 'Failed to update status', position: 'topRight' });
                        }
                    })
                    .catch(function() {
                        toggle.checked = !toggle.checked;
                        toggleText.textContent = toggle.checked ? 'On' : 'Off';
                        iziToast.error({ message: 'Failed to update status', position: 'topRight' });
                    });
                });
            });

            document.querySelectorAll('.edit-renting-service').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const modal = document.getElementById('edit-renting-service-modal');
                    modal.querySelector('input[name="name"]').value = this.getAttribute('data-name') || '';
                    modal.querySelector('input[name="code"]').value = this.getAttribute('data-code') || '';
                    modal.querySelector('select[name="country_id"]').value = this.getAttribute('data-country-id') || '';
                    modal.querySelector('select[name="dtype"]').value = this.getAttribute('data-dtype') || 'week';
                    modal.querySelector('input[name="dcount"]').value = this.getAttribute('data-dcount') || '1';
                    modal.querySelector('select[name="status"]').value = this.getAttribute('data-status') || '1';
                    modal.querySelector('form').action =
                        '{{ route('backend.rentings.services.update', ['id' => '__ID__']) }}'
                        .replace('__ID__', this.getAttribute('data-id'));
                    modal.classList.add('active');
                });
            });
        });
    </script>
@endpush
