@extends('layouts.app')

@section('title', 'Maintenance Request Details')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/maintenance.css') }}">
@endpush

@section('content')
<div class="maintenance-container">
    <!-- Header with Back Button -->
    <div class="maintenance-header">
        <div class="header-title">
            <a href="{{ route('tenant.maintenance.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to My Requests
            </a>
            <h1><i class="fas fa-tools"></i> Request #{{ $maintenanceRequest->id }}</h1>
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
                <h2><i class="fas fa-info-circle"></i> Request Information</h2>
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
                
                @if($maintenanceRequest->completed_date)
                <div class="info-row">
                    <label><i class="fas fa-calendar-check"></i> Completed Date:</label>
                    <div class="info-value">{{ $maintenanceRequest->completed_date->format('F d, Y') }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Property Information -->
        <div class="details-card">
            <div class="card-header">
                <h2><i class="fas fa-building"></i> Property Details</h2>
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
                    <label><i class="fas fa-user-tie"></i> Landlord:</label>
                    <div class="info-value">{{ $maintenanceRequest->landlord->name ?? 'N/A' }}</div>
                </div>
                
                @if($maintenanceRequest->landlord->email)
                <div class="info-row">
                    <label><i class="fas fa-envelope"></i> Email:</label>
                    <div class="info-value">{{ $maintenanceRequest->landlord->email }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Assigned Staff (if any) -->
        <div class="details-card">
            <div class="card-header">
                <h2><i class="fas fa-user-cog"></i> Assigned Staff</h2>
            </div>
            <div class="card-body">
                @if($maintenanceRequest->assignedStaff)
                    <div class="assigned-staff-info">
                        <div class="staff-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="staff-details">
                            <h3>{{ $maintenanceRequest->assignedStaff->staffProfile->name ?? $maintenanceRequest->assignedStaff->email }}</h3>
                            @if($maintenanceRequest->assignedStaff->staffProfile)
                                <p><strong>Type:</strong> {{ ucwords(str_replace('_', ' ', $maintenanceRequest->assignedStaff->staffProfile->staff_type ?? 'N/A')) }}</p>
                                @if($maintenanceRequest->assignedStaff->staffProfile->phone)
                                    <p><strong>Phone:</strong> {{ $maintenanceRequest->assignedStaff->staffProfile->phone }}</p>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    @if($maintenanceRequest->status == 'assigned')
                        <div class="alert alert-info" style="margin-top: 1rem;">
                            <i class="fas fa-info-circle"></i> A staff member has been assigned to your request. They will contact you soon.
                        </div>
                    @elseif($maintenanceRequest->status == 'in_progress')
                        <div class="alert alert-success" style="margin-top: 1rem;">
                            <i class="fas fa-spinner"></i> Work is currently in progress on your request.
                        </div>
                    @endif
                @else
                    <div class="no-assignment">
                        <i class="fas fa-clock"></i>
                        <p>No staff assigned yet</p>
                        <small>Your landlord will assign a staff member soon</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Status Timeline -->
        <div class="details-card">
            <div class="card-header">
                <h2><i class="fas fa-tasks"></i> Request Status</h2>
            </div>
            <div class="card-body">
                <div class="status-timeline">
                    <div class="timeline-item {{ $maintenanceRequest->status != 'pending' ? 'completed' : 'active' }}">
                        <div class="timeline-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="timeline-content">
                            <h4>Submitted</h4>
                            <p>{{ $maintenanceRequest->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    
                    <div class="timeline-item {{ in_array($maintenanceRequest->status, ['assigned', 'in_progress', 'completed']) ? 'completed' : ($maintenanceRequest->status == 'pending' ? 'pending' : 'inactive') }}">
                        <div class="timeline-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="timeline-content">
                            <h4>Assigned</h4>
                            <p>{{ in_array($maintenanceRequest->status, ['assigned', 'in_progress', 'completed']) ? 'Staff assigned' : 'Waiting for assignment' }}</p>
                        </div>
                    </div>
                    
                    <div class="timeline-item {{ in_array($maintenanceRequest->status, ['in_progress', 'completed']) ? 'completed' : ($maintenanceRequest->status == 'assigned' ? 'active' : 'inactive') }}">
                        <div class="timeline-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="timeline-content">
                            <h4>In Progress</h4>
                            <p>{{ in_array($maintenanceRequest->status, ['in_progress', 'completed']) ? 'Work in progress' : 'Not started yet' }}</p>
                        </div>
                    </div>
                    
                    <div class="timeline-item {{ $maintenanceRequest->status == 'completed' ? 'completed' : 'inactive' }}">
                        <div class="timeline-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <h4>Completed</h4>
                            <p>{{ $maintenanceRequest->status == 'completed' ? $maintenanceRequest->completed_date->format('M d, Y') : 'Not completed yet' }}</p>
                        </div>
                    </div>
                </div>

                @if($maintenanceRequest->status == 'cancelled')
                    <div class="alert alert-warning">
                        <i class="fas fa-ban"></i> This request has been cancelled.
                    </div>
                @endif
            </div>
        </div>

        <!-- Your Notes Card -->
        <div class="details-card full-width">
            <div class="card-header">
                <h2><i class="fas fa-sticky-note"></i> Your Notes</h2>
            </div>
            <div class="card-body">
                @if($maintenanceRequest->tenant_notes)
                    <div class="current-notes">
                        <p>{{ $maintenanceRequest->tenant_notes }}</p>
                    </div>
                @endif
                
                @if(!in_array($maintenanceRequest->status, ['completed', 'cancelled']))
                <form method="POST" action="{{ route('tenant.maintenance.update-notes', $maintenanceRequest->id) }}" class="notes-form">
                    @csrf
                    <label for="tenant_notes">Update your notes:</label>
                    <textarea name="tenant_notes" id="tenant_notes" rows="4" placeholder="Add or update your notes about this request...">{{ $maintenanceRequest->tenant_notes }}</textarea>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Notes</button>
                </form>
                @else
                    <p class="text-muted" style="margin: 0;">
                        <i class="fas fa-info-circle"></i> This request is {{ $maintenanceRequest->status }}. Notes cannot be updated.
                    </p>
                @endif
            </div>
        </div>

        <!-- Staff Notes (if any) -->
        @if($maintenanceRequest->staff_notes)
        <div class="details-card full-width">
            <div class="card-header" style="background: linear-gradient(135deg, #1abc9c 0%, #16a085 100%);">
                <h2><i class="fas fa-comment-dots"></i> Staff Notes</h2>
            </div>
            <div class="card-body">
                <div class="staff-notes-box">
                    <p>{{ $maintenanceRequest->staff_notes }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Actions -->
        @if(!in_array($maintenanceRequest->status, ['completed', 'cancelled']))
        <div class="details-card full-width">
            <div class="card-header" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                <h2><i class="fas fa-exclamation-triangle"></i> Request Actions</h2>
            </div>
            <div class="card-body">
                <p style="margin-bottom: 1rem;">If this request is no longer needed, you can cancel it below.</p>
                <form method="POST" action="{{ route('tenant.maintenance.cancel', $maintenanceRequest->id) }}" onsubmit="return confirm('Are you sure you want to cancel this maintenance request?');">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancel This Request
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.status-timeline {
    padding: 1rem 0;
}

.timeline-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border-left: 3px solid #ddd;
    margin-left: 1rem;
    position: relative;
}

.timeline-item:not(:last-child) {
    margin-bottom: 0.5rem;
}

.timeline-item.completed {
    border-color: #2e7d32;
}

.timeline-item.completed .timeline-icon {
    background: #2e7d32;
}

.timeline-item.active {
    border-color: #3498db;
}

.timeline-item.active .timeline-icon {
    background: #3498db;
}

.timeline-item.pending .timeline-icon {
    background: #f39c12;
}

.timeline-item.inactive .timeline-icon {
    background: #95a5a6;
}

.timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
    margin-left: -1.3rem;
}

.timeline-content h4 {
    margin: 0 0 0.25rem 0;
    color: #2c3e50;
    font-size: 1rem;
}

.timeline-content p {
    margin: 0;
    color: #7f8c8d;
    font-size: 0.9rem;
}

.staff-notes-box {
    background: #f0fdf4;
    padding: 1rem;
    border-radius: 6px;
    border-left: 4px solid #16a085;
    line-height: 1.6;
    color: #2c3e50;
}

.stat-icon.completed {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
}
</style>
@endsection

