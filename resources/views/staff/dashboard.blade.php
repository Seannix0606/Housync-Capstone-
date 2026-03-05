@extends('layouts.staff-app')

@section('title', 'Staff Dashboard')

@section('content')
<div class="page-title-box mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="fw-bold">Staff Dashboard</h1>
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Staff Dashboard</li>
        </ol>
    </div>
</div>

<!-- Welcome Section -->
<div class="card mb-4">
    <div class="card-body d-flex align-items-center">
        <div class="avatar-lg me-3">
            <span class="avatar-title bg-soft-primary rounded-circle">
                <i class="mdi mdi-account font-24 text-primary"></i>
            </span>
        </div>
        <div>
            <h4 class="mb-1">Welcome, {{ Auth::user()->staffProfile->name ?? Auth::user()->email }}!</h4>
            <p class="text-muted mb-0">
                <i class="mdi mdi-tools me-1"></i>
                {{ ucwords(str_replace('_', ' ', Auth::user()->staffProfile->staff_type ?? 'Staff')) }}
                @if($stats['active_tasks'] > 0)
                    - {{ $stats['active_tasks'] }} Active {{ Str::plural('Task', $stats['active_tasks']) }}
                @else
                    - No Active Tasks
                @endif
            </p>
        </div>
    </div>
</div>

<!-- Statistics Row -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="text-muted fw-normal mt-0" title="Total Assigned">Total Assigned</h5>
                        <h3 class="mt-3 mb-3">{{ $stats['total_assigned'] }}</h3>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-soft-primary rounded">
                            <i class="mdi mdi-clipboard-list-outline font-20 text-primary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="text-muted fw-normal mt-0" title="Active Tasks">Active Tasks</h5>
                        <h3 class="mt-3 mb-3">{{ $stats['active_tasks'] }}</h3>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-soft-warning rounded">
                            <i class="mdi mdi-clock-outline font-20 text-warning"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="text-muted fw-normal mt-0" title="In Progress">In Progress</h5>
                        <h3 class="mt-3 mb-3">{{ $stats['in_progress'] }}</h3>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-soft-info rounded">
                            <i class="mdi mdi-progress-wrench font-20 text-info"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="text-muted fw-normal mt-0" title="Completed">Completed</h5>
                        <h3 class="mt-3 mb-3">{{ $stats['completed'] }}</h3>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-soft-success rounded">
                            <i class="mdi mdi-check-circle-outline font-20 text-success"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($currentTask)
<!-- Current Active Task -->
<div class="row">
    <div class="col-xl-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0 text-white"><i class="mdi mdi-star me-1"></i> Current Priority Task</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="mb-3">{{ $currentTask->title }}</h5>
                        <p class="text-muted">{{ $currentTask->description }}</p>
                        
                        <div class="mb-3">
                            <span class="badge bg-{{ $currentTask->priority_badge_class }} me-2">
                                {{ ucfirst($currentTask->priority) }} Priority
                            </span>
                            <span class="badge bg-{{ $currentTask->status_badge_class }}">
                                {{ ucwords(str_replace('_', ' ', $currentTask->status)) }}
                            </span>
                            <span class="badge bg-secondary ms-2">
                                <i class="mdi mdi-shape me-1"></i>{{ ucfirst($currentTask->category) }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Location:</label>
                            <p class="mb-1"><i class="mdi mdi-map-marker me-1"></i>{{ $currentTask->unit->apartment->name }} - Unit {{ $currentTask->unit->unit_number }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Requested Date:</label>
                            <p class="mb-1">{{ $currentTask->requested_date->format('F d, Y') }}</p>
                        </div>

                        @if($currentTask->expected_completion_date)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Expected Completion:</label>
                            <p class="mb-1">
                                {{ $currentTask->expected_completion_date->format('F d, Y') }}
                                @if($currentTask->expected_completion_date->isPast() && $currentTask->status != 'completed')
                                    <span class="badge bg-danger ms-2">Overdue</span>
                                @endif
                            </p>
                        </div>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('staff.maintenance.show', $currentTask->id) }}" class="btn btn-primary">
                                <i class="mdi mdi-eye me-1"></i> View Full Details
                            </a>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <h6 class="text-primary mb-3">Tenant Information</h6>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Name:</label>
                            <p class="mb-1">{{ $currentTask->tenant->tenantProfile->name ?? $currentTask->tenant->email }}</p>
                        </div>
                        @if($currentTask->tenant->email)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email:</label>
                            <p class="mb-1">{{ $currentTask->tenant->email }}</p>
                        </div>
                        @endif
                        @if($currentTask->tenant->tenantProfile && $currentTask->tenant->tenantProfile->phone)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone:</label>
                            <p class="mb-1">{{ $currentTask->tenant->tenantProfile->phone }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title"><i class="mdi mdi-account-tie me-1"></i> Landlord Information</h4>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-sm me-3">
                        <span class="avatar-title bg-soft-primary rounded-circle">
                            {{ substr($currentTask->landlord->landlordProfile->name ?? 'L', 0, 1) }}
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $currentTask->landlord->landlordProfile->name ?? 'Landlord' }}</h6>
                        <small class="text-muted">{{ $currentTask->landlord->email }}</small>
                    </div>
                </div>
                @if($currentTask->landlord->landlordProfile && $currentTask->landlord->landlordProfile->phone)
                <div class="mb-2">
                    <label class="form-label fw-bold">Phone:</label>
                    <p class="mb-1">{{ $currentTask->landlord->landlordProfile->phone }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title"><i class="mdi mdi-lightning-bolt me-1"></i> Quick Actions</h4>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('staff.maintenance.index') }}" class="btn btn-primary">
                        <i class="mdi mdi-format-list-bulleted me-1"></i> View All Tasks
                    </a>
                    <a href="{{ route('staff.maintenance.show', $currentTask->id) }}" class="btn btn-outline-success">
                        <i class="mdi mdi-pencil me-1"></i> Update Task Status
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<!-- No Active Tasks -->
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body text-center py-5">
                <i class="mdi mdi-check-circle text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3">All Caught Up!</h4>
                <p class="text-muted">You have no active maintenance tasks assigned at the moment.</p>
                <a href="{{ route('staff.maintenance.index') }}" class="btn btn-outline-primary mt-3">
                    <i class="mdi mdi-history me-1"></i> View Task History
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Recent Maintenance Tasks -->
@if($activeMaintenanceRequests->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h4 class="card-title"><i class="mdi mdi-tools me-1"></i> Your Maintenance Tasks</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-centered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeMaintenanceRequests as $request)
                    <tr>
                        <td>#{{ $request->id }}</td>
                        <td>
                            <strong>{{ Str::limit($request->title, 40) }}</strong><br>
                            <small class="text-muted">{{ Str::limit($request->description, 50) }}</small>
                        </td>
                        <td>
                            <small>{{ $request->unit->apartment->name }}</small><br>
                            <small class="text-muted">Unit {{ $request->unit->unit_number }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $request->priority_badge_class }}">
                                {{ ucfirst($request->priority) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $request->status_badge_class }}">
                                {{ ucwords(str_replace('_', ' ', $request->status)) }}
                            </span>
                        </td>
                        <td>{{ $request->requested_date->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('staff.maintenance.show', $request->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                <i class="mdi mdi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($activeMaintenanceRequests->count() >= 10)
        <div class="text-center mt-3">
            <a href="{{ route('staff.maintenance.index') }}" class="btn btn-outline-primary">
                View All Tasks <i class="mdi mdi-arrow-right ms-1"></i>
            </a>
        </div>
        @endif
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// Add any additional staff dashboard functionality here
</script>
@endpush
