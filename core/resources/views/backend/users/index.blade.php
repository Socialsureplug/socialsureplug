@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $pageTitle }}</h1>
    </div>

    <div class="card table-card">
        <div class="table-header">
            <form method="GET" action="{{ route('backend.users.index') }}" class="table-search-form">
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search users..." value="{{ $search ?? '' }}">
                    <i class="las la-search"></i>
                </div>
                <button type="submit" class="btn btn-secondary search-btn">Search</button>
                @if(!empty($search))
                    <a href="{{ route('backend.users.index') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>
            <button class="btn btn-primary" id="add-user"><i class="las la-plus"></i>Add user</button>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('SL') }}</th>
                        <th>{{ __('Full name') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Country') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                    <tr>
                        <td>{{ $users->firstItem() + $loop->index }}</td>
                        <td>{{ $user->fname }} {{ $user->lname }}</td>
                        <td>{{ $user->phone }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->country ?? 'N/A' }}</td>
                        <td>
                            <div class="toggle-switch">
                                <input type="checkbox" id="toggle-{{ $user->id }}" {{ $user->status == 1 ? 'checked' : '' }}>
                                <label for="toggle-{{ $user->id }}" class="toggle-label">
                                    <span class="toggle-text">{{ $user->status == 1 ? 'On' : 'Off' }}</span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('backend.users.login-as-user', $user->id) }}" target="_blank" class="table-btn"><i class="las la-long-arrow-alt-right"></i></a> 
                                <a href="{{ route('backend.users.details', $user->id) }}" class="table-btn"><i class="las la-pen"></i></a>
                                <form action="{{ route('backend.users.destroy', $user->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-btn" title="Delete"><i class="las la-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ __('No data found') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{ $users->links('vendor.pagination.default') }}
    </div>
</div>

<div class="modal" id="add-user-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add user</h3>
            <button class="close-modal"><i class="las la-times"></i></button>
        </div>
        <form action="{{ route('backend.users.add-user') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group mb-25">
                    <label class="form-label">First Name<span class="required">*</span></label>
                    <input type="text" class="form-control" name="fname" placeholder="Enter first name">
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Last Name<span class="required">*</span></label>
                    <input type="text" class="form-control" name="lname" placeholder="Enter last name">
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" name="phone" placeholder="Enter phone number">
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Email<span class="required">*</span></label>
                    <input type="text" class="form-control" name="email" placeholder="Enter email">
                </div>
                <div class="form-group mb-25">
                    <label class="form-label">Password<span class="required">*</span></label>
                    <input type="password" class="form-control" name="password" placeholder="Enter password">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="submit">Add user</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleSwitches = document.querySelectorAll('input[type="checkbox"][id^="toggle-"]');

            toggleSwitches.forEach(toggle =>  {
                toggle.addEventListener('change', function() {
                    const userId = this.id.replace('toggle-', '');
                    const status = this.checked ? 1 : 0;
                    const toggleText = this.nextElementSibling.querySelector('.toggle-text');

                    toggleText.textContent = status ? 'On' : 'Off';

                    fetch('{{ route('backend.users.toggle-status') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            id: userId,
                            status: status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            iziToast.success({
                                message: 'Status updated successfully',
                                position: 'topRight'
                            })
                        } else {
                            this.checked = !this.checked;
                            toggleText.textContent = this.checked ? 'On' : 'Off';
                            iziToast.error({
                                message: 'Failed to update status',
                                position: 'topRight'
                            })
                        }
                    })
                    .catch(error =>{
                        console.error('Error:', error);
                        this.checked = !this.checked;
                        toggleText.textContent = this.checked ? 'On' : 'Off';
                        iziToast.error({
                            message: 'Failed to update status',
                            position: 'topRight'
                        })
                    } )
                })
            })
        })
    </script>
@endpush