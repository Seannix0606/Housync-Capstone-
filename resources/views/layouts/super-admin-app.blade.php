<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Admin Portal')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body { min-height: 100%; background-color: #f8fafc; }
        body { font-family: 'Inter', sans-serif; color: #1e293b; }
        .dashboard-container { display: flex; min-height: 100vh; }
        aside.sidebar {
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
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
        .sidebar-footer { display:none; }
        .main-content { flex: 1 1 0%; background: #f8fafc; min-width: 0; padding: 2rem; transition: none; }
        /* Profile dropdown */
        .topbar{display:flex;justify-content:flex-end;align-items:center;margin-bottom:1rem}
        .profile-btn{display:flex;align-items:center;gap:.6rem;background:#fff;border:1px solid #e2e8f0;border-radius:9999px;padding:.35rem .6rem;cursor:pointer;box-shadow:0 1px 2px rgba(0,0,0,.04)}
        .profile-avatar{width:32px;height:32px;border-radius:50%;background:#1e40af;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600}
        .dropdown{position:relative}
        .dropdown-menu{position:absolute;right:0;top:calc(100% + 8px);background:#fff;border:1px solid #e2e8f0;border-radius:10px;min-width:220px;box-shadow:0 8px 24px rgba(0,0,0,.08);display:none;z-index:1050;padding:.4rem}
        .dropdown-menu.show{display:block}
        .dropdown-item{display:flex;align-items:center;gap:.6rem;padding:.6rem .8rem;border-radius:8px;color:#1e293b;text-decoration:none}
        .dropdown-item:hover{background:#f8fafc;text-decoration:none}
        /* Global dashboard styles (shared) */
        .main-content .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.5rem;margin-bottom:2rem}
        .main-content .stat-card{background:#fff;border-radius:1rem;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.06);border-left:4px solid #1e40af;text-align:center}
        .main-content .stat-card .stat-value{font-size:2rem;font-weight:700;color:#1e293b}
        .main-content .stat-card .stat-label{color:#64748b;font-size:.92rem}
        @media(max-width:900px){aside.sidebar{position:fixed;left:0;height:100vh;z-index:1040}.main-content{padding:1.5rem .5rem .5rem 1rem}}
        @media(max-width:600px){.main-content{padding:.7rem .2rem}aside.sidebar{width:90px}.dashboard-container.collapsed aside.sidebar{width:56px}}
        
        /* Dark Mode Styles - Global */
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
        
        body.dark-mode .stat-card {
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
        
        body.dark-mode .page-section {
            background: #1e293b !important;
            color: #e2e8f0;
        }
        
        body.dark-mode .content-card {
            background: #1e293b !important;
            color: #e2e8f0;
        }
        
        body.dark-mode .data-table {
            color: #e2e8f0;
        }
        
        body.dark-mode .data-table th {
            background: #0f172a !important;
            color: #94a3b8;
            border-bottom-color: #334155;
        }
        
        body.dark-mode .data-table td {
            border-bottom-color: #334155;
            color: #e2e8f0;
        }
        
        body.dark-mode .data-table tbody tr:hover {
            background: #0f172a !important;
        }
        
        body.dark-mode h1, body.dark-mode h2, body.dark-mode h3 {
            color: #f1f5f9 !important;
        }
        
        body.dark-mode p {
            color: #cbd5e1;
        }
        
        body.dark-mode .content-header h1 {
            color: #f1f5f9 !important;
        }
        
        body.dark-mode .content-header p {
            color: #94a3b8 !important;
        }
        
        body.dark-mode .section-title {
            color: #f1f5f9 !important;
        }
        
        body.dark-mode .section-subtitle {
            color: #94a3b8 !important;
        }
        
        body.dark-mode .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        body.dark-mode .btn-primary:hover {
            background: #2563eb;
        }
        
        body.dark-mode .btn-success {
            background: #10b981;
            color: white;
        }
        
        body.dark-mode .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        body.dark-mode .status-badge {
            color: white;
        }
        
        body.dark-mode .status-pending {
            background: #78350f;
            color: #fbbf24;
        }
        
        body.dark-mode .status-approved {
            background: #064e3b;
            color: #6ee7b7;
        }
        
        body.dark-mode .status-rejected {
            background: #7f1d1d;
            color: #fca5a5;
        }
        
        body.dark-mode .alert {
            color: #e2e8f0;
        }
        
        body.dark-mode .alert-success {
            background: #064e3b !important;
            border-color: #047857 !important;
            color: #6ee7b7 !important;
        }
        
        body.dark-mode .alert-error {
            background: #7f1d1d !important;
            border-color: #991b1b !important;
            color: #fca5a5 !important;
        }
        
        body.dark-mode input[type="text"],
        body.dark-mode input[type="email"],
        body.dark-mode input[type="number"],
        body.dark-mode input[type="password"],
        body.dark-mode textarea,
        body.dark-mode select {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }
        
        body.dark-mode input:focus,
        body.dark-mode textarea:focus,
        body.dark-mode select:focus {
            border-color: #60a5fa !important;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1) !important;
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="dashboard-container" id="dashboardContainer">
    <aside class="sidebar">
        <div class="sidebar-header">
            <button class="sidebar-hamburger" id="sidebarCollapseBtn" aria-label="Toggle navigation"><i class="fas fa-bars"></i></button>
            <span class="portal-title-label">Super Admin Portal</span>
        </div>
        <nav class="sidebar-nav">
            <a class="nav-link{{ request()->routeIs('super-admin.dashboard') ? ' active' : '' }}" href="{{ route('super-admin.dashboard') }}"><span class="nav-icon"><i class="fas fa-home"></i></span> <span class="nav-label">Dashboard</span></a>
            <a class="nav-link{{ request()->routeIs('super-admin.pending-landlords') ? ' active' : '' }}" href="{{ route('super-admin.pending-landlords') }}"><span class="nav-icon"><i class="fas fa-user-clock"></i></span> <span class="nav-label">Pending Approvals</span></a>
            <a class="nav-link{{ request()->routeIs('super-admin.users') ? ' active' : '' }}" href="{{ route('super-admin.users') }}"><span class="nav-icon"><i class="fas fa-users"></i></span> <span class="nav-label">User Management</span></a>
            <a class="nav-link{{ request()->routeIs('super-admin.apartments') ? ' active' : '' }}" href="{{ route('super-admin.apartments') }}"><span class="nav-icon"><i class="fas fa-building"></i></span> <span class="nav-label">Properties</span></a>
            <a class="nav-link{{ request()->routeIs('super-admin.settings') ? ' active' : '' }}" href="{{ route('super-admin.settings') }}"><span class="nav-icon"><i class="fas fa-cog"></i></span> <span class="nav-label">System Settings</span></a>
        </nav>
        <div class="sidebar-footer mt-auto"></div>
    </aside>
    <main class="main-content">
        <div class="topbar">
            <div class="dropdown" id="saProfileDropdown">
                <div class="profile-btn" id="saProfileBtn">
                    <div class="profile-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down" style="font-size:.85rem;color:#64748b"></i>
                </div>
                <div class="dropdown-menu" id="saDropdownMenu">
                    <a href="{{ route('super-admin.settings') }}" class="dropdown-item"><i class="fas fa-user-cog"></i> Settings</a>
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
    // Dark Mode Global Application
    (function() {
        function applyDarkMode(isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                document.documentElement.setAttribute('data-theme', 'light');
            }
        }
        
        // Check localStorage first for immediate effect
        const savedDarkMode = localStorage.getItem('darkMode');
        if (savedDarkMode === 'true') {
            applyDarkMode(true);
        } else {
            // Check database setting via AJAX if not in localStorage
            fetch('/super-admin/check-dark-mode')
                .then(response => response.json())
                .then(data => {
                    if (data.darkMode) {
                        applyDarkMode(true);
                        localStorage.setItem('darkMode', 'true');
                    }
                })
                .catch(() => {
                    // Fallback: check if setting exists in page
                    const darkModeSetting = document.getElementById('setting_dark_mode');
                    if (darkModeSetting && darkModeSetting.checked) {
                        applyDarkMode(true);
                    }
                });
        }
        
        // Listen for dark mode changes from settings page
        window.addEventListener('storage', function(e) {
            if (e.key === 'darkMode') {
                applyDarkMode(e.newValue === 'true');
            }
        });
        
        // Also check periodically for changes (when settings are saved)
        setInterval(function() {
            const darkModeSetting = document.getElementById('setting_dark_mode');
            if (darkModeSetting) {
                const isDark = darkModeSetting.checked;
                const currentDark = document.body.classList.contains('dark-mode');
                if (isDark !== currentDark) {
                    applyDarkMode(isDark);
                    localStorage.setItem('darkMode', isDark ? 'true' : 'false');
                }
            }
        }, 1000);
    })();
    
    document.getElementById('sidebarCollapseBtn').onclick = function() {
        var cont = document.getElementById('dashboardContainer');
        if (cont) { cont.classList.toggle('collapsed'); try { localStorage.setItem('superAdminSidebarCollapsed', cont.classList.contains('collapsed') ? '1' : '0'); } catch(e) {} }
    };
    (function(){ var cont=document.getElementById('dashboardContainer'); try{ if(localStorage.getItem('superAdminSidebarCollapsed')==='1') cont.classList.add('collapsed'); }catch(e){} })();
    (function(){
        var btn=document.getElementById('saProfileBtn');
        var menu=document.getElementById('saDropdownMenu');
        if(btn&&menu){
            btn.addEventListener('click',function(e){ e.stopPropagation(); menu.classList.toggle('show'); });
            document.addEventListener('click',function(){ menu.classList.remove('show'); });
        }
    })();
</script>
@stack('scripts')
@yield('scripts')
</body>
</html>
