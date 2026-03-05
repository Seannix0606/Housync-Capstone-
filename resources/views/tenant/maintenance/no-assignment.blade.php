@extends('layouts.app')

@section('title', 'Maintenance Requests')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/maintenance.css') }}">
@endpush

@section('content')
<div class="maintenance-container">
    <div class="maintenance-header">
        <div class="header-title">
            <h1><i class="fas fa-tools"></i> Maintenance Requests</h1>
        </div>
    </div>

    <div class="empty-state" style="padding: 4rem 2rem;">
        <i class="fas fa-home" style="font-size: 4rem; color: #95a5a6; margin-bottom: 1.5rem;"></i>
        <h3 style="font-size: 1.5rem; color: #7f8c8d; margin-bottom: 1rem;">No Active Unit Assignment</h3>
        <p style="color: #95a5a6; margin-bottom: 2rem;">
            You need to have an active unit assignment to submit maintenance requests.
        </p>
        <a href="{{ route('tenant.dashboard') }}" class="btn btn-primary">
            <i class="fas fa-home"></i> Go to Dashboard
        </a>
    </div>
</div>
@endsection


