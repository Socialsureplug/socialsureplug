@extends('backend.layout.app')

@section('content')
    <div class="container">
        <div class="container-header">
            <h1>{{ $title }}</h1>

            <div class="container-right">
                <button
                    type="button"
                    class="btn btn-primary"
                    id="sync-selling-descriptions"
                    data-active-sync-id="{{ $activeDescriptionSync->id ?? '' }}"
                    data-status-url-template="{{ route('backend.selling.products.sync-descriptions.status', ['syncId' => '__SYNC_ID__']) }}"
                >Update Description</button>
                <button type="button" class="btn btn-primary" id="import-selling-products">Import products</button>
                <a href="{{ route('backend.selling.products.create') }}" class="btn btn-primary">Add Product</a>
            </div>
        </div>

        <div class="card table-card">
            <div class="table-header">
                <form method="GET" action="{{ route('backend.selling.products') }}" class="table-search-form">
                    <div class="table-search-form__group">
                        <div class="table-search table-search--narrow">
                            <input type="text" name="search" class="search-input" placeholder="Search by title..."
                                value="{{ $search ?? '' }}">
                            <i class="las la-search"></i>
                        </div>
                        <select name="category_id" class="form-control table-header-select" aria-label="Filter by category" onchange="this.form.submit()">
                            <option value="">All categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected((string) ($category_id ?? '') === (string) $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-secondary search-btn">Search</button>
                        @if (!empty($search) || !empty($category_id))
                            <a href="{{ route('backend.selling.products') }}" class="btn btn-outline">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Subcategory</th>
                            <th>API</th>
                            <th>Provider Price</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $products->firstItem() + $loop->index }}</td>
                                <td>{{ $product->title }}</td>
                                <td>
                                    @if($product->sellingSubcategory)
                                        {{ $product->sellingSubcategory->sellingCategory?->name }} › {{ $product->sellingSubcategory->name }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $product->sellingApi?->name ?? '—' }}</td>
                                <td>{{ $product->provider_price }}</td>
                                <td>{{ $product->price }}</td>
                                <td>{{ $product->stock }}</td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('backend.selling.products.edit', $product->id) }}" class="table-btn"><i class="las la-pen"></i></a>
                                        <form action="{{ route('backend.selling.products.destroy', $product->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this product?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="table-btn"><i class="las la-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No products yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $products->links('vendor.pagination.default') }}
        </div>
    </div>

    <div class="modal" id="import-selling-products-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Import products</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.selling.products.import') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">API<span class="required">*</span></label>
                        <select name="api_id" class="form-control" required>
                            <option value="">Select API</option>
                            @foreach ($apis as $api)
                                <option value="{{ $api->id }}">{{ $api->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="mb-20">Subcategories are assigned automatically from each listing. Import subcategories before products. This may take several minutes — do not close the page.</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="sync-selling-descriptions-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Update descriptions</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.selling.products.sync-descriptions') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">API<span class="required">*</span></label>
                        <select name="api_id" class="form-control" required>
                            <option value="">Select AccsBulk API</option>
                            @foreach ($accsbulkApis as $api)
                                <option value="{{ $api->id }}">{{ $api->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="mb-20">This runs in queue and updates product descriptions in chunks. Keep your queue worker running.</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Start sync</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const syncBtn = document.getElementById('sync-selling-descriptions');
    const importBtn = document.getElementById('import-selling-products');
    const syncModal = document.getElementById('sync-selling-descriptions-modal');
    const importModal = document.getElementById('import-selling-products-modal');

    const openModal = (modal) => modal?.classList.add('active');
    const closeModal = (modal) => modal?.classList.remove('active');

    syncBtn?.addEventListener('click', () => openModal(syncModal));
    importBtn?.addEventListener('click', () => openModal(importModal));

    document.querySelectorAll('.close-modal').forEach((btn) => {
        btn.addEventListener('click', () => {
            closeModal(btn.closest('.modal'));
        });
    });

    document.querySelectorAll('.modal').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    if (!syncBtn) return;

    const defaultLabel = 'Update Description';
    const syncId = Number(syncBtn.dataset.activeSyncId ?? '');
    const statusUrlTemplate = syncBtn.dataset.statusUrlTemplate ?? '';

    if (!Number.isInteger(syncId) || syncId <= 0 || statusUrlTemplate === '') return;

    const statusUrl = statusUrlTemplate.replace('__SYNC_ID__', String(syncId));
    let poller = null;
    let notified = false;

    const renderProgress = (sync) => {
        const processed = Number(sync?.processed_products ?? 0);
        const total = Number(sync?.total_products ?? 0);
        const status = String(sync?.status ?? '');

        if (status === 'pending' || status === 'running') {
            syncBtn.textContent = `${defaultLabel} (${processed}/${total})`;
            syncBtn.disabled = true;
            return true;
        }

        syncBtn.textContent = defaultLabel;
        syncBtn.disabled = false;

        if (!notified && status === 'completed') {
            notified = true;
            iziToast.success({ message: 'Description sync completed.', position: 'topRight' });
        } else if (!notified && status === 'failed') {
            notified = true;
            const msg = sync?.error_message
                ? `Description sync failed: ${sync.error_message}`
                : 'Description sync failed.';
            iziToast.error({ message: msg, position: 'topRight' });
        }

        return false;
    };

    const poll = async () => {
        try {
            const response = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
            const payload = await response.json().catch(() => null);
            if (!response.ok || !payload?.success || !payload.sync) return;

            const shouldContinue = renderProgress(payload.sync);
            if (!shouldContinue && poller) {
                clearInterval(poller);
                poller = null;
            }
        } catch (_) {
            // Ignore transient polling failures.
        }
    };

    poll();
    poller = setInterval(poll, 2500);
});
</script>
@endpush
