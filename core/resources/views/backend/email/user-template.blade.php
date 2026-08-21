@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $pageTitle }}</h1>
    </div>

    <div class="card table-card">
        <div class="table-header">
            <div class="table-search">
                <input type="text" name="" class="search-input" placeholder="Search">
                <i class="las la-search"></i>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Sl</th>
                        <th>Name</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($userTemplates as $userTemplate)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $userTemplate->name }}</td>
                        <td>{{ $userTemplate->subject }}</td>
                        <td>
                            <div class="toggle-switch">
                                <input type="checkbox" id="toggle" checked {{ $userTemplate->status == 1 ? 'checked' : '' }}>
                                <label for="toggle" class="toggle-label">
                                    <span class="toggle-text">On</span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('backend.email.user-template.details', $userTemplate->id) }}" class="table-btn"><i class="las la-pen"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No data found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection