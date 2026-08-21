@extends('backend.layout.app')

@section('content')
<div class="container">
    <div class="container-header">
        <h1>{{ $title }}</h1>
    </div>

    @php
        $statusBadgeClass = $payment->status === 'success'
            ? 'active'
            : ($payment->status === 'failed' ? 'inactive' : 'pending');
        $statusLabel = $payment->status === 'success'
            ? 'Approved'
            : ($payment->status === 'failed' ? 'Rejected' : 'Pending');
    @endphp

    <div class="card deposit-details-card">
        <div class="list-group mb-25">
            <div class="list-group-item">
                <span>Transaction id</span>
                <span>{{ $payment->reference }}</span>
            </div>
            <div class="list-group-item">
                <span>User</span>
                <span>{{ $payment->user?->fname }} {{ $payment->user?->lname }}</span>
            </div>
            <div class="list-group-item">
                <span>Payment date</span>
                <span>{{ $payment->created_at?->format('d F Y, h:i A') }}</span>
            </div>
            <div class="list-group-item">
                <span>Payment gateway</span>
                <span>{{ ucfirst($payment->method) }}</span>
            </div>
            <div class="list-group-item">
                <span>Amount</span>
                <span>{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</span>
            </div>
            <div class="list-group-item">
                <span>Channel</span>
                <span>{{ $payment->channel ?? 'N/A' }}</span>
            </div>
            <div class="list-group-item">
                <span>Status</span>
                <span class="badge {{ $statusBadgeClass }}">
                    {{ $statusLabel }}
                </span>
            </div>
        </div>

        @if($payment->method === 'bank')
            <div class="list-group mb-25">
                <div class="list-group-item">
                    <span>Account name</span>
                    <span>{{ $params->name ?? 'N/A' }}</span>
                </div>
                <div class="list-group-item">
                    <span>Account number</span>
                    <span>{{ $params->account_number ?? 'N/A' }}</span>
                </div>
                <div class="list-group-item">
                    <span>Bank / Branch</span>
                    <span>{{ $params->branch_name ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="list-group mb-25">
                <h3>User Payment Proof</h3>
                @if($payment->proof_path)
                    <div class="list-group-item has-image">
                        <span>Payment proof</span>
                        <a href="{{ asset($payment->proof_path) }}" target="_blank">
                            <img src="{{ asset($payment->proof_path) }}" alt="Payment proof">
                        </a>
                    </div>
                @else
                    <div class="list-group-item">
                        <span class="text-muted">No payment proof submitted</span>
                    </div>
                @endif
            </div>

            @if($payment->status === 'pending')
                <div class="deposit-actions">
                    <form action="{{ route('backend.payments.approve-bank', $payment) }}" method="post" style="display: inline-block;" onsubmit="return confirm('Approve this bank payment?');">
                        @csrf
                        <button type="submit" class="btn btn-primary">Approve & Credit</button>
                    </form>
                    <form action="{{ route('backend.payments.reject-bank', $payment) }}" method="post" style="display: inline-block;" onsubmit="return confirm('Reject this bank payment?');">
                        @csrf
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </form>
                </div>
            @endif
        @endif
                {{-- Manual Status Override: For missed gateway callbacks --}}
        @if(in_array($payment->status, ['pending', 'failed']))
            <div class="deposit-actions" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                <p style="margin-bottom: 10px; color: #666; font-size: 13px;">
                    <i class="las la-info-circle"></i> 
                    User may have left before gateway callback. Only mark as success if you have verified payment with the provider.
                </p>
                <form action="{{ route('backend.payments.mark-success', $payment) }}" method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to mark this payment as SUCCESS?\n\nAmount: {{ number_format($payment->amount, 2) }} {{ $payment->currency }}\nUser: {{ $payment->user?->fname }} {{ $payment->user?->lname }}\n\nThis will credit the user.');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">
                        <i class="las la-check-circle"></i> Mark as Success
                    </button>
                </form>
            </div>
        @endif

        @if($payment->gateway_response)
            <div class="list-group mb-25">
                <h3>Gateway Response (raw)</h3>
                <div class="list-group-item">
                    <pre style="white-space: pre-wrap; word-break: break-word; font-size: 12px; margin: 0;">
{{ $payment->gateway_response }}
                    </pre>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection