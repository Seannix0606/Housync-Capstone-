@extends('layouts.staff-app')

@section('title', 'Maintenance Task Details')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/maintenance.css') }}">
@endpush

@section('content')
<div class="maintenance-container">
    <!-- Header with Back Button -->
    <div class="maintenance-header">
        <div class="header-title">
            <a href="{{ route('staff.maintenance.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to My Tasks
            </a>
            <h1><i class="fas fa-tools"></i> Maintenance Task #{{ $maintenanceRequest->id }}</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="maintenance-details-grid">
        <!-- Main Information Card -->
        <div class="details-card main-card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Task Information</h2>
                <div class="header-badges">
                    <span class="priority-badge priority-{{ $maintenanceRequest->priority }}">
                        {{ ucfirst($maintenanceRequest->priority) }} Priority
                    </span>
                    <span class="status-badge status-{{ str_replace('_', '-', $maintenanceRequest->status) }}">
                        {{ ucwords(str_replace('_', ' ', $maintenanceRequest->status)) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <label><i class="fas fa-heading"></i> Title:</label>
                    <div class="info-value">{{ $maintenanceRequest->title }}</div>
                </div>
                
                <div class="info-row">
                    <label><i class="fas fa-align-left"></i> Description:</label>
                    <div class="info-value description-box">{{ $maintenanceRequest->description }}</div>
                </div>
                
                <div class="info-row">
                    <label><i class="fas fa-tag"></i> Category:</label>
                    <div class="info-value">
                        <span class="category-badge category-{{ $maintenanceRequest->category }}">
                            <i class="fas fa-{{ $maintenanceRequest->category == 'plumbing' ? 'water' : ($maintenanceRequest->category == 'electrical' ? 'bolt' : ($maintenanceRequest->category == 'hvac' ? 'wind' : 'tools')) }}"></i>
                            {{ ucfirst($maintenanceRequest->category) }}
                        </span>
                    </div>
                </div>
                
                <div class="info-row">
                    <label><i class="fas fa-calendar"></i> Requested Date:</label>
                    <div class="info-value">{{ $maintenanceRequest->requested_date->format('F d, Y') }}</div>
                </div>
                
                @if($maintenanceRequest->expected_completion_date)
                <div class="info-row">
                    <label><i class="fas fa-calendar-alt"></i> Expected Completion:</label>
                    <div class="info-value">
                        {{ $maintenanceRequest->expected_completion_date->format('F d, Y') }}
                        @if($maintenanceRequest->status != 'completed' && $maintenanceRequest->expected_completion_date->isPast())
                            <span class="priority-badge priority-urgent" style="margin-left: 0.5rem;">Overdue</span>
                        @endif
                    </div>
                </div>
                @endif
                
                @if($maintenanceRequest->completed_date)
                <div class="info-row">
                    <label><i class="fas fa-calendar-check"></i> Completed Date:</label>
                    <div class="info-value">{{ $maintenanceRequest->completed_date->format('F d, Y') }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Property and Tenant Information -->
        <div class="details-card">
            <div class="card-header">
                <h2><i class="fas fa-building"></i> Location & Contact</h2>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <label><i class="fas fa-building"></i> Property:</label>
                    <div class="info-value">{{ $maintenanceRequest->unit->apartment->name ?? 'N/A' }}</div>
                </div>
                
                <div class="info-row">
                    <label><i class="fas fa-door-open"></i> Unit:</label>
                    <div class="info-value">Unit {{ $maintenanceRequest->unit->unit_number ?? 'N/A' }}</div>
                </div>
                
                <div class="info-row">
                    <label><i class="fas fa-user"></i> Tenant:</label>
                    <div class="info-value">{{ $maintenanceRequest->tenant->tenantProfile->name ?? $maintenanceRequest->tenant->email }}</div>
                </div>
                
                @if($maintenanceRequest->tenant->email)
                <div class="info-row">
                    <label><i class="fas fa-envelope"></i> Email:</label>
                    <div class="info-value">{{ $maintenanceRequest->tenant->email }}</div>
                </div>
                @endif
                
                @if($maintenanceRequest->tenant->tenantProfile && $maintenanceRequest->tenant->tenantProfile->phone)
                <div class="info-row">
                    <label><i class="fas fa-phone"></i> Phone:</label>
                    <div class="info-value">{{ $maintenanceRequest->tenant->tenantProfile->phone }}</div>
                </div>
                @endif

                @if($maintenanceRequest->tenant_notes)
                <div class="info-row">
                    <label><i class="fas fa-comment"></i> Tenant Notes:</label>
                    <div class="info-value notes-box">{{ $maintenanceRequest->tenant_notes }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Status Management Card -->
        <div class="details-card">
            <div class="card-header">
                <h2><i class="fas fa-tasks"></i> Update Status</h2>
            </div>
            <div class="card-body">
                @if($maintenanceRequest->status != 'completed' && $maintenanceRequest->status != 'cancelled')
                <form method="POST" action="{{ route('staff.maintenance.update-status', $maintenanceRequest->id) }}" class="status-form">
                    @csrf
                    <label>Change Task Status:</label>
                    <select name="status" required>
                        @if($maintenanceRequest->status == 'pending' || $maintenanceRequest->status == 'assigned')
                            <option value="in_progress" selected>Start Working (In Progress)</option>
                            <option value="completed">Mark as Completed</option>
                        @elseif($maintenanceRequest->status == 'in_progress')
                            <option value="in_progress" selected>In Progress</option>
                            <option value="completed">Mark as Completed</option>
                        @endif
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Status</button>
                </form>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This task is {{ $maintenanceRequest->status }}. Status cannot be changed.
                    </div>
                @endif
            </div>
        </div>

        <!-- Landlord Information -->
        <div class="details-card">
            <div class="card-header" style="background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);">
                <h2><i class="fas fa-user-tie"></i> Landlord</h2>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <label><i class="fas fa-user-tie"></i> Name:</label>
                    <div class="info-value">{{ $maintenanceRequest->landlord->landlordProfile->name ?? $maintenanceRequest->landlord->email }}</div>
                </div>
                
                @if($maintenanceRequest->landlord->email)
                <div class="info-row">
                    <label><i class="fas fa-envelope"></i> Email:</label>
                    <div class="info-value">{{ $maintenanceRequest->landlord->email }}</div>
                </div>
                @endif
                
                @if($maintenanceRequest->landlord->landlordProfile && $maintenanceRequest->landlord->landlordProfile->phone)
                <div class="info-row">
                    <label><i class="fas fa-phone"></i> Phone:</label>
                    <div class="info-value">{{ $maintenanceRequest->landlord->landlordProfile->phone }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Staff Notes Card -->
        <div class="details-card full-width">
            <div class="card-header">
                <h2><i class="fas fa-sticky-note"></i> My Work Notes</h2>
            </div>
            <div class="card-body">
                @if($maintenanceRequest->staff_notes)
                    <div class="current-notes">
                        <p>{{ $maintenanceRequest->staff_notes }}</p>
                    </div>
                @endif
                
                @if($maintenanceRequest->status != 'completed' && $maintenanceRequest->status != 'cancelled')
                <form method="POST" action="{{ route('staff.maintenance.update-notes', $maintenanceRequest->id) }}" class="notes-form">
                    @csrf
                    <label for="staff_notes">Update work notes:</label>
                    <textarea name="staff_notes" id="staff_notes" rows="4" placeholder="Add notes about the work performed, parts used, observations, etc...">{{ $maintenanceRequest->staff_notes }}</textarea>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Notes</button>
                </form>
                @else
                    @if(!$maintenanceRequest->staff_notes)
                        <p class="text-muted" style="margin: 0;">
                            <i class="fas fa-info-circle"></i> No work notes added for this task.
                        </p>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

