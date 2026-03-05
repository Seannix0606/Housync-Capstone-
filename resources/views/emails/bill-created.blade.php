@extends('emails.layout')

@section('title', 'New Bill - HouseSync')

@section('body')
<h2>New Bill Created</h2>

<p>Hi {{ $tenantName }},</p>

<p>A new bill has been created for your unit. Please review the details below:</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Invoice Number</span>
        <span class="info-value">{{ $bill->invoice_number }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Type</span>
        <span class="info-value">{{ ucfirst($bill->type) }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Amount</span>
        <span class="info-value" style="color:#059669;font-size:18px">₱{{ number_format($bill->amount, 2) }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Due Date</span>
        <span class="info-value">{{ $bill->due_date?->format('F d, Y') ?? 'N/A' }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Property</span>
        <span class="info-value">{{ $propertyName }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Unit</span>
        <span class="info-value">{{ $unitNumber }}</span>
    </div>
    @if($bill->billing_period_start && $bill->billing_period_end)
    <div class="info-row">
        <span class="info-label">Billing Period</span>
        <span class="info-value">{{ $bill->billing_period_start->format('M d') }} - {{ $bill->billing_period_end->format('M d, Y') }}</span>
    </div>
    @endif
</div>

@if($bill->description)
<p><strong>Description:</strong> {{ $bill->description }}</p>
@endif

<p style="text-align:center">
    <a href="{{ route('tenant.payments') }}" class="btn">View My Bills</a>
</p>

<p style="font-size:13px;color:#6b7280">Please ensure timely payment to avoid late fees. Contact your landlord if you have any questions.</p>
@endsection
