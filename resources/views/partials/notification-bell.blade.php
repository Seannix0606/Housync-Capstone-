@auth
<style>
.notification-bell{position:relative;display:inline-flex;align-items:center;margin-right:.75rem}
.notification-bell .bell-btn{background:none;border:none;font-size:1.25rem;color:#64748b;cursor:pointer;padding:.4rem;border-radius:50%;transition:background .2s}
.notification-bell .bell-btn:hover{background:#f1f5f9;color:#1e293b}
.notification-bell .badge-dot{position:absolute;top:2px;right:2px;width:10px;height:10px;background:#ef4444;border-radius:50%;border:2px solid #fff}
.notification-bell .badge-num{position:absolute;top:-2px;right:-6px;background:#ef4444;color:#fff;font-size:.65rem;font-weight:700;border-radius:9999px;min-width:18px;height:18px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;padding:0 4px}
.notification-dropdown{position:absolute;right:0;top:calc(100% + 8px);background:#fff;border:1px solid #e2e8f0;border-radius:12px;width:380px;max-height:460px;box-shadow:0 12px 32px rgba(0,0,0,.12);display:none;z-index:1060;overflow:hidden}
.notification-dropdown.show{display:block}
.notification-dropdown .notif-header{display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #f1f5f9}
.notification-dropdown .notif-header h6{margin:0;font-size:.875rem;font-weight:600;color:#1e293b}
.notification-dropdown .notif-header a{font-size:.75rem;color:#f97316;text-decoration:none}
.notification-dropdown .notif-header a:hover{text-decoration:underline}
.notification-dropdown .notif-list{max-height:360px;overflow-y:auto}
.notification-dropdown .notif-item{display:flex;align-items:flex-start;gap:.75rem;padding:.75rem 1rem;border-bottom:1px solid #f8fafc;transition:background .15s;text-decoration:none;color:inherit}
.notification-dropdown .notif-item:hover{background:#f8fafc;text-decoration:none}
.notification-dropdown .notif-item.unread{background:#eff6ff}
.notification-dropdown .notif-item.unread:hover{background:#dbeafe}
.notification-dropdown .notif-icon{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.875rem;flex-shrink:0}
.notification-dropdown .notif-icon.maintenance{background:#fef3c7;color:#d97706}
.notification-dropdown .notif-icon.billing{background:#d1fae5;color:#059669}
.notification-dropdown .notif-icon.announcement{background:#dbeafe;color:#2563eb}
.notification-dropdown .notif-icon.general{background:#f1f5f9;color:#64748b}
.notification-dropdown .notif-body{flex:1;min-width:0}
.notification-dropdown .notif-body .notif-msg{font-size:.8rem;color:#334155;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.notification-dropdown .notif-body .notif-time{font-size:.7rem;color:#94a3b8;margin-top:2px}
.notification-dropdown .notif-empty{text-align:center;padding:2rem 1rem;color:#94a3b8;font-size:.85rem}
.notification-dropdown .notif-footer{padding:.5rem;text-align:center;border-top:1px solid #f1f5f9}
.notification-dropdown .notif-footer a{font-size:.8rem;color:#f97316;text-decoration:none;font-weight:500}

body.dark-mode .notification-bell .bell-btn{color:#94a3b8}
body.dark-mode .notification-bell .bell-btn:hover{background:#334155;color:#e2e8f0}
body.dark-mode .notification-dropdown{background:#1e293b;border-color:#334155;box-shadow:0 12px 32px rgba(0,0,0,.3)}
body.dark-mode .notification-dropdown .notif-header{border-bottom-color:#334155}
body.dark-mode .notification-dropdown .notif-header h6{color:#f1f5f9}
body.dark-mode .notification-dropdown .notif-item{border-bottom-color:#1e293b}
body.dark-mode .notification-dropdown .notif-item:hover{background:#334155}
body.dark-mode .notification-dropdown .notif-item.unread{background:#1e3a5f}
body.dark-mode .notification-dropdown .notif-body .notif-msg{color:#cbd5e1}
body.dark-mode .notification-dropdown .notif-body .notif-time{color:#64748b}
body.dark-mode .notification-dropdown .notif-footer{border-top-color:#334155}
</style>

@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
    $recentNotifications = auth()->user()->notifications()->take(10)->get();
@endphp

<div class="notification-bell" id="notificationBellWrapper">
    <button class="bell-btn" id="notificationBellBtn" title="Notifications">
        <i class="fas fa-bell"></i>
        @if($unreadCount > 0)
            <span class="badge-num" id="notifBadge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </button>
    <div class="notification-dropdown" id="notificationDropdown">
        <div class="notif-header">
            <h6>Notifications</h6>
            @if($unreadCount > 0)
                <a href="#" id="markAllReadBtn">Mark all read</a>
            @endif
        </div>
        <div class="notif-list">
            @forelse($recentNotifications as $notification)
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
                @endphp
                <a href="{{ route('notifications.mark-read', $notification->id) }}"
                   class="notif-item {{ is_null($notification->read_at) ? 'unread' : '' }}"
                   onclick="event.preventDefault(); document.getElementById('notif-form-{{ $notification->id }}').submit();">
                    <div class="notif-icon {{ $iconClass }}"><i class="fas {{ $iconChar }}"></i></div>
                    <div class="notif-body">
                        <div class="notif-msg">{{ $data['message'] ?? 'New notification' }}</div>
                        <div class="notif-time">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                </a>
                <form id="notif-form-{{ $notification->id }}" action="{{ route('notifications.mark-read', $notification->id) }}" method="POST" style="display:none">@csrf</form>
            @empty
                <div class="notif-empty"><i class="fas fa-bell-slash" style="font-size:1.5rem;display:block;margin-bottom:.5rem"></i>No notifications yet</div>
            @endforelse
        </div>
        @if($recentNotifications->count() > 0)
        <div class="notif-footer">
            <a href="{{ route('notifications.index') }}">View all notifications</a>
        </div>
        @endif
    </div>
</div>

<form id="markAllReadForm" action="{{ route('notifications.mark-all-read') }}" method="POST" style="display:none">@csrf</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var bellBtn = document.getElementById('notificationBellBtn');
    var dropdown = document.getElementById('notificationDropdown');
    var markAllBtn = document.getElementById('markAllReadBtn');

    if (bellBtn && dropdown) {
        bellBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target) && e.target !== bellBtn) {
                dropdown.classList.remove('show');
            }
        });
    }

    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('markAllReadForm').submit();
        });
    }
});
</script>
@endauth
