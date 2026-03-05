@php
    $layout = match(auth()->user()->role) {
        'landlord' => 'layouts.landlord-app',
        'staff' => 'layouts.staff-app',
        'super_admin' => 'layouts.super-admin-app',
        default => 'layouts.app',
    };
@endphp

@extends($layout)

@section('title', 'Notifications')

@section('content')
<style>
    .notif-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: .75rem;
    }
    .notif-page-header h1 {
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0;
        color: #1e293b;
    }
    .notif-page-header .btn-mark-all {
        background: #f97316;
        color: #fff;
        border: none;
        padding: .5rem 1.25rem;
        border-radius: 8px;
        font-size: .875rem;
        font-weight: 600;
        cursor: pointer;
        transition: background .2s;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }
    .notif-page-header .btn-mark-all:hover {
        background: #ea580c;
    }
    .notif-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
        overflow: hidden;
    }
    .notif-list-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background .15s;
        text-decoration: none;
        color: inherit;
    }
    .notif-list-item:last-child {
        border-bottom: none;
    }
    .notif-list-item:hover {
        background: #f8fafc;
        text-decoration: none;
        color: inherit;
    }
    .notif-list-item.unread {
        background: #eff6ff;
    }
    .notif-list-item.unread:hover {
        background: #dbeafe;
    }
    .notif-list-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .notif-list-icon.maintenance { background: #fef3c7; color: #d97706; }
    .notif-list-icon.billing     { background: #d1fae5; color: #059669; }
    .notif-list-icon.announcement{ background: #dbeafe; color: #2563eb; }
    .notif-list-icon.general     { background: #f1f5f9; color: #64748b; }
    .notif-list-body {
        flex: 1;
        min-width: 0;
    }
    .notif-list-msg {
        font-size: .925rem;
        color: #334155;
        line-height: 1.45;
        margin-bottom: .25rem;
    }
    .notif-list-item.unread .notif-list-msg {
        font-weight: 600;
        color: #1e293b;
    }
    .notif-list-meta {
        font-size: .78rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
    }
    .notif-list-meta .dot {
        width: 3px;
        height: 3px;
        background: #cbd5e1;
        border-radius: 50%;
    }
    .notif-unread-dot {
        width: 8px;
        height: 8px;
        background: #3b82f6;
        border-radius: 50%;
        flex-shrink: 0;
        align-self: center;
    }
    .notif-empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #94a3b8;
    }
    .notif-empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
        color: #cbd5e1;
    }
    .notif-empty-state p {
        font-size: 1rem;
        margin: 0;
    }
    .notif-pagination {
        display: flex;
        justify-content: center;
        margin-top: 1.5rem;
    }
    .notif-pagination .pagination {
        gap: .25rem;
    }
    .notif-pagination .page-link {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: .85rem;
        padding: .4rem .75rem;
    }
    .notif-pagination .page-item.active .page-link {
        background: #f97316;
        border-color: #f97316;
        color: #fff;
    }
    .notif-pagination .page-link:hover {
        background: #fff7ed;
        color: #ea580c;
    }

    /* Dark mode overrides */
    body.dark-mode .notif-page-header h1 { color: #f1f5f9; }
    body.dark-mode .notif-card { background: #1e293b; box-shadow: 0 1px 4px rgba(0,0,0,.2); }
    body.dark-mode .notif-list-item { border-bottom-color: #334155; }
    body.dark-mode .notif-list-item:hover { background: #334155; }
    body.dark-mode .notif-list-item.unread { background: #1e3a5f; }
    body.dark-mode .notif-list-item.unread:hover { background: #1e4578; }
    body.dark-mode .notif-list-msg { color: #cbd5e1; }
    body.dark-mode .notif-list-item.unread .notif-list-msg { color: #f1f5f9; }
    body.dark-mode .notif-list-meta { color: #64748b; }
    body.dark-mode .notif-empty-state { color: #64748b; }
    body.dark-mode .notif-empty-state i { color: #475569; }
    body.dark-mode .notif-pagination .page-link { background: #1e293b; border-color: #334155; color: #94a3b8; }
    body.dark-mode .notif-pagination .page-item.active .page-link { background: #f97316; border-color: #f97316; color: #fff; }
</style>

<div class="notif-page-header">
    <h1><i class="fas fa-bell" style="margin-right:.5rem;color:#f97316"></i>Notifications</h1>
    @if($notifications->count() > 0)
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn-mark-all">
                <i class="fas fa-check-double"></i> Mark All Read
            </button>
        </form>
    @endif
</div>

<div class="notif-card">
    @forelse($notifications as $notification)
        @php
            $data = $notification->data;
            $type = $data['type'] ?? 'general';
            $iconClass = match(true) {
                str_contains($type, 'maintenance') => 'maintenance',
                str_contains($type, 'bill') || str_contains($type, 'payment') => 'billing',
                str_contains($type, 'announcement') => 'announcement',
                default => 'general',
            };
            $iconChar = match($iconClass) {
                'maintenance' => 'fa-wrench',
                'billing' => 'fa-credit-card',
                'announcement' => 'fa-bullhorn',
                default => 'fa-bell',
            };
            $isUnread = is_null($notification->read_at);
        @endphp

        <a href="#"
           class="notif-list-item {{ $isUnread ? 'unread' : '' }}"
           onclick="event.preventDefault(); document.getElementById('notif-form-{{ $notification->id }}').submit();">
            <div class="notif-list-icon {{ $iconClass }}">
                <i class="fas {{ $iconChar }}"></i>
            </div>
            <div class="notif-list-body">
                <div class="notif-list-msg">{{ $data['message'] ?? 'New notification' }}</div>
                <div class="notif-list-meta">
                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                    <span class="dot"></span>
                    <span>{{ $notification->created_at->format('M d, Y \a\t g:i A') }}</span>
                </div>
            </div>
            @if($isUnread)
                <div class="notif-unread-dot" title="Unread"></div>
            @endif
        </a>
        <form id="notif-form-{{ $notification->id }}"
              action="{{ route('notifications.mark-read', $notification->id) }}"
              method="POST" style="display:none">
            @csrf
        </form>
    @empty
        <div class="notif-empty-state">
            <i class="fas fa-bell-slash"></i>
            <p>You're all caught up — no notifications yet.</p>
        </div>
    @endforelse
</div>

@if($notifications->hasPages())
    <div class="notif-pagination">
        {{ $notifications->links() }}
    </div>
@endif
@endsection
