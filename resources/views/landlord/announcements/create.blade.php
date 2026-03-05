@extends('layouts.landlord-app')

@section('title', 'Create Announcement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('landlord.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('landlord.announcements.index') }}">Announcements</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
                <h4 class="page-title">Create Announcement</h4>
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
                    <h5 class="card-title mb-4"><i class="fas fa-bullhorn me-2" style="color: #f97316;"></i>New Announcement</h5>

                    <form method="POST" action="{{ route('landlord.announcements.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required placeholder="Enter announcement title">
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="6" required placeholder="Write your announcement...">{{ old('content') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="general" {{ old('type') === 'general' ? 'selected' : '' }}>General</option>
                                    <option value="maintenance" {{ old('type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="emergency" {{ old('type') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                    <option value="event" {{ old('type') === 'event' ? 'selected' : '' }}>Event</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="property_id" class="form-label">Property</label>
                                <select class="form-select" id="property_id" name="property_id">
                                    <option value="">All Properties</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>{{ $property->name }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Leave blank to send to all properties.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="audience" class="form-label">Audience <span class="text-danger">*</span></label>
                                <select class="form-select" id="audience" name="audience" required>
                                    <option value="all_tenants" {{ old('audience') === 'all_tenants' ? 'selected' : '' }}>All Tenants</option>
                                    <option value="property_tenants" {{ old('audience') === 'property_tenants' ? 'selected' : '' }}>Property Tenants Only</option>
                                    <option value="all_staff" {{ old('audience') === 'all_staff' ? 'selected' : '' }}>All Staff</option>
                                    <option value="everyone" {{ old('audience') === 'everyone' ? 'selected' : '' }}>Everyone</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="expires_at" class="form-label">Expiry Date <small class="text-muted">(optional)</small></label>
                            <input type="date" class="form-control" id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_pinned" name="is_pinned" value="1" {{ old('is_pinned') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_pinned">
                                        <i class="fas fa-thumbtack me-1" style="color: #f97316;"></i> Pin this announcement
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="publish_now" name="publish_now" value="1" {{ old('publish_now', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="publish_now">
                                        <i class="fas fa-paper-plane me-1" style="color: #10b981;"></i> Publish immediately
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('landlord.announcements.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Create Announcement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card" style="border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-info-circle me-2" style="color: #3b82f6;"></i>Tips</h5>
                    <ul class="list-unstyled mb-0" style="color: #64748b; font-size: 0.9rem;">
                        <li class="mb-2"><i class="fas fa-check-circle me-2" style="color: #10b981;"></i>Use <strong>Emergency</strong> type for urgent notices.</li>
                        <li class="mb-2"><i class="fas fa-check-circle me-2" style="color: #10b981;"></i>Pin important announcements to keep them at the top.</li>
                        <li class="mb-2"><i class="fas fa-check-circle me-2" style="color: #10b981;"></i>Set an expiry date for time-sensitive announcements.</li>
                        <li class="mb-2"><i class="fas fa-check-circle me-2" style="color: #10b981;"></i>Choose the right audience to target your message.</li>
                        <li><i class="fas fa-check-circle me-2" style="color: #10b981;"></i>Uncheck "Publish immediately" to save as draft.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
