@extends('layouts.app')

@section('title', 'Bill Details - ' . $bill->invoice_number)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/billing.css') }}">
<style>
    .bill-detail-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .bill-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .bill-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 1.5rem 2rem;
    }
    .bill-header h2 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .bill-header p {
        opacity: 0.9;
        margin: 0;
    }
    .bill-body {
        padding: 2rem;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .info-label {
        font-size: 0.8rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-value {
        font-size: 1rem;
        font-weight: 500;
        color: #1e293b;
    }
    .amount-section {
        background: #f8fafc;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .amount-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .amount-row:last-child {
        border-bottom: none;
        font-weight: 600;
        font-size: 1.1rem;
    }
    .amount-row.balance {
        color: #ef4444;
    }
    .amount-row.paid {
        color: #10b981;
    }
    .status-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .status-badge.paid { background: #d1fae5; color: #065f46; }
    .status-badge.unpaid { background: #fee2e2; color: #991b1b; }
    .status-badge.partially_paid { background: #fef3c7; color: #92400e; }
    .status-badge.overdue { background: #fecaca; color: #7f1d1d; }
    .payment-history {
        margin-top: 2rem;
    }
    .payment-history h4 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #1e293b;
    }
    .payment-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 0.5rem;
    }
    .payment-details {
        display: flex;
        flex-direction: column;
    }
    .payment-date {
        font-size: 0.85rem;
        color: #64748b;
    }
    .payment-method {
        font-size: 0.8rem;
        color: #94a3b8;
    }
    .payment-amount {
        font-weight: 600;
        color: #10b981;
    }
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        text-decoration: none;
        font-weight: 500;
        margin-bottom: 1rem;
    }
    .btn-back:hover {
        color: #1e293b;
    }
</style>
@endpush

@section('content')
<div class="bill-detail-container">
    <a href="{{ route('tenant.payments') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back to Payments
    </a>

    <div class="bill-card">
        <div class="bill-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2>{{ $bill->invoice_number }}</h2>
                    <p>{{ ucfirst($bill->type) }} Bill</p>
                </div>
                <span class="status-badge {{ $bill->status }}">
                    {{ str_replace('_', ' ', ucfirst($bill->status)) }}
                </span>
            </div>
        </div>
        
        <div class="bill-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Property</span>
                    <span class="info-value">{{ $bill->unit->property->name ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Unit</span>
                    <span class="info-value">{{ $bill->unit->unit_number ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Due Date</span>
                    <span class="info-value">{{ $bill->due_date ? $bill->due_date->format('F d, Y') : '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Created</span>
                    <span class="info-value">{{ $bill->created_at->format('F d, Y') }}</span>
                </div>
                @if($bill->billing_period_start && $bill->billing_period_end)
                <div class="info-item">
                    <span class="info-label">Billing Period</span>
                    <span class="info-value">
                        {{ $bill->billing_period_start->format('M d') }} - {{ $bill->billing_period_end->format('M d, Y') }}
                    </span>
                </div>
                @endif
            </div>

            @if($bill->description)
            <div class="mb-4">
                <span class="info-label">Description</span>
                <p class="info-value mt-1">{{ $bill->description }}</p>
            </div>
            @endif

            <div class="amount-section">
                <div class="amount-row">
                    <span>Total Amount</span>
                    <span>₱{{ number_format($bill->amount, 2) }}</span>
                </div>
                <div class="amount-row paid">
                    <span>Amount Paid</span>
                    <span>₱{{ number_format($bill->amount_paid, 2) }}</span>
                </div>
                <div class="amount-row {{ $bill->balance > 0 ? 'balance' : 'paid' }}">
                    <span>Balance Due</span>
                    <span>₱{{ number_format($bill->balance, 2) }}</span>
                </div>
            </div>

            @if($bill->payments->count() > 0)
            <div class="payment-history">
                <h4><i class="fas fa-history me-2"></i>Payment History</h4>
                @foreach($bill->payments as $payment)
                <div class="payment-item">
                    <div class="payment-details">
                        <span class="payment-date">{{ $payment->paid_at->format('M d, Y h:i A') }}</span>
                        <span class="payment-method">
                            {{ ucfirst(str_replace('_', ' ', $payment->method)) }}
                            @if($payment->reference_number)
                                • Ref: {{ $payment->reference_number }}
                            @endif
                        </span>
                    </div>
                    <span class="payment-amount">+₱{{ number_format($payment->amount, 2) }}</span>
                </div>
                @endforeach
            </div>
            @endif

            @if($bill->notes)
            <div class="mt-4 p-3 bg-light rounded">
                <strong class="d-block mb-1"><i class="fas fa-sticky-note me-1"></i> Notes from Landlord</strong>
                <p class="mb-0 text-muted">{{ $bill->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

