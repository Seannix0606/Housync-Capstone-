@extends('layouts.landlord-app')

@section('title', 'Edit Announcement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('landlord.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('landlord.announcements.index') }}">Announcements</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
                <h4 class="page-title">Edit Announcement</h4>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-8">
            <div class="card" style="border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="fas fa-edit me-2" style="color: #f97316;"></i>Edit Announcement</h5>

                    <form method="POST" action="{{ route('landlord.announcements.update', $announcement->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $announcement->title) }}" required placeholder="Enter announcement title">
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="6" required placeholder="Write your announcement...">{{ old('content', $announcement->content) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="general" {{ old('type', $announcement->type) === 'general' ? 'selected' : '' }}>General</option>
                                    <option value="maintenance" {{ old('type', $announcement->type) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="emergency" {{ old('type', $announcement->type) === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                    <option value="event" {{ old('type', $announcement->type) === 'event' ? 'selected' : '' }}>Event</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="low" {{ old('priority', $announcement->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ old('priority', $announcement->priority) === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority', $announcement->priority) === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority', $announcement->priority) === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="property_id" class="form-label">Property</label>
                                <select class="form-select" id="property_id" name="property_id">
                                    <option value="">All Properties</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}" {{ old('property_id', $announcement->property_id) == $property->id ? 'selected' : '' }}>{{ $property->name }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Leave blank to send to all properties.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="audience" class="form-label">Audience <span class="text-danger">*</span></label>
                                <select class="form-select" id="audience" name="audience" required>
                                    <option value="all_tenants" {{ old('audience', $announcement->audience) === 'all_tenants' ? 'selected' : '' }}>All Tenants</option>
                                    <option value="property_tenants" {{ old('audience', $announcement->audience) === 'property_tenants' ? 'selected' : '' }}>Property Tenants Only</option>
                                    <option value="all_staff" {{ old('audience', $announcement->audience) === 'all_staff' ? 'selected' : '' }}>All Staff</option>
                                    <option value="everyone" {{ old('audience', $announcement->audience) === 'everyone' ? 'selected' : '' }}>Everyone</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="expires_at" class="form-label">Expiry Date <small class="text-muted">(optional)</small></label>
                            <input type="date" class="form-control" id="expires_at" name="expires_at" value="{{ old('expires_at', $announcement->expires_at ? $announcement->expires_at->format('Y-m-d') : '') }}">
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_pinned" name="is_pinned" value="1" {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_pinned">
                                        <i class="fas fa-thumbtack me-1" style="color: #f97316;"></i> Pin this announcement
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="publish_now" name="publish_now" value="1" {{ old('publish_now', $announcement->is_published) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="publish_now">
                                        <i class="fas fa-paper-plane me-1" style="color: #10b981;"></i> Published
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('landlord.announcements.show', $announcement->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Announcement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card" style="border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-info-circle me-2" style="color: #3b82f6;"></i>Announcement Info</h5>
                    <table class="table table-borderless" style="font-size: 0.9rem;">
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $announcement->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Updated:</td>
                            <td>{{ $announcement->updated_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                @if($announcement->is_published)
                                    <span class="badge bg-success">Published</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                        </tr>
                        @if($announcement->published_at)
                        <tr>
                            <td class="text-muted">Published:</td>
                            <td>{{ $announcement->published_at->format('M d, Y') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
