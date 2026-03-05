@extends('emails.layout')

@section('title', 'Lease Expiry Reminder - HouseSync')

@section('body')
<h2>Lease Expiry Reminder</h2>

<p>Hi {{ $tenantName }},</p>

<p>This is a reminder that your lease is expiring soon. Please review the details below:</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Unit</span>
        <span class="info-value">{{ $unitNumber }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Lease End Date</span>
        <span class="info-value" style="color:#dc2626;font-weight:700">{{ $expiryDate }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Days Remaining</span>
        <span class="info-value">
            @if($daysRemaining <= 7)
                <span class="badge badge-danger">{{ $daysRemaining }} days</span>
            @elseif($daysRemaining <= 30)
                <span class="badge badge-warning">{{ $daysRemaining }} days</span>
            @else
                <span class="badge badge-info">{{ $daysRemaining }} days</span>
            @endif
        </span>
    </div>
</div>

<p>Please contact your landlord to discuss lease renewal options or plan your move-out accordingly.</p>

<p style="text-align:center">
    <a href="{{ route('tenant.dashboard') }}" class="btn">Go to Dashboard</a>
</p>
@endsection
