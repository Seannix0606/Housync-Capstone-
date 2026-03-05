@extends('emails.layout')

@section('title', 'Maintenance Update - HouseSync')

@section('body')
<h2>Maintenance Request Update</h2>

<p>There has been an update to your maintenance request:</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Ticket Number</span>
        <span class="info-value">{{ $ticketNumber }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Title</span>
        <span class="info-value">{{ $request->title }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Status Changed</span>
        <span class="info-value">
            <span class="badge badge-warning">{{ ucfirst(str_replace('_', ' ', $oldStatus)) }}</span>
            &rarr;
            @php
                $badgeClass = match($newStatus) {
                    'completed' => 'badge-success',
                    'cancelled' => 'badge-danger',
                    'in_progress' => 'badge-info',
                    default => 'badge-warning',
                };
            @endphp
            <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $newStatus)) }}</span>
        </span>
    </div>
    <div class="info-row">
        <span class="info-label">Priority</span>
        <span class="info-value">{{ ucfirst($request->priority) }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Category</span>
        <span class="info-value">{{ ucfirst($request->category) }}</span>
    </div>
</div>

@if($newStatus === 'completed')
<p style="text-align:center;color:#059669;font-weight:600">Your maintenance request has been completed!</p>
@endif

<p style="text-align:center">
    <a href="{{ config('app.url') }}" class="btn">View Details</a>
</p>
@endsection
