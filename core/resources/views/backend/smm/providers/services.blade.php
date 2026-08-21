@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
        <a href="{{ route('backend.smm-providers.index') }}" class="btn btn-outline">Back to providers</a>
    </div>

    <div class="card mb-25">
        <div class="card-body">
            <form action="{{ route('backend.smm-providers.import-services') }}" method="post" id="import-services-form">
                @csrf
                <input type="hidden" name="api_id" value="{{ $provider->id }}">
                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Category<span class="required">*</span></label>
                        <select class="form-control" name="cate_id" required>
                            <option value="">Select category</option>
                            @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">Subcategory<span class="required">*</span></label>
                        <select class="form-control" name="sub_cate_id" id="import-sub-cate-id" required>
                            <option value="">Select category first</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Price increase %<span class="required">*</span></label>
                        <input type="number" class="form-control" name="price_percentage_increase" value="30" min="0" required>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group width-100">
                        <label class="form-label">Select services to import</label>
                        <div class="table-header smm-services">
                            <div class="table-search-form">
                                <div class="table-search-form__group">
                                    <div class="table-search table-search--narrow">
                                        <input type="text" id="services-search" class="search-input" placeholder="Search services..." value="{{ $search ?? '' }}">
                                        <i class="las la-search"></i>
                                    </div>
                                    <button type="button" class="btn btn-secondary search-btn" id="services-search-btn">Search</button>
                                    @if(!empty($search))
                                        <a href="{{ route('backend.smm-providers.services', $provider->id) }}" class="btn btn-outline">Clear</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="services-selection-toolbar">
                            <p class="text-muted services-selection-count">Selected across pages: <span id="selected-services-count">0</span></p>
                            <button type="button" class="btn btn-outline" id="clear-selected-services">Clear selected</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all-services" class="form-control"></th>
                                        <th>#</th>
                                        <th>Service ID</th>
                                        <th>Name</th>
                                        <th>Rate</th>
                                        <th>Min / Max</th>
                                    </tr>
                                </thead>
                                <tbody id="services-table-body">
                                    @php $pageItems = $services->items(); @endphp
                                    @forelse($pageItems as $svc)
                                    <tr>
                                        <td><input type="checkbox" value="{{ $svc['sid'] }}" class="service-checkbox"></td>
                                        <td>{{ $services->firstItem() + $loop->index }}</td>
                                        <td>{{ $svc['sid'] }}</td>
                                        <td>{{ $svc['name'] }}</td>
                                        <td>{{ $svc['rate'] }}</td>
                                        <td>{{ $svc['min'] }} / {{ $svc['max'] }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center">No services returned from API or API could not be reached.</td></tr>
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
                @if($services->total() > 0)
                <button type="submit" class="btn btn-primary">Import selected services</button>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
var smmImportSubcategoryRows = @json($smmImportSubcategoryRowsForJs);

function rebuildSmmImportSubcategorySelect(selectEl, categoryId) {
    if (!selectEl) return;
    while (selectEl.options.length) {
        selectEl.remove(0);
    }
    var ph = document.createElement('option');
    ph.value = '';
    ph.textContent = categoryId ? 'Select subcategory' : 'Select category first';
    selectEl.appendChild(ph);
    var cid = categoryId ? parseInt(categoryId, 10) : NaN;
    if (!categoryId || Number.isNaN(cid)) {
        selectEl.value = '';
        return;
    }
    smmImportSubcategoryRows.forEach(function(row) {
        if (row.cid !== cid) return;
        var o = document.createElement('option');
        o.value = String(row.id);
        o.textContent = row.label;
        selectEl.appendChild(o);
    });
    selectEl.value = '';
}

document.addEventListener('DOMContentLoaded', function() {
    var importCat = document.querySelector('#import-services-form select[name="cate_id"]');
    var importSub = document.getElementById('import-sub-cate-id');
    if (importCat && importSub) {
        function syncImportSubcats() {
            rebuildSmmImportSubcategorySelect(importSub, importCat.value);
        }
        importCat.addEventListener('change', syncImportSubcats);
        syncImportSubcats();
    }

    var searchInput = document.getElementById('services-search');
    var searchBtn = document.getElementById('services-search-btn');
    var searchBaseUrl = '{{ route('backend.smm-providers.services', $provider->id) }}';

    function submitSearch() {
        if (!searchInput) return;
        var term = (searchInput.value || '').trim();
        if (!term) {
            window.location.href = searchBaseUrl;
            return;
        }
        window.location.href = searchBaseUrl + '?search=' + encodeURIComponent(term);
    }

    searchBtn?.addEventListener('click', submitSearch);
    searchInput?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            submitSearch();
        }
    });

    var providerId = '{{ $provider->id }}';
    var storageKey = 'smm_provider_selected_services_' + providerId;
    var form = document.getElementById('import-services-form');
    var checkboxes = Array.from(document.querySelectorAll('.service-checkbox'));
    var selectAll = document.getElementById('select-all-services');
    var countEl = document.getElementById('selected-services-count');
    var clearSelectedBtn = document.getElementById('clear-selected-services');
    var selected = new Set();

    try {
        var saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
        if (Array.isArray(saved)) {
            saved.forEach(function(id) { selected.add(String(id)); });
        }
    } catch (e) {
        selected = new Set();
    }

    function persistSelected() {
        localStorage.setItem(storageKey, JSON.stringify(Array.from(selected)));
        if (countEl) {
            countEl.textContent = String(selected.size);
        }
    }

    function syncPageCheckboxes() {
        checkboxes.forEach(function(cb) {
            cb.checked = selected.has(String(cb.value));
        });
        if (selectAll && checkboxes.length) {
            selectAll.checked = checkboxes.every(function(cb) { return cb.checked; });
        }
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            var id = String(this.value);
            if (this.checked) {
                selected.add(id);
            } else {
                selected.delete(id);
            }
            persistSelected();
            syncPageCheckboxes();
        });
    });

    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(function(cb) {
            cb.checked = selectAll.checked;
            var id = String(cb.value);
            if (selectAll.checked) {
                selected.add(id);
            } else {
                selected.delete(id);
            }
        });
        persistSelected();
    });

    form?.addEventListener('submit', function() {
        form.querySelectorAll('input[name="ids[]"][data-generated="1"]').forEach(function(el) {
            el.remove();
        });
        Array.from(selected).forEach(function(id) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            input.setAttribute('data-generated', '1');
            form.appendChild(input);
        });
    });

    syncPageCheckboxes();
    persistSelected();

    clearSelectedBtn?.addEventListener('click', function() {
        selected.clear();
        localStorage.removeItem(storageKey);
        syncPageCheckboxes();
        persistSelected();
    });

    if (form && !form.dataset.selectionPersistBound) {
        form.dataset.selectionPersistBound = '1';
        window.addEventListener('pageshow', function() {
            syncPageCheckboxes();
            persistSelected();
        });
    }
});
</script>
@endpush
