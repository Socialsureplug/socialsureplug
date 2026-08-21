@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $pageTitle }}</h1>
        <a href="{{ route('backend.email.user-template') }}" class="btn btn-primary"><i class="las la-arrow-left"></i>Back</a>
    </div>

    <div class="card p-20 mb-25">
        <h2 class="card-title mb-25">Variables</h2>
        <div class="list-group">
            @forelse($userTemplate->meaning as $key => $value)
            <div class="list-group-item">
                <span>{{ '{' . $key . '}' }}</span>
                <span>{{ $value }}</span>
            </div>
            @empty
            <div class="list-group-item">
                <span class="text-muted">No variables available</span>
            </div>
            @endforelse
        </div>
    </div>

    <div class="card p-20">
        <form action="{{ route('backend.email.user-template.update', $userTemplate->id) }}" method="post">
            @csrf
            @method('PUT')
            <div class="form-group mb-25">
                <label class="form-label">Subject<span class="required">*</span></label>
                <input type="text" class="form-control" name="subject" placeholder="Enter property name" value="{{ $userTemplate->subject }}">
            </div>
            <div class="form-group mb-25">
                <label class="form-label">Template<span class="required">*</span></label>
                <textarea class="textarea" name="template" placeholder="Enter property name">{{ $userTemplate->template }}</textarea>
            </div>
            <button class="btn btn-primary"><i class="las la-save"></i>Update template</button>
        </form>
    </div>
</div>
@endsection