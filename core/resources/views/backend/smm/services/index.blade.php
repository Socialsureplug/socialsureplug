@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card mb-25">
        <div class="card-body">
            <form action="{{ route('backend.smm-services.store') }}" method="post">
                @csrf
                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Service name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Instagram Followers" required>
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">Category<span class="required">*</span></label>
                        <select class="form-control" name="smm_category_id" id="add-service-smm-category-id" required>
                            <option value="">Select category</option>
                            @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">Subcategory<span class="required">*</span></label>
                        <select class="form-control" name="smm_subcategory_id" id="add-service-smm-subcategory-id" required>
                            <option value="">Select category first</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Min quantity<span class="required">*</span></label>
                        <input type="number" class="form-control" name="min" value="1" min="0" required>
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">Max quantity<span class="required">*</span></label>
                        <input type="number" class="form-control" name="max" value="10000" min="1" required>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Price per 1000<span class="required">*</span></label>
                        <input type="number" step="0.0001" class="form-control" name="price" placeholder="0.00" required>
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">Average time (minutes)</label>
                        <input type="number" class="form-control" name="average_time_minutes" min="1" placeholder="e.g. 471">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status">
                            <option value="1">On</option>
                            <option value="0">Off</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add SMM service</button>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="table-header">
            <form method="get" action="{{ route('backend.smm-services.index') }}" class="table-search-form">
                <div class="table-search-form__group">
                    <div class="table-search table-search--narrow">
                        <input type="text" name="search" class="search-input" placeholder="Search services..." value="{{ $search ?? '' }}">
                        <i class="las la-search"></i>
                    </div>
                    <select name="category_id" class="form-control" style="max-width:180px;">
                        <option value="">All categories</option>
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <select name="subcategory_id" class="form-control" style="max-width:180px;">
                        <option value="">All subcategories</option>
                        @foreach($subcategories as $sub)
                        <option value="{{ $sub->id }}" @selected(request('subcategory_id') == $sub->id)>{{ $sub->smmCategory->name ?? '' }} — {{ $sub->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-secondary search-btn">Search</button>
                    @if(!empty($search) || request('category_id') || request('subcategory_id'))
                        <a href="{{ route('backend.smm-services.index') }}" class="btn btn-outline">Clear</a>
                    @endif
                </div>
            </form>
            <div class="table-actions">
                <button type="button" id="smm-sync-provider-prices-btn" class="btn btn-primary">
                    Refetch provider prices
                </button>
                <button type="button" id="smm-sync-total-prices-btn" class="btn btn-secondary">
                    Generate your prices
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th>Provider</th>
                        <th>Min / Max</th>
                        <th>Provider price</th>
                        <th>Your price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $svc)
                    <tr>
                        <td>{{ $services->firstItem() + $loop->index }}</td>
                        <td>{{ $svc->name }}</td>
                        <td>{{ $svc->smmCategory->name ?? '-' }}</td>
                        <td>
                            @if($svc->smmSubcategory)
                                {{ ($svc->smmCategory->name ?? '') !== '' ? $svc->smmCategory->name.' — '.$svc->smmSubcategory->name : $svc->smmSubcategory->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $svc->smmProvider->name ?? 'Manual' }}</td>
                        <td>{{ $svc->min }} / {{ $svc->max }}</td>
                        <td>{{ $svc->original_price !== null ? number_format((float) $svc->original_price, 4) : '—' }}</td>
                        <td>{{ number_format($svc->price, 4) }}</td>
                        <td>{{ $svc->status ? 'On' : 'Off' }}</td>
                        <td>
                            <div class="table-actions">
                                <button type="button" class="table-btn edit-smm-service" data-id="{{ $svc->id }}" data-name="{{ $svc->name }}" data-smm-category-id="{{ $svc->smm_category_id }}" data-smm-subcategory-id="{{ $svc->smm_subcategory_id }}" data-min="{{ $svc->min }}" data-max="{{ $svc->max }}" data-average-time-minutes="{{ $svc->average_time_minutes }}" data-price="{{ $svc->price }}" data-status="{{ $svc->status ? '1' : '0' }}" title="Edit"><i class="las la-pen"></i></button>
                                <form action="{{ route('backend.smm-services.destroy', $svc->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this service?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-btn" title="Delete"><i class="las la-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center">No SMM services yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($services instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="pagination-wrapper">
            {{ $services->links('vendor.pagination.default') }}
        </div>
        @endif
    </div>
</div>

<div class="modal" id="edit-smm-service-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit SMM service</h3>
            <button class="close-modal"><i class="las la-times"></i></button>
        </div>
        <form action="{{ route('backend.smm-services.update', ['id' => '__ID__']) }}" method="post" id="edit-smm-service-form">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group mb-25">
                    <label class="form-label">Service name<span class="required">*</span></label>
                    <input type="text" class="form-control" name="name" id="edit-service-name" required>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Category<span class="required">*</span></label>
                    <select class="form-control" name="smm_category_id" id="edit-service-smm-category-id" required>
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Subcategory<span class="required">*</span></label>
                    <select class="form-control" name="smm_subcategory_id" id="edit-service-smm-subcategory-id" required>
                        <option value="">Select subcategory</option>
                    </select>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Min quantity<span class="required">*</span></label>
                    <input type="number" class="form-control" name="min" id="edit-service-min" min="0" required>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Max quantity<span class="required">*</span></label>
                    <input type="number" class="form-control" name="max" id="edit-service-max" min="1" required>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Price per 1000<span class="required">*</span></label>
                    <input type="number" step="0.0001" class="form-control" name="price" id="edit-service-price" required>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Average time (minutes)</label>
                    <input type="number" class="form-control" name="average_time_minutes" id="edit-service-average-time-minutes" min="1" placeholder="e.g. 471">
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status" id="edit-service-status">
                        <option value="1">On</option>
                        <option value="0">Off</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="submit">Update service</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
var smmSubcategoryRows = @json($smmSubcategoryRowsForJs);

function rebuildSmmSubcategorySelect(selectEl, categoryId, placeholderText, preserveId) {
    if (!selectEl) return;
    while (selectEl.options.length) {
        selectEl.remove(0);
    }
    var ph = document.createElement('option');
    ph.value = '';
    ph.textContent = placeholderText;
    selectEl.appendChild(ph);
    var cid = categoryId ? parseInt(categoryId, 10) : NaN;
    if (!categoryId || Number.isNaN(cid)) {
        selectEl.value = '';
        return;
    }
    smmSubcategoryRows.forEach(function(row) {
        if (row.cid !== cid) return;
        var o = document.createElement('option');
        o.value = String(row.id);
        o.textContent = row.label;
        selectEl.appendChild(o);
    });
    var pv = preserveId != null && preserveId !== '' ? String(preserveId) : '';
    if (pv && Array.from(selectEl.options).some(function(o) { return o.value === pv; })) {
        selectEl.value = pv;
    } else {
        selectEl.value = '';
    }
}

function syncAddFormSubcategories() {
    var cat = document.getElementById('add-service-smm-category-id');
    var sub = document.getElementById('add-service-smm-subcategory-id');
    if (!cat || !sub) return;
    var catId = cat.value;
    rebuildSmmSubcategorySelect(sub, catId, catId ? 'Select subcategory' : 'Select category first', null);
}

function syncEditFormSubcategories(presetSubId) {
    var cat = document.getElementById('edit-service-smm-category-id');
    var sub = document.getElementById('edit-service-smm-subcategory-id');
    if (!cat || !sub) return;
    rebuildSmmSubcategorySelect(sub, cat.value, 'Select subcategory', presetSubId);
}

document.getElementById('add-service-smm-category-id')?.addEventListener('change', syncAddFormSubcategories);
syncAddFormSubcategories();

document.getElementById('edit-service-smm-category-id')?.addEventListener('change', function() {
    syncEditFormSubcategories(null);
});

document.querySelectorAll('.edit-smm-service').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var modal = document.getElementById('edit-smm-service-modal');
        var form = document.getElementById('edit-smm-service-form');
        form.action = '{{ route("backend.smm-services.update", ["id" => "__ID__"]) }}'.replace('__ID__', this.getAttribute('data-id'));
        document.getElementById('edit-service-name').value = this.getAttribute('data-name') || '';
        document.getElementById('edit-service-smm-category-id').value = this.getAttribute('data-smm-category-id') || '';
        document.getElementById('edit-service-min').value = this.getAttribute('data-min') || '1';
        document.getElementById('edit-service-max').value = this.getAttribute('data-max') || '10000';
        document.getElementById('edit-service-price').value = this.getAttribute('data-price') || '';
        document.getElementById('edit-service-average-time-minutes').value = this.getAttribute('data-average-time-minutes') || '';
        document.getElementById('edit-service-status').value = this.getAttribute('data-status') ?? '1';
        syncEditFormSubcategories(this.getAttribute('data-smm-subcategory-id') || '');
        modal.classList.add('active');
    });
});
</script>

<script>
(function() {
    const syncBtn = document.getElementById('smm-sync-provider-prices-btn');
    if (!syncBtn) return;

    const syncUrl = '{{ route('backend.smm-services.sync-provider-prices') }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const batchSize = 5;

    function wait(ms) {
        return new Promise(function(resolve) {
            setTimeout(resolve, ms);
        });
    }

    async function postChunk(offset) {
        const res = await fetch(syncUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                offset: offset,
                batch_size: batchSize
            })
        });

        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    }

    syncBtn.addEventListener('click', async function() {
        const defaultLabel = syncBtn.innerHTML;
        syncBtn.disabled = true;
        let offset = 0,
            total = 0,
            processed = 0;
        let updatedTotal = 0,
            nulledTotal = 0,
            errorsTotal = 0;

        try {
            while (true) {
                syncBtn.innerHTML = processed > 0 ?
                    'Syncing... ' + processed + '/' + (total || '?') :
                    'Syncing...';

                const data = await postChunk(offset);
                if (data.ok === false) throw new Error(data.message || 'Sync rejected');

                total = data.total || total;
                processed = data.processed_total || processed;
                updatedTotal += data.updated || 0;
                nulledTotal += data.nulled || 0;
                errorsTotal += data.errors || 0;

                if (!data.has_more) break;
                offset = data.next_offset || (offset + batchSize);
                await wait(3000);
            }

            if (typeof iziToast !== 'undefined') {
                iziToast.success({
                    title: 'Done',
                    message: 'Updated: ' + updatedTotal + ', Null: ' + nulledTotal + ', Errors: ' + errorsTotal,
                    position: 'topRight'
                });
            }
            setTimeout(function() {
                window.location.reload();
            }, 700);
        } catch (err) {
            syncBtn.disabled = false;
            syncBtn.innerHTML = defaultLabel;
            if (typeof iziToast !== 'undefined') {
                iziToast.error({
                    title: 'Sync failed',
                    message: 'Could not complete provider price sync. Retry again.',
                    position: 'topRight'
                });
            } else {
                alert('Sync failed');
            }
        }
    });
})();
</script>

<script>
(function() {
    const totalBtn = document.getElementById('smm-sync-total-prices-btn');
    if (!totalBtn) return;

    const totalUrl = '{{ route('backend.smm-services.sync-total-prices') }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const batchSize = 100;

    function wait(ms) {
        return new Promise(function(resolve) {
            setTimeout(resolve, ms);
        });
    }

    async function postChunk(offset) {
        const res = await fetch(totalUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                offset: offset,
                batch_size: batchSize
            })
        });

        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    }

    totalBtn.addEventListener('click', async function() {
        const defaultLabel = totalBtn.innerHTML;
        totalBtn.disabled = true;

        let offset = 0;
        let total = 0;
        let processed = 0;
        let updatedTotal = 0;
        let nulledTotal = 0;
        let currencyRate = 0;
        let priceIncrease = 0;

        try {
            while (true) {
                totalBtn.innerHTML = processed > 0 ?
                    'Generating... ' + processed + '/' + (total || '?') :
                    'Generating...';

                const data = await postChunk(offset);
                if (data.ok === false) throw new Error(data.message || 'Rejected');

                total = data.total || total;
                processed = data.processed_total || processed;
                updatedTotal += data.updated || 0;
                nulledTotal += data.nulled || 0;
                currencyRate = data.currency_rate ?? currencyRate;
                priceIncrease = data.price_increase ?? priceIncrease;

                if (!data.has_more) break;
                offset = data.next_offset || (offset + batchSize);
                await wait(80);
            }

            if (typeof iziToast !== 'undefined') {
                iziToast.success({
                    title: 'Done',
                    message: 'Your prices updated: ' + updatedTotal + ', Null: ' + nulledTotal +
                        ' (Rate: ' + currencyRate + ', Increase %: ' + priceIncrease + ')',
                    position: 'topRight'
                });
            }
            setTimeout(function() {
                window.location.reload();
            }, 700);
        } catch (err) {
            totalBtn.disabled = false;
            totalBtn.innerHTML = defaultLabel;

            const msg = err && err.message ? err.message : 'Could not generate your prices.';
            if (typeof iziToast !== 'undefined') {
                iziToast.error({
                    title: 'Failed',
                    message: msg,
                    position: 'topRight'
                });
            } else {
                alert(msg);
            }
        }
    });
})();
</script>
@endpush
