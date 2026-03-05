<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2>Tenant Portal</h2>
        <p>Welcome, Ganador</p>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('tenant.dashboard') }}" class="nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="{{ route('explore') }}" class="nav-item {{ request()->routeIs('explore') ? 'active' : '' }}">
            <i class="fas fa-search"></i> Browse Properties
        </a>
        <a href="{{ route('tenant.upload-documents') }}" class="nav-item {{ request()->routeIs('tenant.upload-documents') ? 'active' : '' }}">
            <i class="fas fa-upload"></i> Upload Documents
        </a>
        <a href="{{ route('tenant.payments') }}" class="nav-item {{ request()->routeIs('tenant.payments') ? 'active' : '' }}">
            <i class="fas fa-credit-card"></i> Payments
        </a>
        <a href="{{ route('tenant.maintenance.index') }}" class="nav-item {{ request()->routeIs('tenant.maintenance') ? 'active' : '' }}">
            <i class="fas fa-tools"></i> Maintenance
        </a>
        <a href="{{ route('tenant.chat') }}" class="nav-item {{ request()->routeIs('tenant.chat*') ? 'active' : '' }}">
            <i class="fas fa-comments"></i> Messages
            @if(auth()->user()->total_unread_messages > 0)
                <span class="unread-badge">{{ auth()->user()->total_unread_messages }}</span>
            @endif
        </a>
        <a href="{{ route('tenant.lease') }}" class="nav-item {{ request()->routeIs('tenant.lease') ? 'active' : '' }}">
            <i class="fas fa-file-contract"></i> Lease
        </a>
        <a href="{{ route('tenant.profile') }}" class="nav-item {{ request()->routeIs('tenant.profile') ? 'active' : '' }}">
            <i class="fas fa-user"></i> Profile
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="{{ route('logout') }}" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</div>
