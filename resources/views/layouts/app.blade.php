<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tenant Portal')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            min-height: 100%;
            background-color: #f8fafc;
        }
        body {
            font-family: 'Inter', sans-serif;
            color: #1e293b;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        aside.sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            width: 260px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            transition: width .2s cubic-bezier(.4,0,.2,1);
        }
        .dashboard-container.collapsed aside.sidebar {
            width: 72px;
        }
        .sidebar-header {
            padding: 1.25rem 1.5rem;
            font-size: 1.2rem;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            letter-spacing: .5px;
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .sidebar-header .portal-title-label {
            transition: opacity .2s;
        }
        .dashboard-container.collapsed .portal-title-label {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        .sidebar-hamburger {
            background: transparent;
            color: #fff;
            border: none;
            font-size: 1.4rem;
            margin-right: .75rem;
            outline: none;
            cursor: pointer;
        }
        nav.sidebar-nav {
            flex: 1 1 auto;
            padding: 1rem 0;
            display: flex;
            flex-direction: column;
            gap: .25rem;
        }
        nav.sidebar-nav a.nav-link {
            color: #fff;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-radius: 8px 0 0 8px;
            padding: 0.8rem 1.25rem;
            font-size: 1.08rem;
            border-left: 4px solid transparent;
            transition: background .2s, border .2s, color .2s;
            font-weight: 500;
            text-decoration: none;
        }
        nav.sidebar-nav a.nav-link.active, nav.sidebar-nav a.nav-link:hover {
            background: rgba(255,255,255,0.08);
            border-left: 4px solid #fff;
            color: #fff;
            text-decoration: none;
        }
        nav.sidebar-nav .nav-icon {
            min-width: 22px;
            text-align: center;
            font-size: 1.2em;
        }
        nav.sidebar-nav .nav-label {
            transition: opacity .2s, width .2s;
        }
        .dashboard-container.collapsed nav.sidebar-nav .nav-label {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        .sidebar-footer { display:none; }
        .main-content {
            flex: 1 1 0%;
            background: #f8fafc;
            min-width: 0;
            padding: 2rem;
            transition: none;
        }
        /* Profile dropdown */
        .topbar{display:flex;justify-content:flex-end;align-items:center;margin-bottom:1rem}
        .profile-btn{display:flex;align-items:center;gap:.6rem;background:#fff;border:1px solid #e2e8f0;border-radius:9999px;padding:.35rem .6rem;cursor:pointer;box-shadow:0 1px 2px rgba(0,0,0,.04)}
        .profile-avatar{width:32px;height:32px;border-radius:50%;background:#6366f1;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600}
        .dropdown{position:relative}
        .dropdown-menu{position:absolute;right:0;top:calc(100% + 8px);background:#fff;border:1px solid #e2e8f0;border-radius:10px;min-width:220px;box-shadow:0 8px 24px rgba(0,0,0,.08);display:none;z-index:1050;padding:.4rem}
        .dropdown-menu.show{display:block}
        .dropdown-item{display:flex;align-items:center;gap:.6rem;padding:.6rem .8rem;border-radius:8px;color:#1e293b;text-decoration:none}
        .dropdown-item:hover{background:#f8fafc;text-decoration:none}
        /* --- GLOBAL DASHBOARD STYLES (copied/adjusted from landlord-app) --- */
        .main-content .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .main-content .content-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }
        .main-content .user-profile {
            display: flex;
            align-items: center;
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .main-content .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, #818cf8, #a21caf);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600; font-size: 125%; margin-right: 0.9rem;
        }
        .main-content .user-info h3 {
            font-size: 0.93rem;
            font-weight: 600;
            color: #1e293b;
        }
        .main-content .user-info p { font-size: 0.75rem; color: #64748b; }
        .main-content .welcome-section {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.07);
            border-left: 4px solid #818cf8;
        }
        .main-content .welcome-section h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: .6rem;
        }
        .main-content .welcome-section p { color: #64748b; font-size: 1rem; }
        .main-content .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .main-content .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border-left: 4px solid #818cf8;
        }
        .main-content .stat-card .stat-value { font-size: 2rem; font-weight: 700; color: #1e293b; margin-bottom: 0.2rem; }
        .main-content .stat-card .stat-label { color: #64748b; font-size: 0.92rem; margin-bottom: 0.5rem; }
        .main-content .stat-card .stat-sublabel { font-size: 0.78rem; color: #94a3b8; }
        .main-content .stat-card.revenue-card { border-left-color: #10b981; background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); }
        .main-content .revenue-value { color: #059669; }
        .main-content .property-summary {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        .main-content .occupancy-rate {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem; background: #f5f7fa; border-radius: 0.5rem;
            border-left: 4px solid #818cf8;
        }
        .main-content .occupancy-percentage { font-size: 2rem; font-weight: 700; color: #818cf8; }
        .main-content .occupancy-label { font-size: 0.875rem; color: #64748b; }
        .main-content .badge-count { background: #ef4444; color: white; border-radius: 9999px; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600; margin-left: 8px; }
        .main-content .activity-section, .main-content .quick-actions, .main-content .property-summary { background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1.5rem; }
        .main-content .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
        .main-content .section-title { font-size: 1.25rem; font-weight: 600; color: #1e293b; }
        .main-content .btn-primary { background: #6366f1; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.90rem; font-weight: 500; transition: all 0.2s; }
        .main-content .btn-primary:hover { background: #818cf8; color: #fff; }
        .main-content .status-badge.status-available { background: #d1fae5; color: #059669; }
        .main-content .status-badge.status-occupied { background: #dbeafe; color: #2563eb; }
        .main-content .status-badge.status-maintenance { background: #fef3c7; color: #d97706; }
        @media (max-width: 1200px) { .main-content .content-grid { grid-template-columns: 1fr; } }
        @media (max-width: 900px) { aside.sidebar { position: fixed; left: 0; height: 100vh; z-index: 1040; } .main-content { padding: 1.5rem .5rem .5rem 1rem; } }
        @media (max-width: 600px) { .main-content { padding: .6rem .2rem; } .main-content .stats-grid { grid-template-columns: 1fr; } }

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

        body.dark-mode .content-header h1 {
            color: #f1f5f9 !important;
        }

        body.dark-mode .welcome-section,
        body.dark-mode .stat-card,
        body.dark-mode .property-summary,
        body.dark-mode .activity-section,
        body.dark-mode .quick-actions {
            background: #1e293b !important;
            color: #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .stat-card.revenue-card {
            background: linear-gradient(135deg, #064e3b 0%, #1e293b 100%) !important;
        }

        body.dark-mode .stat-card .stat-value {
            color: #f1f5f9 !important;
        }

        body.dark-mode .stat-card .stat-label {
            color: #94a3b8 !important;
        }

        body.dark-mode .stat-card .stat-sublabel {
            color: #64748b !important;
        }

        body.dark-mode .welcome-section h2,
        body.dark-mode .section-title {
            color: #f1f5f9 !important;
        }

        body.dark-mode .welcome-section p {
            color: #94a3b8 !important;
        }

        body.dark-mode .user-profile {
            background: #1e293b !important;
        }

        body.dark-mode .user-info h3 {
            color: #f1f5f9 !important;
        }

        body.dark-mode .user-info p {
            color: #94a3b8 !important;
        }

        body.dark-mode .occupancy-rate {
            background: #0f172a !important;
        }

        body.dark-mode .occupancy-label {
            color: #94a3b8 !important;
        }

        body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, body.dark-mode h4, body.dark-mode h5 {
            color: #f1f5f9 !important;
        }

        body.dark-mode p {
            color: #94a3b8;
        }

        body.dark-mode .page-section,
        body.dark-mode .content-card,
        body.dark-mode .card {
            background: #1e293b !important;
            color: #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
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

        body.dark-mode .status-badge.status-available {
            background: #064e3b !important;
            color: #6ee7b7 !important;
        }

        body.dark-mode .status-badge.status-occupied {
            background: #1e40af !important;
            color: #bfdbfe !important;
        }

        body.dark-mode .status-badge.status-maintenance {
            background: #78350f !important;
            color: #fbbf24 !important;
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
        body.dark-mode .chat-card,
        body.dark-mode .chat-page .chat-card {
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

        body.dark-mode .message.received .message-text {
            color: #e2e8f0 !important;
        }

        body.dark-mode .message.received .attachment-item {
            background: #475569 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .message-system {
            background: #334155 !important;
            color: #94a3b8 !important;
        }

        body.dark-mode .date-separator span {
            background: #0f172a !important;
            color: #94a3b8 !important;
        }

        body.dark-mode .date-separator::before {
            background: #334155 !important;
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

        body.dark-mode .header-action-btn,
        body.dark-mode .input-action-btn {
            background: #334155 !important;
            border-color: #475569 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .back-btn {
            background: #334155 !important;
            color: #94a3b8 !important;
        }

        body.dark-mode .back-btn:hover {
            background: #475569 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .chat-header-info h3 {
            color: #f1f5f9 !important;
        }

        body.dark-mode .chat-header-info p {
            color: #94a3b8 !important;
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
            <span class="portal-title-label">Tenant Portal</span>
        </div>
        <nav class="sidebar-nav">
            <a class="nav-link{{ request()->routeIs('tenant.dashboard') ? ' active' : '' }}" href="{{ route('tenant.dashboard') }}">
                <span class="nav-icon"><i class="fas fa-home"></i></span> <span class="nav-label">Dashboard</span>
            </a>
            <a class="nav-link{{ request()->routeIs('tenant.upload-documents') ? ' active' : '' }}" href="{{ route('tenant.upload-documents') }}">
                <span class="nav-icon"><i class="fas fa-upload"></i></span> <span class="nav-label">Upload Documents</span>
            </a>
            <a class="nav-link{{ request()->routeIs('explore') ? ' active' : '' }}" href="{{ route('explore') }}">
                <span class="nav-icon"><i class="fas fa-search"></i></span> <span class="nav-label">Browse Properties</span>
            </a>
            <a class="nav-link{{ request()->routeIs('tenant.payments') ? ' active' : '' }}" href="{{ route('tenant.payments') }}">
                <span class="nav-icon"><i class="fas fa-credit-card"></i></span> <span class="nav-label">Payments</span>
            </a>
            <a class="nav-link{{ request()->routeIs('tenant.maintenance*') ? ' active' : '' }}" href="{{ route('tenant.maintenance.index') }}"><span class="nav-icon"><i class="fas fa-wrench"></i></span> <span class="nav-label">Maintenance</span></a>
            <a class="nav-link{{ request()->routeIs('tenant.announcements*') ? ' active' : '' }}" href="{{ route('tenant.announcements.index') }}"><span class="nav-icon"><i class="fas fa-bullhorn"></i></span> <span class="nav-label">Announcements</span></a>
            <a class="nav-link{{ request()->routeIs('tenant.chat*') ? ' active' : '' }}" href="{{ route('tenant.chat') }}">
                <span class="nav-icon"><i class="fas fa-comments"></i></span> 
                <span class="nav-label">Messages</span>
                @if(auth()->user()->total_unread_messages > 0)
                    <span class="badge-count">{{ auth()->user()->total_unread_messages }}</span>
                @endif
            </a>
            <a class="nav-link{{ request()->routeIs('tenant.lease') ? ' active' : '' }}" href="{{ route('tenant.lease') }}">
                <span class="nav-icon"><i class="fas fa-file-contract"></i></span> <span class="nav-label">Lease</span>
            </a>
            <a class="nav-link{{ request()->routeIs('tenant.profile') ? ' active' : '' }}" href="{{ route('tenant.profile') }}">
                <span class="nav-icon"><i class="fas fa-user"></i></span> <span class="nav-label">Profile</span>
            </a>
        </nav>
        <div class="sidebar-footer mt-auto"></div>
    </aside>
    <main class="main-content">
        <div class="topbar">
            @include('partials.notification-bell')
            <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode">
                <i class="fas fa-moon" id="darkModeIcon"></i>
            </button>
            <div class="dropdown" id="tnProfileDropdown">
                <div class="profile-btn" id="tnProfileBtn">
                    <div class="profile-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down" style="font-size:.85rem;color:#64748b"></i>
                </div>
                <div class="dropdown-menu" id="tnDropdownMenu">
                    <a href="{{ route('tenant.profile') }}" class="dropdown-item"><i class="fas fa-user"></i> Profile</a>
                    <a href="{{ route('tenant.profile') }}" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a>
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
            try { localStorage.setItem('tenantSidebarCollapsed', cont.classList.contains('collapsed') ? '1' : '0'); } catch (e) {}
        }
    };
    // Respect stored state
    (function() {
        var cont = document.getElementById('dashboardContainer');
        try { if (localStorage.getItem('tenantSidebarCollapsed') === '1') cont.classList.add('collapsed'); } catch (e) {}
    })();
    (function(){
        var btn=document.getElementById('tnProfileBtn');
        var menu=document.getElementById('tnDropdownMenu');
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