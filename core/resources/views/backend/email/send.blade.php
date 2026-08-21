@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $pageTitle }}</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('backend.email.send.submit') }}" method="post" id="send-email-form">
                @csrf

                <div class="form-group mb-25">
                    <div class="row">
                        <div class="form-group width-50">
                            <label class="form-label">Send to<span class="required">*</span></label>
                            <select name="recipients" id="recipients-select" class="form-control">
                                <option value="all">All users</option>
                                <option value="selected">Select users</option>
                            </select>
                        </div>
                        <div class="form-group width-50" id="user-selection-box" style="display: none;">
                            <label class="form-label">Select users</label>
                            <div class="multi-select" id="user-multi-select">
                                <button type="button" class="multi-select-trigger form-control">
                                    <span class="multi-select-preview-text" data-placeholder="Select users">Select users</span>
                                    <i class="las la-angle-down multi-select-icon"></i>
                                </button>
                                <div class="multi-select-dropdown card">
                                    <div class="multi-select-search">
                                        <div class="table-search">
                                            <input type="search" class="search-input multi-select-search-input" placeholder="Search users..." aria-label="Search users">
                                            <i class="las la-search"></i>
                                        </div>
                                    </div>
                                    <div class="multi-select-list">
                                        @forelse($users as $user)
                                        <div class="multi-select-item" data-value="{{ $user->id }}" data-label="{{ $user->fname }} {{ $user->lname }} ({{ $user->email }})">
                                            <input type="checkbox" id="user-select-{{ $user->id }}" class="user-checkbox">
                                            <label for="user-select-{{ $user->id }}">{{ $user->fname }} {{ $user->lname }} <span class="text-muted">({{ $user->email }})</span></label>
                                        </div>
                                        @empty
                                        <div class="multi-select-empty text-muted">No users found.</div>
                                        @endforelse
                                    </div>
                                    <div class="multi-select-actions">
                                        <button type="button" class="btn btn-outline clear-btn">Clear All</button>
                                        <button type="button" class="btn btn-primary apply-btn">Apply</button>
                                    </div>
                                </div>
                                <div id="user-ids-container" aria-hidden="true"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-25">
                    <label class="form-label">Subject<span class="required">*</span></label>
                    <input type="text" class="form-control" name="subject" placeholder="Enter email subject" value="{{ old('subject') }}" required>
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Message<span class="required">*</span></label>
                    <textarea class="textarea" name="message" placeholder="Enter message" rows="6" required>{{ old('message') }}</textarea>
                </div>
                <div class="form-group mb-25">
                    <small class="text-muted">Available variables: {username}, {email}, {fname}, {lname}</small>
                </div>

                <button type="submit" class="btn btn-primary">Send email</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
.multi-select { position: relative; width: 100%; }
.multi-select-trigger { display: flex; align-items: center; justify-content: space-between; cursor: pointer; text-align: left; min-height: 45px; }
.multi-select-preview-text { flex: 1; font-size: 14px; color: inherit; }
.multi-select-icon { font-size: 14px; transition: transform 0.2s ease; flex-shrink: 0; margin-left: 8px; }
.multi-select-dropdown { position: absolute; top: 100%; left: 0; right: 0; margin-top: 8px; padding: 15px; z-index: 20; opacity: 0; visibility: hidden; transform: translateY(-8px); transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s; box-shadow: 0 4px 16px rgba(0,0,0,.12); border-radius: 8px; }
.multi-select-dropdown.show { opacity: 1; visibility: visible; transform: translateY(0); }
.multi-select.is-open .multi-select-icon { transform: rotate(180deg); }
.multi-select-search { margin-bottom: 12px; }
.multi-select-search .table-search { width: 100%; }
.multi-select-list { max-height: 240px; overflow-y: auto; margin-bottom: 12px; display: flex; flex-direction: column; gap: 2px; }
.multi-select-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; cursor: pointer; border-radius: 6px; transition: background-color 0.15s ease; font-size: 14px; }
.multi-select-item:hover { background-color: rgba(0,0,0,.04); }
.multi-select-item input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary-color); flex-shrink: 0; }
.multi-select-item label { cursor: pointer; flex: 1; margin: 0; font-weight: 400; user-select: none; }
.multi-select-actions { display: flex; gap: 10px; padding-top: 12px; border-top: 1px solid rgba(0,0,0,.08); }
.multi-select-actions .btn { flex: 1; }
.multi-select-empty { padding: 12px; font-size: 14px; text-align: center; }
#user-ids-container { display: none; }
</style>
@endpush

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('send-email-form');
    const recipientsSelect = document.getElementById('recipients-select');
    const userSelectionBox = document.getElementById('user-selection-box');
    const multiSelect = document.getElementById('user-multi-select');
    if (!multiSelect) return;

    const trigger = multiSelect.querySelector('.multi-select-trigger');
    const dropdown = multiSelect.querySelector('.multi-select-dropdown');
    const previewText = multiSelect.querySelector('.multi-select-preview-text');
    const searchInput = multiSelect.querySelector('.multi-select-search-input');
    const items = multiSelect.querySelectorAll('.multi-select-item');
    const checkboxes = multiSelect.querySelectorAll('.multi-select-item .user-checkbox');
    const clearBtn = multiSelect.querySelector('.clear-btn');
    const applyBtn = multiSelect.querySelector('.apply-btn');
    const container = document.getElementById('user-ids-container');
    const placeholder = previewText ? (previewText.getAttribute('data-placeholder') || 'Select users') : 'Select users';

    function updatePreview() {
        if (!previewText) return;
        const checked = multiSelect.querySelectorAll('.multi-select-item .user-checkbox:checked');
        if (checked.length === 0) {
            previewText.textContent = placeholder;
        } else if (checked.length === 1) {
            const item = checked[0].closest('.multi-select-item');
            previewText.textContent = item ? item.getAttribute('data-label') : placeholder;
        } else {
            previewText.textContent = checked.length + ' users selected';
        }
    }

    function updateUserIdsContainer() {
        if (!container) return;
        const checked = multiSelect.querySelectorAll('.multi-select-item .user-checkbox:checked');
        container.innerHTML = '';
        checked.forEach(function(cb) {
            const item = cb.closest('.multi-select-item');
            if (item && item.dataset.value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = item.dataset.value;
                container.appendChild(input);
            }
        });
    }

    function syncFromCheckboxes() {
        updatePreview();
        updateUserIdsContainer();
    }

    if (trigger && dropdown) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            multiSelect.classList.toggle('is-open');
            dropdown.classList.toggle('show');
        });
    }

    document.addEventListener('click', function(e) {
        if (multiSelect && !multiSelect.contains(e.target)) {
            multiSelect.classList.remove('is-open');
            if (dropdown) dropdown.classList.remove('show');
        }
    });

    if (items.length) {
        items.forEach(function(item) {
            const cb = item.querySelector('.user-checkbox');
            const label = item.querySelector('label');
            if (label && cb) {
                label.addEventListener('click', function(e) {
                    e.preventDefault();
                    cb.checked = !cb.checked;
                    syncFromCheckboxes();
                });
            }
            if (cb) {
                cb.addEventListener('change', syncFromCheckboxes);
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            items.forEach(function(item) {
                const label = item.querySelector('label');
                const text = label ? label.textContent.toLowerCase() : '';
                const match = !q || text.indexOf(q) !== -1;
                item.style.display = match ? 'flex' : 'none';
            });
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            checkboxes.forEach(function(cb) { cb.checked = false; });
            syncFromCheckboxes();
        });
    }

    if (applyBtn && dropdown) {
        applyBtn.addEventListener('click', function() {
            multiSelect.classList.remove('is-open');
            dropdown.classList.remove('show');
        });
    }

    if (recipientsSelect && userSelectionBox) {
        function toggleUserList() {
            const isSelected = recipientsSelect.value === 'selected';
            userSelectionBox.style.display = isSelected ? 'block' : 'none';
            if (!isSelected && container) {
                container.innerHTML = '';
                checkboxes.forEach(function(cb) { cb.checked = false; });
                updatePreview();
            }
        }
        recipientsSelect.addEventListener('change', toggleUserList);
        toggleUserList();
    }

    if (form && recipientsSelect) {
        form.addEventListener('submit', function(e) {
            if (recipientsSelect.value === 'selected') {
                const count = container ? container.querySelectorAll('input[name="user_ids[]"]').length : 0;
                if (count === 0) {
                    e.preventDefault();
                    if (typeof iziToast !== 'undefined') {
                        iziToast.error({ message: 'Please select at least one user.', position: 'topRight' });
                    } else {
                        alert('Please select at least one user.');
                    }
                    return false;
                }
            }
        });
    }

    syncFromCheckboxes();
});
</script>
@endpush
