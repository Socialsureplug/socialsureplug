@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card">

        <div class="card-body">
            <form action="{{ route('backend.email.config.update') }}" method="post" class="mb-25">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">SMTP host<span class="required">*</span></label>
                        <input type="text" class="form-control" name="smtp_host" placeholder="Enter SMTP host" value="{{ $emailConfig->smtp_host }}">
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">SMTP port<span class="required">*</span></label>
                        <input type="text" class="form-control" name="smtp_port" placeholder="Enter SMTP port" value="{{ $emailConfig->smtp_port }}">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">SMTP username<span class="required">*</span></label>
                        <input type="text" class="form-control" name="smtp_username" placeholder="Enter SMTP username" value="{{ $emailConfig->smtp_username }}">
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">SMTP password<span class="required">*</span></label>
                        <div class="form-input-group">
                            <input type="password" class="form-control" id="password" name="smtp_password" placeholder="Enter SMTP password" value="{{ $emailConfig->smtp_password }}">
                            <i class="las la-eye" id="password-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Sent from<span class="required">*</span></label>
                        <input type="text" class="form-control" name="sent_from" placeholder="Enter Sent from" value="{{ $emailConfig->sent_from }}">
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">SMTP encryption<span class="required">*</span></label>
                        <select name="smtp_encryption" id="" class="form-control">
                            <option value="0" {{ $emailConfig->smtp_encryption == 0 ? 'selected' : '' }}>TLS</option>
                            <option value="1" {{ $emailConfig->smtp_encryption == 1 ? 'selected' : '' }}>SSL</option>
                            <option value="2" {{ $emailConfig->smtp_encryption == 2 ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label class="form-label">Status<span class="required">*</span></label>
                        <div class="toggle-switch">
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox" id="toggle" name="status" value="1" {{ $emailConfig->status == 1 ? 'checked' : '' }}>
                            <label for="toggle" class="toggle-label">
                                <span class="toggle-text">On</span>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update config</button>
            </form>
        </div>
    </div>

</div>
@endsection