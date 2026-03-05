@extends('layouts.landlord-app')

@section('title', 'View Announcement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('landlord.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('landlord.announcements.index') }}">Announcements</a></li>
                        <li class="breadcrumb-item active">View</li>
                    </ol>
                </div>
                <h4 class="page-title">Announcement Details</h4>
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

    <div class="row">
        <div class="col-xl-8">
            <div class="card" style="border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-1" style="font-weight: 700; color: #1e293b;">{{ $announcement->title }}</h4>
                            <div class="d-flex gap-2 flex-wrap mt-2">
                                @php
                                    $typeBadge = match($announcement->type) {
                                        'general' => 'primary',
                                        'maintenance' => 'warning',
                                        'emergency' => 'danger',
                                        'event' => 'info',
                                        default => 'secondary'
                                    };
                                    $priorityBadge = match($announcement->priority) {
                                        'low' => 'secondary',
                                        'normal' => 'primary',
                                        'high' => 'warning',
                                        'urgent' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $typeBadge }}"><i class="fas fa-tag me-1"></i>{{ ucfirst($announcement->type) }}</span>
                                <span class="badge bg-{{ $priorityBadge }}"><i class="fas fa-flag me-1"></i>{{ ucfirst($announcement->priority) }}</span>
                                @if($announcement->is_published)
                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Published</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-file-alt me-1"></i>Draft</span>
                                @endif
                                @if($announcement->is_pinned)
                                    <span class="badge" style="background: #fff7ed; color: #ea580c;"><i class="fas fa-thumbtack me-1"></i>Pinned</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4" style="line-height: 1.7; color: #334155;">
                        {!! nl2br(e($announcement->content)) !!}
                    </div>

                    <hr>

                    <div class="row" style="font-size: 0.9rem; color: #64748b;">
                        <div class="col-md-6">
                            <p class="mb-2"><i class="fas fa-building me-2"></i><strong>Property:</strong> {{ $announcement->property ? $announcement->property->name : 'All Properties' }}</p>
                            <p class="mb-2"><i class="fas fa-users me-2"></i><strong>Audience:</strong> {{ str_replace('_', ' ', ucfirst($announcement->audience)) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><i class="fas fa-calendar-plus me-2"></i><strong>Created:</strong> {{ $announcement->created_at->format('M d, Y \a\t h:i A') }}</p>
                            @if($announcement->published_at)
                                <p class="mb-2"><i class="fas fa-paper-plane me-2"></i><strong>Published:</strong> {{ $announcement->published_at->format('M d, Y \a\t h:i A') }}</p>
                            @endif
                            @if($announcement->expires_at)
                                <p class="mb-2"><i class="fas fa-calendar-times me-2"></i><strong>Expires:</strong> {{ $announcement->expires_at->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card" style="border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-cogs me-2" style="color: #f97316;"></i>Actions</h5>

                    <div class="d-grid gap-2">
                        @if(!$announcement->is_published)
                            <form method="POST" action="{{ route('landlord.announcements.publish', $announcement->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Publish this announcement?')">
                                    <i class="fas fa-paper-plane me-1"></i> Publish Now
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('landlord.announcements.edit', $announcement->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i> Edit Announcement
                        </a>

                        <form method="POST" action="{{ route('landlord.announcements.destroy', $announcement->id) }}" id="deleteForm">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Are you sure you want to delete this announcement? This action cannot be undone.')">
                                <i class="fas fa-trash-alt me-1"></i> Delete Announcement
                            </button>
                        </form>

                        <a href="{{ route('landlord.announcements.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Announcements
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
