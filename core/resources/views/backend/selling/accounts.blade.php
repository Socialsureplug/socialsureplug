@extends('backend.layout.app')

@section('content')
    <div class="container">
        <div class="container-header">
            <h1 class="subtitle">{{ $title }}</h1>
            <div class="container-right">
                <button class="btn btn-primary" id="import-selling-account">Import Accounts</button>
                <button type="button" class="btn btn-primary" id="add-selling-account">Add account</button>
                <a href="{{ route('backend.selling.products.edit', $product->id) }}" class="btn btn-primary">Back to
                    product</a>
            </div>
        </div>

        <div class="card table-card">
            <div class="table-header">
                <form method="GET" action="{{ route('backend.selling.products.accounts', $product->id) }}"
                    class="table-search-form">
                    <div class="table-search">
                        <input type="text" name="search" class="search-input" placeholder="Search by name..."
                            value="{{ $search ?? '' }}">
                        <i class="las la-search"></i>
                    </div>
                    <button type="submit" class="btn btn-secondary search-btn">Search</button>
                    @if (!empty($search))
                        <a href="{{ route('backend.selling.products.accounts', $product->id) }}"
                            class="btn btn-outline">Clear</a>
                    @endif
                </form>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Sold</th>
                            <th>Credentials</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td>{{ $accounts->firstItem() + $loop->index }}</td>
                                <td>{{ $account->name }}</td>
                                <td>{{ $account->is_sold ? 'Yes' : 'No' }}</td>
                                <td><code
                                        style="word-break:break-all;">{{ \Illuminate\Support\Str::limit($account->credentials, 80) }}</code>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="table-btn edit-selling-account" title="Edit"
                                            data-id="{{ $account->id }}" data-name="{{ $account->name }}"
                                            data-credentials="{{ e($account->credentials) }}"
                                            data-is-sold="{{ $account->is_sold ? '1' : '0' }}">
                                            <i class="las la-pen"></i>
                                        </button>
                                        <form
                                            action="{{ route('backend.selling.products.accounts.destroy', [$product->id, $account->id]) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Delete this account?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="table-btn" title="Delete">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No accounts for this product yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $accounts->links('vendor.pagination.default') }}
        </div>
    </div>

    <div class="modal" id="add-selling-account-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add account</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form action="{{ route('backend.selling.products.accounts.store', $product->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Credentials<span class="required">*</span></label>
                        <textarea class="textarea" name="credentials" rows="4" required>{{ old('credentials') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sold status</label>
                        <select name="is_sold" class="form-control">
                            <option value="0" @selected((string) old('is_sold', '0') === '0')>Not sold</option>
                            <option value="1" @selected((string) old('is_sold', '0') === '1')>Sold</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="edit-selling-account-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit account</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>
            <form id="edit-selling-account-form" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label class="form-label">Name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="name" id="edit-account-name" required>
                    </div>
                    <div class="form-group mb-25">
                        <label class="form-label">Credentials<span class="required">*</span></label>
                        <textarea class="textarea" name="credentials" id="edit-account-credentials" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sold status</label>
                        <select name="is_sold" class="form-control" id="edit-account-is-sold">
                            <option value="0">Not sold</option>
                            <option value="1">Sold</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="import-selling-account-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Import account</h3>
                <button type="button" class="close-modal"><i class="las la-times"></i></button>
            </div>

            <form action="{{ route('backend.selling.products.accounts.import', $product->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div class="modal-body">
                    <div class="upload-accounts-wrapper">
                        <input type="file" name="file" accept=".txt,text/plain" id="selling-accounts-file-input"
                            hidden>

                        <div class="upload-accounts-placeholder" id="selling-accounts-placeholder">
                            <div class="icon">
                                <i class="las la-cloud-upload-alt"></i>
                            </div>
                            <span class="text">Upload TXT File</span>
                            <span class="format">username|password</span>
                        </div>

                        <div class="upload-accounts-selected hidden" id="selling-accounts-selected">
                            <div class="icon">
                                <i class="las la-file-alt"></i>
                            </div>
                            <span class="text" id="selling-accounts-filename"></span>
                            <button type="button" class="btn btn-primary">Click to change file</button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const wrapper = document.querySelector('.upload-accounts-wrapper');
            const input = document.getElementById('selling-accounts-file-input');
            const placeholder = document.getElementById('selling-accounts-placeholder');
            const selected = document.getElementById('selling-accounts-selected');
            const filename = document.getElementById('selling-accounts-filename');

            if (!wrapper || !input || !placeholder || !selected) return;

            wrapper.addEventListener('click', () => input.click());

            input.addEventListener('change', () => {
                const file = input.files?.[0];

                if (!file) {
                    placeholder.classList.remove('hidden');
                    selected.classList.add('hidden');
                    return;
                }

                if (!file.name.toLowerCase().endsWith('.txt')) {
                    input.value = '';
                    iziToast.error({
                        message: 'Please select a .txt file',
                        position: 'topRight'
                    });

                    return;
                }

                if (filename) filename.textContent = file.name;
                placeholder.classList.add('hidden');
                selected.classList.remove('hidden');
            })
        });

        const editForm = document.getElementById('edit-selling-account-form');
        const editModal = document.getElementById('edit-selling-account-modal');
        const updateUrlTemplate = @json(route('backend.selling.products.accounts.update', [$product->id, '__ID__']));

        document.querySelectorAll('.edit-selling-account').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                editForm.action = updateUrlTemplate.replace('__ID__', id);
                document.getElementById('edit-account-name').value = btn.dataset.name || '';
                document.getElementById('edit-account-credentials').value = btn.dataset.credentials || '';
                document.getElementById('edit-account-is-sold').value = btn.dataset.isSold || '0';
                editModal.classList.add('active');
            });
        });
    </script>
@endpush
