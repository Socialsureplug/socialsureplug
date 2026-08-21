@extends('backend.layout.app')

@section('content')
    <div class="container">
        <div class="container-header">
            <h1>{{ $title }}</h1>

            <div class="container-right">
                <a href="{{ route('backend.rentings.api.services', $api->id) }}" class="btn btn-outline">
                    <i class="las la-arrow-left"></i>Back to Services
                </a>
            </div>
        </div>

        <div class="card table-card">
            <div class="filter-grid">
                <form method="GET" action="{{ route('backend.rentings.services.service-operators', $service->id) }}" class="table-search-form">
                    <div class="table-search">
                        <input type="text" name="search" class="search-input"
                            placeholder="Search by operator name..." value="{{ $search ?? '' }}">
                        <i class="las la-search"></i>
                    </div>
                    <button type="submit" class="btn btn-secondary search-btn">Search</button>
                    @if (!empty($search))
                        <a href="{{ route('backend.rentings.services.service-operators', $service->id) }}" class="btn btn-outline clear-btn">Clear</a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Operator</th>
                            <th>Stock</th>
                            <th>Provider Price</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($offers as $offer)
                            <tr>
                                <td>{{ $offers->firstItem() + $loop->index }}</td>
                                <td>{{ $offer->rentingOperator?->name ?? '—' }}</td>
                                <td>{{ number_format($offer->stock) }}</td>
                                <td>{{ number_format($offer->provider_price, 2) }}</td>
                                <td>{{ number_format($offer->price, 2) }}</td>
                                <td>
                                    <div class="toggle-switch">
                                        <input type="checkbox" id="toggle-{{ $offer->id }}"
                                            {{ $offer->status == 1 ? 'checked' : '' }}>
                                        <label for="toggle-{{ $offer->id }}" class="toggle-label">
                                            <span class="toggle-text">{{ $offer->status == 1 ? 'On' : 'Off' }}</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="table-btn edit-service-operator"
                                            data-id="{{ $offer->id }}"
                                            data-operator="{{ $offer->rentingOperator?->name }}"
                                            data-stock="{{ $offer->stock }}"
                                            data-provider-price="{{ $offer->provider_price }}"
                                            data-price="{{ $offer->price }}"
                                            data-status="{{ $offer->status }}">
                                            <i class="las la-pen"></i>
                                        </button>
                                        <form action="{{ route('backend.rentings.service-operators.destroy', $offer->id) }}"
                                            method="post" class="d-inline" data-confirm="Remove this operator from the service?">
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
            {{ $offers->links('vendor.pagination.default') }}
        </div>
    </div>

    <div class="modal" id="edit-service-operator-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Operator Pricing</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.rentings.service-operators.update', ['id' => '__ID__']) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Operator</label>
                        <input type="text" class="form-control" id="edit-operator-name" readonly>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Stock</label>
                        <input type="number" class="form-control" name="stock" id="edit-operator-stock" min="0">
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Provider price<span class="required">*</span></label>
                        <input type="number" class="form-control" name="provider_price" id="edit-operator-provider-price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Price<span class="required">*</span></label>
                        <input type="number" class="form-control" name="price" id="edit-operator-price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Status<span class="required">*</span></label>
                        <select class="form-control" name="status" id="edit-operator-status" required>
                            <option value="1">On</option>
                            <option value="0">Off</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit"><i class="las la-save"></i>Update</button>
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

                    fetch('{{ route('backend.rentings.service-operators.toggle-status') }}', {
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

            document.querySelectorAll('.edit-service-operator').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const modal = document.getElementById('edit-service-operator-modal');
                    document.getElementById('edit-operator-name').value = this.getAttribute('data-operator') || '';
                    document.getElementById('edit-operator-stock').value = this.getAttribute('data-stock') || '0';
                    document.getElementById('edit-operator-provider-price').value = this.getAttribute('data-provider-price') || '';
                    document.getElementById('edit-operator-price').value = this.getAttribute('data-price') || '';
                    document.getElementById('edit-operator-status').value = this.getAttribute('data-status') || '1';
                    modal.querySelector('form').action =
                        '{{ route('backend.rentings.service-operators.update', ['id' => '__ID__']) }}'
                        .replace('__ID__', this.getAttribute('data-id'));
                    modal.classList.add('active');
                });
            });
        });
    </script>
@endpush
