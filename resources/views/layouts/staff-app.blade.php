<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Staff Portal')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body { min-height: 100%; background-color: #f8fafc; }
        body { font-family: 'Inter', sans-serif; color: #1e293b; }
        .dashboard-container { display: flex; min-height: 100vh; }
        aside.sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            width: 260px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            transition: width .2s cubic-bezier(.4,0,.2,1);
        }
        .dashboard-container.collapsed aside.sidebar { width: 72px; }
        .sidebar-header { padding: 1.25rem 1.5rem; font-size: 1.2rem; font-weight: bold; border-bottom: 1px solid rgba(255,255,255,0.05); letter-spacing: .5px; display: flex; align-items: center; gap: .75rem; }
        .sidebar-header .portal-title-label { transition: opacity .2s; }
        .dashboard-container.collapsed .portal-title-label { opacity: 0; width: 0; overflow: hidden; }
        .sidebar-hamburger { background: transparent; color: #fff; border: none; font-size: 1.4rem; margin-right: .75rem; outline: none; cursor: pointer; }
        nav.sidebar-nav { flex: 1 1 auto; padding: 1rem 0; display: flex; flex-direction: column; gap: .25rem; }
        nav.sidebar-nav a.nav-link { color: #fff; display: flex; align-items: center; gap: 1rem; border-radius: 8px 0 0 8px; padding: 0.8rem 1.25rem; font-size: 1.08rem; border-left: 4px solid transparent; transition: background .2s, border .2s, color .2s; font-weight: 500; text-decoration: none; }
        nav.sidebar-nav a.nav-link.active, nav.sidebar-nav a.nav-link:hover { background: rgba(255,255,255,0.08); border-left: 4px solid #fff; color: #fff; text-decoration: none; }
        nav.sidebar-nav .nav-icon { min-width: 22px; text-align: center; font-size: 1.2em; }
        nav.sidebar-nav .nav-label { transition: opacity .2s, width .2s; }
        .dashboard-container.collapsed nav.sidebar-nav .nav-label { opacity: 0; width: 0; overflow: hidden; }
        .sidebar-footer { padding: 1.25rem 1.5rem .8rem; border-top: 1px solid rgba(255,255,255,0.05); }
        .main-content { flex: 1 1 0%; background: #f8fafc; min-width: 0; padding: 2rem; transition: none; }
        @media (max-width: 900px) { aside.sidebar { position: fixed; left: 0; height: 100vh; z-index: 1040; } .main-content { padding: 1.5rem .5rem .5rem 1rem; } }
        @media (max-width: 600px) { .main-content { padding: .7rem .2rem; } aside.sidebar { width: 90px; } .dashboard-container.collapsed aside.sidebar { width: 56px; } }

        /* Profile dropdown and topbar */
        .topbar{display:flex;justify-content:flex-end;align-items:center;margin-bottom:1rem;padding:0 1rem}
        .profile-btn{display:flex;align-items:center;gap:.6rem;background:#fff;border:1px solid #e2e8f0;border-radius:9999px;padding:.35rem .6rem;cursor:pointer;box-shadow:0 1px 2px rgba(0,0,0,.04)}
        .profile-avatar{width:32px;height:32px;border-radius:50%;background:#667eea;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600}
        .dropdown{position:relative}
        .dropdown-menu{position:absolute;right:0;top:calc(100% + 8px);background:#fff;border:1px solid #e2e8f0;border-radius:10px;min-width:220px;box-shadow:0 8px 24px rgba(0,0,0,.08);display:none;z-index:1050;padding:.4rem}
        .dropdown-menu.show{display:block}
        .dropdown-item{display:flex;align-items:center;gap:.6rem;padding:.6rem .8rem;border-radius:8px;color:#1e293b;text-decoration:none}
        .dropdown-item:hover{background:#f8fafc;text-decoration:none}

        /* ========== DARK MODE STYLES ========== */
        body.dark-mode, body.dark-mode html {
            background-color: #0f172a !important;
            color: #e2e8f0;
        }

        body.dark-mode .main-content {
            background: #0f172a !important;
            color: #e2e8f0;
        }

        body.dark-mode .profile-btn {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0;
        }

        body.dark-mode .dropdown-menu {
            background: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .dropdown-item {
            color: #e2e8f0 !important;
        }

        body.dark-mode .dropdown-item:hover {
            background: #334155 !important;
        }

        body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, body.dark-mode h4, body.dark-mode h5 {
            color: #f1f5f9 !important;
        }

        body.dark-mode p {
            color: #94a3b8;
        }

        body.dark-mode .page-section,
        body.dark-mode .content-card,
        body.dark-mode .card,
        body.dark-mode .welcome-section,
        body.dark-mode .stat-card,
        body.dark-mode .property-summary,
        body.dark-mode .activity-section,
        body.dark-mode .quick-actions {
            background: #1e293b !important;
            color: #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .stat-card .stat-value {
            color: #f1f5f9 !important;
        }

        body.dark-mode .stat-card .stat-label {
            color: #94a3b8 !important;
        }

        body.dark-mode .data-table,
        body.dark-mode table {
            color: #e2e8f0;
        }

        body.dark-mode .data-table th,
        body.dark-mode table th {
            background: #0f172a !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }

        body.dark-mode .data-table td,
        body.dark-mode table td {
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .data-table tbody tr:hover,
        body.dark-mode table tbody tr:hover {
            background: #0f172a !important;
        }

        body.dark-mode input[type="text"],
        body.dark-mode input[type="email"],
        body.dark-mode input[type="number"],
        body.dark-mode input[type="password"],
        body.dark-mode input[type="tel"],
        body.dark-mode input[type="date"],
        body.dark-mode textarea,
        body.dark-mode select,
        body.dark-mode .form-control {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode input:focus,
        body.dark-mode textarea:focus,
        body.dark-mode select:focus,
        body.dark-mode .form-control:focus {
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.1) !important;
        }

        body.dark-mode .form-label,
        body.dark-mode label {
            color: #e2e8f0 !important;
        }

        body.dark-mode .alert-success {
            background: #064e3b !important;
            border-color: #065f46 !important;
            color: #6ee7b7 !important;
        }

        body.dark-mode .alert-danger,
        body.dark-mode .alert-error {
            background: #7f1d1d !important;
            border-color: #991b1b !important;
            color: #fca5a5 !important;
        }

        body.dark-mode .alert-warning {
            background: #78350f !important;
            border-color: #92400e !important;
            color: #fbbf24 !important;
        }

        body.dark-mode .alert-info {
            background: #0c4a6e !important;
            border-color: #075985 !important;
            color: #7dd3fc !important;
        }

        body.dark-mode .modal-content {
            background: #1e293b !important;
            color: #e2e8f0;
        }

        body.dark-mode .modal-header {
            border-color: #334155 !important;
        }

        body.dark-mode .modal-footer {
            border-color: #334155 !important;
        }

        body.dark-mode .btn-close {
            filter: invert(1);
        }

        body.dark-mode .pagination a,
        body.dark-mode .pagination span {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .pagination a:hover {
            background: #334155 !important;
        }

        body.dark-mode .text-muted {
            color: #94a3b8 !important;
        }

        body.dark-mode .section-title {
            color: #f1f5f9 !important;
        }

        /* Dark mode toggle button */
        .dark-mode-toggle {
            background: transparent;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 0.5rem;
            cursor: pointer;
            color: #64748b;
            margin-right: 1rem;
            transition: all 0.2s;
        }

        .dark-mode-toggle:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        body.dark-mode .dark-mode-toggle {
            border-color: #475569;
            color: #fbbf24;
        }

        body.dark-mode .dark-mode-toggle:hover {
            background: #334155;
            color: #fbbf24;
        }

        /* ========== Chat / Messages Dark Mode ========== */
        body.dark-mode .chat-card {
            background: #1e293b !important;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .chat-header {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        body.dark-mode .chat-messages {
            background: #0f172a !important;
        }

        body.dark-mode .message.received .message-content {
            background: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .chat-input-area {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        body.dark-mode .chat-input,
        body.dark-mode .chat-input-area textarea {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .chat-input::placeholder {
            color: #64748b !important;
        }

        /* ========== Input/Textarea Autofill & Placeholder ========== */
        body.dark-mode input:-webkit-autofill,
        body.dark-mode input:-webkit-autofill:hover,
        body.dark-mode input:-webkit-autofill:focus,
        body.dark-mode input:-webkit-autofill:active,
        body.dark-mode textarea:-webkit-autofill,
        body.dark-mode textarea:-webkit-autofill:hover,
        body.dark-mode textarea:-webkit-autofill:focus,
        body.dark-mode textarea:-webkit-autofill:active {
            -webkit-text-fill-color: #e2e8f0 !important;
            -webkit-box-shadow: 0 0 0 1000px #0f172a inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        body.dark-mode input::placeholder,
        body.dark-mode textarea::placeholder {
            color: #64748b !important;
        }

        /* Override inline white backgrounds in dark mode */
        body.dark-mode [style*="background: white"],
        body.dark-mode [style*="background:white"],
        body.dark-mode [style*="background: #fff"],
        body.dark-mode [style*="background:#fff"] {
            background: #1e293b !important;
        }

        body.dark-mode [style*="background: #f8fafc"],
        body.dark-mode [style*="background:#f8fafc"],
        body.dark-mode [style*="background: #f1f5f9"],
        body.dark-mode [style*="background:#f1f5f9"] {
            background: #0f172a !important;
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="dashboard-container" id="dashboardContainer">
    <aside class="sidebar">
        <div class="sidebar-header">
            <button class="sidebar-hamburger" id="sidebarCollapseBtn" aria-label="Toggle navigation"><i class="fas fa-bars"></i></button>
            <span class="portal-title-label">Staff Portal</span>
        </div>
        <nav class="sidebar-nav">
            <a class="nav-link{{ request()->routeIs('staff.dashboard') ? ' active' : '' }}" href="{{ route('staff.dashboard') }}">
                <span class="nav-icon"><i class="fas fa-home"></i></span> <span class="nav-label">Dashboard</span>
            </a>
            <a class="nav-link{{ request()->routeIs('staff.maintenance*') ? ' active' : '' }}" href="{{ route('staff.maintenance.index') }}"><span class="nav-icon"><i class="fas fa-tools"></i></span> <span class="nav-label">Maintenance Requests</span></a>
            <a class="nav-link{{ request()->routeIs('staff.profile') ? ' ' : '' }}" href="{{ route('staff.profile') }}"><span class="nav-icon"><i class="fas fa-calendar"></i></span> <span class="nav-label">Work Schedule</span></a>
            <a class="nav-link{{ request()->routeIs('staff.chat*') ? ' active' : '' }}" href="{{ route('staff.chat') }}">
                <span class="nav-icon"><i class="fas fa-comments"></i></span> 
                <span class="nav-label">Messages</span>
                @if(auth()->user()->total_unread_messages > 0)
                    <span style="background:#ef4444;color:#fff;border-radius:999px;padding:2px 6px;font-size:0.7rem;margin-left:6px;">{{ auth()->user()->total_unread_messages }}</span>
                @endif
            </a>
            <a class="nav-link{{ request()->routeIs('staff.announcements*') ? ' active' : '' }}" href="{{ route('staff.announcements.index') }}"><span class="nav-icon"><i class="fas fa-bullhorn"></i></span> <span class="nav-label">Announcements</span></a>
            <a class="nav-link{{ request()->routeIs('staff.profile') ? ' active' : '' }}" href="{{ route('staff.profile') }}">
                <span class="nav-icon"><i class="fas fa-user"></i></span> <span class="nav-label">Profile</span>
            </a>
        </nav>
        <div class="sidebar-footer mt-auto">
            <a href="{{ route('logout') }}" class="btn btn-danger w-100" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt me-1"></i> <span class="nav-label">Logout</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
    </aside>
    <main class="main-content">
        <div class="topbar">
            @include('partials.notification-bell')
            <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode">
                <i class="fas fa-moon" id="darkModeIcon"></i>
            </button>
            <div class="dropdown" id="stProfileDropdown">
                <div class="profile-btn" id="stProfileBtn">
                    <div class="profile-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down" style="font-size:.85rem;color:#64748b"></i>
                </div>
                <div class="dropdown-menu" id="stDropdownMenu">
                    <a href="{{ route('staff.profile') }}" class="dropdown-item"><i class="fas fa-user"></i> Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item" style="width:100%;background:none;border:none;text-align:left"><i class="fas fa-sign-out-alt"></i> Logout</button>
                    </form>
                </div>
            </div>
        </div>
        @yield('content')
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sidebarCollapseBtn').onclick = function() {
        var cont = document.getElementById('dashboardContainer');
        if (cont) {
            cont.classList.toggle('collapsed');
            try { localStorage.setItem('staffSidebarCollapsed', cont.classList.contains('collapsed') ? '1' : '0'); } catch (e) {}
        }
    };
    // Respect stored state
    (function() {
        var cont = document.getElementById('dashboardContainer');
        try { if (localStorage.getItem('staffSidebarCollapsed') === '1') cont.classList.add('collapsed'); } catch (e) {}
    })();

    // Profile dropdown
    (function(){
        var btn=document.getElementById('stProfileBtn');
        var menu=document.getElementById('stDropdownMenu');
        if(btn&&menu){
            btn.addEventListener('click',function(e){ e.stopPropagation(); menu.classList.toggle('show'); });
            document.addEventListener('click',function(){ menu.classList.remove('show'); });
        }
    })();

    // Dark Mode Toggle
    (function() {
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = document.getElementById('darkModeIcon');

        function applyDarkMode(isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
                document.documentElement.setAttribute('data-theme', 'dark');
                if (darkModeIcon) {
                    darkModeIcon.classList.remove('fa-moon');
                    darkModeIcon.classList.add('fa-sun');
                }
            } else {
                document.body.classList.remove('dark-mode');
                document.documentElement.setAttribute('data-theme', 'light');
                if (darkModeIcon) {
                    darkModeIcon.classList.remove('fa-sun');
                    darkModeIcon.classList.add('fa-moon');
                }
            }
        }

        // Check saved preference
        const savedDarkMode = localStorage.getItem('darkMode');
        if (savedDarkMode === 'true') {
            applyDarkMode(true);
        }

        // Toggle on click
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', function() {
                const isDark = !document.body.classList.contains('dark-mode');
                applyDarkMode(isDark);
                localStorage.setItem('darkMode', isDark ? 'true' : 'false');
            });
        }

        // Listen for changes from other tabs
        window.addEventListener('storage', function(e) {
            if (e.key === 'darkMode') {
                applyDarkMode(e.newValue === 'true');
            }
        });
    })();
</script>
@stack('scripts')
@yield('scripts')
</body>
</html> 