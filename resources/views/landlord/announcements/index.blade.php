@extends('layouts.landlord-app')

@section('title', 'Announcements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('landlord.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Announcements</li>
                    </ol>
                </div>
                <h4 class="page-title">Announcements</h4>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card" style="border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h5 class="card-title mb-0"><i class="fas fa-bullhorn me-2" style="color: #f97316;"></i>All Announcements</h5>
                <a href="{{ route('landlord.announcements.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> New Announcement
                </a>
            </div>

            <form method="GET" action="{{ route('landlord.announcements.index') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>General</option>
                        <option value="maintenance" {{ request('type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="emergency" {{ request('type') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                        <option value="event" {{ request('type') === 'event' ? 'selected' : '' }}>Event</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="property_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Properties</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>{{ $property->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if($announcements->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Property</th>
                                <th>Status</th>
                                <th>Pinned</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($announcements as $announcement)
                                <tr>
                                    <td>
                                        <a href="{{ route('landlord.announcements.show', $announcement->id) }}" style="color: #1e293b; font-weight: 500; text-decoration: none;">
                                            {{ Str::limit($announcement->title, 40) }}
                                        </a>
                                    </td>
                                    <td>
                                        @php
                                            $typeBadge = match($announcement->type) {
                                                'general' => 'primary',
                                                'maintenance' => 'warning',
                                                'emergency' => 'danger',
                                                'event' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $typeBadge }}">{{ ucfirst($announcement->type) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $priorityBadge = match($announcement->priority) {
                                                'low' => 'secondary',
                                                'normal' => 'primary',
                                                'high' => 'warning',
                                                'urgent' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $priorityBadge }}">{{ ucfirst($announcement->priority) }}</span>
                                    </td>
                                    <td>{{ $announcement->property ? $announcement->property->name : 'All Properties' }}</td>
                                    <td>
                                        @if($announcement->is_published)
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Published</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="fas fa-file-alt me-1"></i>Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($announcement->is_pinned)
                                            <i class="fas fa-thumbtack" style="color: #f97316;" title="Pinned"></i>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ $announcement->created_at->format('M d, Y') }}</small></td>
                                    <td class="text-end">
                                        <a href="{{ route('landlord.announcements.show', $announcement->id) }}" class="btn btn-sm btn-outline-primary me-1" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('landlord.announcements.edit', $announcement->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $announcements->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bullhorn" style="font-size: 3rem; color: #cbd5e1;"></i>
                    <h5 class="mt-3" style="color: #64748b;">No Announcements Yet</h5>
                    <p class="text-muted">Create your first announcement to keep your tenants informed.</p>
                    <a href="{{ route('landlord.announcements.create') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus me-1"></i> Create Announcement
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
