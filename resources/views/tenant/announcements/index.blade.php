@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="announcements-container">
    <div class="announcements-header">
        <div class="header-title">
            <h1><i class="fas fa-bullhorn"></i> Announcements</h1>
            <p class="subtitle">Stay up to date with the latest news and updates</p>
        </div>
    </div>

    @if(!$activeAssignment)
        <div class="empty-state">
            <i class="fas fa-building"></i>
            <h3>No Active Assignment</h3>
            <p>You don't have an active unit assignment. Announcements will appear here once you are assigned to a property.</p>
        </div>
    @elseif($announcements->count() > 0)
        <div class="announcements-list">
            @foreach($announcements as $announcement)
                <a href="{{ route('tenant.announcements.show', $announcement->id) }}" class="announcement-card {{ $announcement->is_pinned ? 'pinned' : '' }}">
                    @if($announcement->is_pinned)
                        <div class="pin-indicator">
                            <i class="fas fa-thumbtack"></i>
                        </div>
                    @endif
                    <div class="announcement-card-body">
                        <div class="announcement-top-row">
                            <div class="announcement-badges">
                                <span class="type-badge type-{{ $announcement->type }}">
                                    <i class="fas fa-{{ $announcement->type === 'emergency' ? 'exclamation-triangle' : ($announcement->type === 'maintenance' ? 'tools' : ($announcement->type === 'event' ? 'calendar-alt' : 'info-circle')) }}"></i>
                                    {{ ucfirst($announcement->type) }}
                                </span>
                                <span class="priority-badge priority-{{ $announcement->priority }}">
                                    {{ ucfirst($announcement->priority) }}
                                </span>
                            </div>
                            <span class="announcement-date">
                                <i class="far fa-clock"></i> {{ $announcement->published_at->format('M d, Y') }}
                            </span>
                        </div>
                        <h3 class="announcement-title">{{ $announcement->title }}</h3>
                        <p class="announcement-preview">{{ Str::limit(strip_tags($announcement->content), 160) }}</p>
                        @if($announcement->property)
                            <div class="announcement-property">
                                <i class="fas fa-building"></i> {{ $announcement->property->name }}
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="pagination-container">
            {{ $announcements->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-bullhorn"></i>
            <h3>No Announcements</h3>
            <p>There are no announcements at this time. Check back later!</p>
        </div>
    @endif
</div>

<style>
.announcements-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 1rem;
}

.announcements-header {
    margin-bottom: 1.5rem;
}

.announcements-header .header-title h1 {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.25rem 0;
}

.announcements-header .header-title h1 i {
    color: #667eea;
    margin-right: 0.5rem;
}

.announcements-header .subtitle {
    color: #64748b;
    margin: 0;
    font-size: 0.95rem;
}

.announcements-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.announcement-card {
    display: block;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
    text-decoration: none;
    color: inherit;
    transition: box-shadow 0.2s, transform 0.15s;
    position: relative;
    overflow: hidden;
}

.announcement-card:hover {
    box-shadow: 0 4px 12px rgba(102,126,234,0.18);
    transform: translateY(-2px);
    text-decoration: none;
    color: inherit;
}

.announcement-card.pinned {
    border-left: 4px solid #667eea;
}

.pin-indicator {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    color: #667eea;
    font-size: 0.9rem;
}

.announcement-card-body {
    padding: 1.25rem 1.5rem;
}

.announcement-top-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.announcement-badges {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.7rem;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: capitalize;
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
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-size: 0.75rem;
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

.announcement-date {
    font-size: 0.85rem;
    color: #94a3b8;
}

.announcement-date i {
    margin-right: 0.25rem;
}

.announcement-title {
    font-size: 1.15rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 0.5rem 0;
}

.announcement-preview {
    color: #64748b;
    font-size: 0.92rem;
    line-height: 1.6;
    margin: 0;
}

.announcement-property {
    margin-top: 0.75rem;
    font-size: 0.85rem;
    color: #94a3b8;
}

.announcement-property i {
    margin-right: 0.3rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.empty-state i {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
    display: block;
}

.empty-state h3 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #94a3b8;
    max-width: 400px;
    margin: 0 auto;
}

.pagination-container {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}
</style>
@endsection
