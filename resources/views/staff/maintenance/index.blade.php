@extends('layouts.staff-app')

@section('title', 'My Maintenance Tasks')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/maintenance.css') }}">
@endpush

@section('content')
<div class="maintenance-container">
    <!-- Header Section -->
    <div class="maintenance-header">
        <div class="header-title">
            <h1><i class="fas fa-tools"></i> My Maintenance Tasks</h1>
            <p class="subtitle">View and manage your assigned maintenance requests</p>
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

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-details">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Tasks</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3>{{ $stats['pending'] }}</h3>
                <p>Pending/Assigned</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon in-progress">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="stat-details">
                <h3>{{ $stats['in_progress'] }}</h3>
                <p>In Progress</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon completed">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3>{{ $stats['completed'] }}</h3>
                <p>Completed</p>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
        <form method="GET" action="{{ route('staff.maintenance.index') }}" class="filters-form">
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> Status</label>
                <select name="status">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-exclamation-circle"></i> Priority</label>
                <select name="priority">
                    <option value="all" {{ request('priority') == 'all' ? 'selected' : '' }}>All Priorities</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                <a href="{{ route('staff.maintenance.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- Maintenance Requests Table -->
    <div class="maintenance-table-container">
        @if($maintenanceRequests->count() > 0)
            <table class="maintenance-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Property/Unit</th>
                        <th>Tenant</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Requested Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($maintenanceRequests as $request)
                        <tr class="request-row {{ $request->priority == 'urgent' ? 'urgent-row' : '' }}">
                            <td>#{{ $request->id }}</td>
                            <td>
                                <div class="request-title">{{ $request->title }}</div>
                                <div class="request-description-preview">{{ Str::limit($request->description, 50) }}</div>
                            </td>
                            <td>
                                <div class="unit-info">
                                    <strong>{{ $request->unit->apartment->name ?? 'N/A' }}</strong>
                                    <span>Unit {{ $request->unit->unit_number ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="tenant-info">
                                    {{ $request->tenant->tenantProfile->name ?? $request->tenant->email }}
                                </div>
                            </td>
                            <td>
                                <span class="category-badge category-{{ $request->category }}">
                                    <i class="fas fa-{{ $request->category == 'plumbing' ? 'water' : ($request->category == 'electrical' ? 'bolt' : ($request->category == 'hvac' ? 'wind' : 'tools')) }}"></i>
                                    {{ ucfirst($request->category) }}
                                </span>
                            </td>
                            <td>
                                <span class="priority-badge priority-{{ $request->priority }}">
                                    {{ ucfirst($request->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ str_replace('_', '-', $request->status) }}">
                                    {{ ucwords(str_replace('_', ' ', $request->status)) }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $request->requested_date->format('M d, Y') }}</div>
                                @if($request->expected_completion_date)
                                    <small class="text-muted">Due: {{ $request->expected_completion_date->format('M d, Y') }}</small>
                                    @if($request->expected_completion_date->isPast() && $request->status != 'completed')
                                        <br><span class="priority-badge priority-urgent" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">Overdue</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('staff.maintenance.show', $request->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-container">
                {{ $maintenanceRequests->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3>No Maintenance Tasks</h3>
                <p>You have no maintenance tasks assigned to you at the moment.</p>
            </div>
        @endif
    </div>
</div>

<style>
.stat-icon.completed {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
}
</style>
@endsection

