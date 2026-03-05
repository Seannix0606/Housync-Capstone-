@extends('layouts.app')

@section('title', $announcement->title)

@section('content')
<div class="announcement-detail-container">
    <a href="{{ route('tenant.announcements.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back to Announcements
    </a>

    <div class="announcement-detail-card">
        <div class="detail-header">
            <div class="detail-badges">
                <span class="type-badge type-{{ $announcement->type }}">
                    <i class="fas fa-{{ $announcement->type === 'emergency' ? 'exclamation-triangle' : ($announcement->type === 'maintenance' ? 'tools' : ($announcement->type === 'event' ? 'calendar-alt' : 'info-circle')) }}"></i>
                    {{ ucfirst($announcement->type) }}
                </span>
                <span class="priority-badge priority-{{ $announcement->priority }}">
                    {{ ucfirst($announcement->priority) }} Priority
                </span>
                @if($announcement->is_pinned)
                    <span class="pinned-badge">
                        <i class="fas fa-thumbtack"></i> Pinned
                    </span>
                @endif
            </div>
            <h1 class="detail-title">{{ $announcement->title }}</h1>
            <div class="detail-meta">
                <span><i class="far fa-clock"></i> Published {{ $announcement->published_at->format('F d, Y \a\t g:i A') }}</span>
                @if($announcement->property)
                    <span><i class="fas fa-building"></i> {{ $announcement->property->name }}</span>
                @endif
                @if($announcement->author)
                    <span><i class="fas fa-user"></i> {{ $announcement->author->name }}</span>
                @endif
            </div>
        </div>

        <div class="detail-content">
            {!! nl2br(e($announcement->content)) !!}
        </div>

        @if($announcement->expires_at)
            <div class="detail-footer">
                <i class="fas fa-hourglass-half"></i> This announcement expires on {{ $announcement->expires_at->format('F d, Y') }}
            </div>
        @endif
    </div>
</div>

<style>
.announcement-detail-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 1rem;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    margin-bottom: 1.25rem;
    transition: color 0.15s;
}

.btn-back:hover {
    color: #764ba2;
    text-decoration: none;
}

.announcement-detail-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
    overflow: hidden;
}

.detail-header {
    padding: 1.75rem 2rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
}

.detail-badges {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.type-badge.type-emergency {
    background: #fef2f2;
    color: #dc2626;
}

.type-badge.type-maintenance {
    background: #fffbeb;
    color: #d97706;
}

.type-badge.type-event {
    background: #eff6ff;
    color: #2563eb;
}

.type-badge.type-general {
    background: #f1f5f9;
    color: #64748b;
}

.priority-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.7rem;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
}

.priority-badge.priority-urgent {
    background: #fef2f2;
    color: #dc2626;
}

.priority-badge.priority-high {
    background: #fffbeb;
    color: #d97706;
}

.priority-badge.priority-normal {
    background: #eff6ff;
    color: #2563eb;
}

.priority-badge.priority-low {
    background: #f1f5f9;
    color: #64748b;
}

.pinned-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.7rem;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
    background: #f0ecff;
    color: #667eea;
}

.detail-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.75rem 0;
    line-height: 1.3;
}

.detail-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem;
    font-size: 0.88rem;
    color: #94a3b8;
}

.detail-meta span {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.detail-content {
    padding: 2rem;
    font-size: 1rem;
    line-height: 1.8;
    color: #334155;
}

.detail-footer {
    padding: 1rem 2rem;
    background: #fffbeb;
    color: #92400e;
    font-size: 0.88rem;
    border-top: 1px solid #fef3c7;
}

.detail-footer i {
    margin-right: 0.35rem;
}
</style>
@endsection
