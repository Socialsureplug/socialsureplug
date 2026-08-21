@extends('backend.layout.app')

@section('content')
    <div class="container">
        <div class="container-header">
            <h1>{{ $title }}</h1>

            <div class="container-right">
                <a href="{{ route('backend.rentings.api.countries', $api->id) }}" class="btn btn-outline">
                    <i class="las la-arrow-left"></i>Back to Countries
                </a>
            </div>
        </div>

        <div class="card table-card">
            <div class="filter-grid">
                <form method="GET" action="{{ route('backend.rentings.api.countries.operators', [$api->id, $country->id]) }}" class="table-search-form">
                    <div class="table-search">
                        <input type="text" name="search" class="search-input"
                            placeholder="Search by operator name..." value="{{ $search ?? '' }}">
                        <i class="las la-search"></i>
                    </div>
                    <button type="submit" class="btn btn-secondary search-btn">Search</button>
                    @if (!empty($search))
                        <a href="{{ route('backend.rentings.api.countries.operators', [$api->id, $country->id]) }}" class="btn btn-outline clear-btn">Clear</a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Services linked</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($operators as $operator)
                            <tr>
                                <td>{{ $operators->firstItem() + $loop->index }}</td>
                                <td>{{ $operator->name }}</td>
                                <td>{{ $operator->service_operators_count }}</td>
                                <td>
                                    <div class="toggle-switch">
                                        <input type="checkbox" id="toggle-{{ $operator->id }}"
                                            {{ $operator->status == 1 ? 'checked' : '' }}>
                                        <label for="toggle-{{ $operator->id }}" class="toggle-label">
                                            <span class="toggle-text">{{ $operator->status == 1 ? 'On' : 'Off' }}</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="table-btn edit-renting-operator"
                                            data-id="{{ $operator->id }}"
                                            data-name="{{ $operator->name }}"
                                            data-status="{{ $operator->status }}">
                                            <i class="las la-pen"></i>
                                        </button>
                                        <form action="{{ route('backend.rentings.operators.destroy', $operator->id) }}"
                                            method="post" class="d-inline" data-confirm="Delete this operator?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="table-btn"><i class="las la-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @include('backend.partial.table-empty', ['colspan' => 5])
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $operators->links('vendor.pagination.default') }}
        </div>
    </div>

    <div class="modal" id="edit-renting-operator-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Operator</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.rentings.operators.update', ['id' => '__ID__']) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" required>
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
                    <button class="btn btn-primary" type="submit"><i class="las la-save"></i>Update Operator</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[type="checkbox"][id^="toggle-"]').forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    const id = this.id.replace('toggle-', '');
                    const status = this.checked ? 1 : 0;
                    const toggleText = this.nextElementSibling.querySelector('.toggle-text');
                    toggleText.textContent = status ? 'On' : 'Off';

                    fetch('{{ route('backend.rentings.operators.toggle-status') }}', {
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
                            iziToast.error({ message: data.message || 'Failed to update status', position: 'topRight' });
                        }
                    })
                    .catch(function() {
                        toggle.checked = !toggle.checked;
                        toggleText.textContent = toggle.checked ? 'On' : 'Off';
                    });
                });
            });

            document.querySelectorAll('.edit-renting-operator').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const modal = document.getElementById('edit-renting-operator-modal');
                    modal.querySelector('input[name="name"]').value = this.getAttribute('data-name') || '';
                    modal.querySelector('select[name="status"]').value = this.getAttribute('data-status') || '1';
                    modal.querySelector('form').action =
                        '{{ route('backend.rentings.operators.update', ['id' => '__ID__']) }}'
                        .replace('__ID__', this.getAttribute('data-id'));
                    modal.classList.add('active');
                });
            });
        });
    </script>
@endpush
