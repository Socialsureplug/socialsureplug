@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('backend.numbers.settings.update') }}" method="POST" class="mb-25">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="form-group width-50">
                        <label class="form-label">Currency rate<span class="required">*</span></label>
                        <input type="number" class="form-control" name="currency_rate" step="0.01" min="0"
                            placeholder="e.g. 1.00"
                            value="{{ old('currency_rate', $numberSettings->currency_rate) }}" required>
                    </div>
                    <div class="form-group width-50">
                        <label class="form-label">Price increase (%)<span class="required">*</span></label>
                        <input type="number" class="form-control" name="price_increase" step="0.01" min="0"
                            placeholder="e.g. 10.00"
                            value="{{ old('price_increase', $numberSettings->price_increase) }}" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="las la-save"></i>Update settings</button>
            </form>
        </div>
    </div>
</div>
@endsection
