@extends('backend.layout.app')

@section('content')
    <div class="container">
        <div class="container-header">
            <h1>{{ $title }}</h1>

            <div class="container-right">
                <button type="button" class="btn btn-primary" id="update-renting-balances">
                    <i class="las la-sync"></i> Update Balance
                </button>
                <button class="btn btn-primary" id="add-renting-api"><i class="las la-plus"></i>Add API</button>
            </div>
        </div>

        <div class="card table-card">
            <div class="filter-grid">
                <form method="GET" action="{{ route('backend.rentings.api') }}" class="table-search-form">
                    <div class="table-search">
                        <input type="text" name="search" class="search-input"
                            placeholder="Search by name, label or slug..." value="{{ $search ?? '' }}">
                        <i class="las la-search"></i>
                    </div>
                    <button type="submit" class="btn btn-secondary search-btn">Search</button>
                    @if (!empty($search))
                        <a href="{{ route('backend.rentings.api') }}" class="btn btn-outline clear-btn">Clear</a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Label</th>
                            <th>Slug</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($apis as $api)
                            <tr>
                                <td>{{ $apis->firstItem() + $loop->index }}</td>
                                <td>{{ $api->name }}</td>
                                <td>{{ $api->label }}</td>
                                <td>{{ $api->slug }}</td>
                                <td class="api-balance-cell" data-api-id="{{ $api->id }}">
                                    {{ filled($api->balance) ? $api->balance : '—' }}
                                </td>
                                <td>
                                    <div class="toggle-switch">
                                        <input type="checkbox" id="toggle-{{ $api->id }}"
                                            {{ $api->status == 1 ? 'checked' : '' }}>
                                        <label for="toggle-{{ $api->id }}" class="toggle-label">
                                            <span class="toggle-text">{{ $api->status == 1 ? 'On' : 'Off' }}</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('backend.rentings.api.countries', $api->id) }}" class="table-btn"
                                            title="Countries"><i class="las la-globe"></i></a>
                                        <a href="{{ route('backend.rentings.api.services', $api->id) }}" class="table-btn"
                                            title="Services"><i class="las la-list"></i></a>

                                        <button type="button" class="table-btn edit-renting-api"
                                            data-id="{{ $api->id }}" data-name="{{ $api->name }}"
                                            data-label="{{ $api->label }}" data-slug="{{ $api->slug }}"
                                            data-base-url="{{ $api->base_url }}" data-api-key="{{ $api->api_key }}"
                                            data-status="{{ $api->status }}">
                                            <i class="las la-pen"></i>
                                        </button>
                                        <form action="{{ route('backend.rentings.api.destroy', $api->id) }}" method="post"
                                            class="d-inline" data-confirm="Delete this API?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="table-btn"><i class="las la-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @include('backend.partial.table-empty', ['colspan' => 7])
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $apis->links('vendor.pagination.default') }}
        </div>
    </div>

    <div class="modal" id="add-renting-api-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add API</h3>
                <button class="close-modal"><i class="las la-times"></i></button>
            </div>

            <form action="{{ route('backend.rentings.api.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. SMSPVA" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Label<span class="required">*</span></label>
                        <input type="text" class="form-control" name="label" placeholder="e.g. SMSPVA Rental" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Slug<span class="required">*</span></label>
                        <input type="text" class="form-control" name="slug" placeholder="e.g. smspva-rent" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Base URL<span class="required">*</span></label>
                        <input type="url" class="form-control" name="base_url" value="https://smspva.com" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">API key<span class="required">*</span></label>
                        <input type="text" class="form-control" name="api_key" placeholder="API key" required>
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
                    <button class="btn btn-primary" type="submit"><i class="las la-save"></i>Save API</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="edit-renting-api-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit API</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.rentings.api.update', ['id' => '__ID__']) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Label<span class="required">*</span></label>
                        <input type="text" class="form-control" name="label" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Slug<span class="required">*</span></label>
                        <input type="text" class="form-control" name="slug" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Base URL<span class="required">*</span></label>
                        <input type="url" class="form-control" name="base_url" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">API key<span class="required">*</span></label>
                        <input type="text" class="form-control" name="api_key" required>
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
                    <button class="btn btn-primary" type="submit"><i class="las la-save"></i>Update API</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.getElementById('add-renting-api')?.addEventListener('click', function() {
            document.getElementById('add-renting-api-modal')?.classList.add('active');
        });

        document.querySelectorAll('.edit-renting-api').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const modal = document.getElementById('edit-renting-api-modal');
                modal.querySelector('input[name="name"]').value = this.getAttribute('data-name') || '';
                modal.querySelector('input[name="label"]').value = this.getAttribute('data-label') || '';
                modal.querySelector('input[name="slug"]').value = this.getAttribute('data-slug') || '';
                modal.querySelector('input[name="base_url"]').value = this.getAttribute('data-base-url') || '';
                modal.querySelector('input[name="api_key"]').value = this.getAttribute('data-api-key') || '';
                modal.querySelector('select[name="status"]').value = this.getAttribute('data-status') || '1';
                modal.querySelector('form').action =
                    '{{ route('backend.rentings.api.update', ['id' => '__ID__']) }}'.replace('__ID__', this
                        .getAttribute('data-id'));
                modal.classList.add('active');
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const updateBalancesBtn = document.getElementById('update-renting-balances');
            if (updateBalancesBtn) {
                updateBalancesBtn.addEventListener('click', async function() {
                    const cells = document.querySelectorAll('.api-balance-cell[data-api-id]');
                    if (!cells.length) {
                        if (window.iziToast) {
                            iziToast.info({ message: 'No APIs on this page.', position: 'topRight' });
                        }
                        return;
                    }

                    const apiIds = Array.from(cells).map(function(cell) {
                        return parseInt(cell.getAttribute('data-api-id'), 10);
                    }).filter(function(id) {
                        return !isNaN(id);
                    });

                    const originalHtml = updateBalancesBtn.innerHTML;
                    updateBalancesBtn.disabled = true;
                    updateBalancesBtn.innerHTML = '<i class="las la-spinner la-spin"></i> Updating...';

                    cells.forEach(function(cell) {
                        cell.textContent = '…';
                    });

                    try {
                        const response = await fetch('{{ route('backend.rentings.api.balances') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ api_ids: apiIds }),
                        });

                        const data = await response.json();

                        if (response.ok && data.success && data.balances) {
                            cells.forEach(function(cell) {
                                const id = cell.getAttribute('data-api-id');
                                cell.textContent = data.balances[id] ?? '—';
                            });
                            if (window.iziToast) {
                                iziToast.success({ message: 'Balances updated.', position: 'topRight' });
                            }
                        } else {
                            cells.forEach(function(cell) { cell.textContent = '—'; });
                            if (window.iziToast) {
                                iziToast.error({
                                    message: data.message || 'Could not update balances',
                                    position: 'topRight',
                                });
                            }
                        }
                    } catch (e) {
                        cells.forEach(function(cell) { cell.textContent = '—'; });
                        if (window.iziToast) {
                            iziToast.error({ message: 'Request failed', position: 'topRight' });
                        }
                    } finally {
                        updateBalancesBtn.disabled = false;
                        updateBalancesBtn.innerHTML = originalHtml;
                    }
                });
            }

            document.querySelectorAll('input[type="checkbox"][id^="toggle-"]').forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    const id = this.id.replace('toggle-', '');
                    const status = this.checked ? 1 : 0;
                    const toggleText = this.nextElementSibling.querySelector('.toggle-text');

                    toggleText.textContent = status ? 'On' : 'Off';

                    fetch('{{ route('backend.rentings.api.toggle-status') }}', {
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
                        if (!data.success) {
                            toggle.checked = !toggle.checked;
                            toggleText.textContent = toggle.checked ? 'On' : 'Off';
                            if (window.iziToast) {
                                iziToast.error({
                                    message: data.message || 'Failed to update status',
                                    position: 'topRight',
                                });
                            }
                        } else if (window.iziToast) {
                            iziToast.success({
                                message: data.message || 'Status updated successfully',
                                position: 'topRight',
                            });
                        }
                    })
                    .catch(function() {
                        toggle.checked = !toggle.checked;
                        toggleText.textContent = toggle.checked ? 'On' : 'Off';
                        if (window.iziToast) {
                            iziToast.error({ message: 'Failed to update status', position: 'topRight' });
                        }
                    });
                });
            });
        });
    </script>
@endpush
