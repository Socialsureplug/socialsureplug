@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="card mp-20 bank-transfer-card">
        <div class="bank-transfer-head">
            <div>
                <h2 class="title">Bank Transfer</h2>
                <p class="desc">
                    Transfer the exact amount to the bank account below and upload your payment proof.
                    Your wallet will be funded after admin approves the payment.
                </p>
            </div>
            <div class="bank-transfer-status">
                <span class="status {{ $payment->status ?? 'pending' }}">
                    {{ ucfirst($payment->status ?? 'pending') }}
                </span>
            </div>
        </div>

        @php $params = (object) ($params ?? []); @endphp

        <div class="bank-transfer-grid">
            <div class="bank-details">
                <div class="bank-details-row">
                    <span class="label">Account Name</span>
                    <span class="value">{{ $params->name ?? 'N/A' }}</span>
                </div>
                <div class="bank-details-row">
                    <span class="label">Account Number</span>
                    <span class="value">{{ $params->account_number ?? 'N/A' }}</span>
                </div>
                <div class="bank-details-row">
                    <span class="label">Bank / Branch</span>
                    <span class="value">{{ $params->branch_name ?? 'N/A' }}</span>
                </div>
                <div class="bank-details-row">
                    <span class="label">Currency</span>
                    <span class="value">{{ $params->gateway_currency ?? 'NGN' }}</span>
                </div>
                <div class="bank-details-row highlight">
                    <span class="label">Amount to Pay</span>
                    <span class="value">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="bank-details-row">
                    <span class="label">Payment Reference</span>
                    <span class="value mono">{{ $payment->reference }}</span>
                </div>
                <p class="hint mt-10">
                    Use this reference in your transfer description so support can match your payment quickly.
                </p>
            </div>

            <div class="bank-proof">
                <h3 class="title small">Upload Payment Proof</h3>

                <form action="{{ route('user.payment.bank.upload-proof') }}"
                      method="post"
                      enctype="multipart/form-data"
                      class="mt-10">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">
                            Screenshot or receipt
                            <span class="required">*</span>
                        </label>
                        <input type="file"
                               name="proof"
                               class="form-control"
                               accept="image/*"
                               required>
                        <span class="form-hint">Accepted formats: JPG, PNG. Max 5MB.</span>
                    </div>

                    <button type="submit" class="btn btn-primary mt-10">
                        <span>Submit Proof</span>
                        <i class="ti ti-upload"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection