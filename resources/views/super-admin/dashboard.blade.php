@extends('layouts.super-admin-app')

@section('title', 'Super Admin Dashboard')

@push('styles')
<style>
    .dashboard-header {
        margin-bottom: 2rem;
    }
    
    .dashboard-header h1 {
        font-size: 2.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    
    .dashboard-header p {
        color: #64748b;
        font-size: 1.1rem;
    }

    .main-content .stats-grid,
    .stats-grid {
        display: grid !important;
        grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
        gap: 1rem !important;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border-left: 4px solid;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
        overflow: hidden;
        min-width: 0;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .stat-card.primary { border-left-color: #3b82f6; }
    .stat-card.success { border-left-color: #10b981; }
    .stat-card.warning { border-left-color: #f59e0b; }
    .stat-card.danger { border-left-color: #ef4444; }
    .stat-card.purple { border-left-color: #8b5cf6; }
    .stat-card.teal { border-left-color: #14b8a6; }

    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        gap: 0.5rem;
    }
    
    .stat-card-header > div:first-child {
        min-width: 0;
        flex: 1;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        flex-shrink: 0;
    }

    .stat-card.primary .stat-icon { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .stat-card.success .stat-icon { background: linear-gradient(135deg, #10b981, #059669); }
    .stat-card.warning .stat-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .stat-card.danger .stat-icon { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .stat-card.purple .stat-icon { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .stat-card.teal .stat-icon { background: linear-gradient(135deg, #14b8a6, #0d9488); }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
        line-height: 1;
    }

    .stat-label {
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .stat-change {
        font-size: 0.75rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-change.positive { color: #10b981; }
    .stat-change.negative { color: #ef4444; }

    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .content-card {
        background: white;
        border-radius: 1rem;
        padding: 1.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .content-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .content-card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .content-card-title i {
        color: #3b82f6;
    }

    .view-all-link {
        color: #3b82f6;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: color 0.2s;
    }

    .view-all-link:hover {
        color: #2563eb;
        text-decoration: none;
    }

    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 0.75rem;
        margin-bottom: 0.75rem;
        transition: background 0.2s;
    }

    .activity-item:hover {
        background: #f8fafc;
    }

    .activity-item:last-child {
        margin-bottom: 0;
    }

    .activity-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
        min-width: 0;
    }

    .activity-name {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    .activity-meta {
        font-size: 0.875rem;
        color: #64748b;
    }

    .activity-status {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-pending {
        background: #fef3c7;
        color: #d97706;
    }

    .status-approved {
        background: #d1fae5;
        color: #059669;
    }

    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #64748b;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 1400px) {
        .main-content .stats-grid,
        .stats-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
    }
    
    @media (max-width: 768px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
        
        .main-content .stats-grid,
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* Dark Mode Styles */
    body.dark-mode .dashboard-header h1 {
        color: #f1f5f9 !important;
    }

    body.dark-mode .dashboard-header p {
        color: #94a3b8 !important;
    }

    body.dark-mode .stat-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
    }

    body.dark-mode .stat-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
    }

    body.dark-mode .stat-value {
        color: #f1f5f9 !important;
    }

    body.dark-mode .stat-label {
        color: #94a3b8 !important;
    }

    body.dark-mode .stat-change.positive {
        color: #6ee7b7 !important;
    }

    body.dark-mode .stat-change.negative {
        color: #fca5a5 !important;
    }

    body.dark-mode .content-card {
        background: #1e293b !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    body.dark-mode .content-card-header {
        border-bottom-color: #334155 !important;
    }

    body.dark-mode .content-card-title {
        color: #f1f5f9 !important;
    }

    body.dark-mode .content-card-title i {
        color: #60a5fa !important;
    }

    body.dark-mode .view-all-link {
        color: #60a5fa !important;
    }

    body.dark-mode .view-all-link:hover {
        color: #93c5fd !important;
    }

    body.dark-mode .activity-item:hover {
        background: #0f172a !important;
    }

    body.dark-mode .activity-name {
        color: #f1f5f9 !important;
    }

    body.dark-mode .activity-meta {
        color: #94a3b8 !important;
    }

    body.dark-mode .status-pending {
        background: #78350f !important;
        color: #fbbf24 !important;
    }

    body.dark-mode .status-approved {
        background: #064e3b !important;
        color: #6ee7b7 !important;
    }

    body.dark-mode .empty-state {
        color: #94a3b8 !important;
    }
</style>
@endpush

@section('content')
<div class="dashboard-header">
    <h1>Super Admin Dashboard</h1>
    <p>Overview and management of application users and properties</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-card-header">
            <div>
                <div class="stat-value">{{ $stats['total_users'] ?? 0 }}</div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <div class="stat-card success">
        <div class="stat-card-header">
            <div>
                <div class="stat-value">{{ $stats['approved_landlords'] ?? 0 }}</div>
                <div class="stat-label">Approved Landlords</div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-user-tie"></i>
            </div>
        </div>
    </div>

    <div class="stat-card warning">
        <div class="stat-card-header">
            <div>
                <div class="stat-value">{{ $stats['pending_landlords'] ?? 0 }}</div>
                <div class="stat-label">Pending Approvals</div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-user-clock"></i>
            </div>
        </div>
    </div>

    <div class="stat-card purple">
        <div class="stat-card-header">
            <div>
                <div class="stat-value">{{ $stats['total_tenants'] ?? 0 }}</div>
                <div class="stat-label">Total Tenants</div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-user-friends"></i>
            </div>
        </div>
    </div>

    <div class="stat-card teal">
        <div class="stat-card-header">
            <div>
                <div class="stat-value">{{ $stats['total_apartments'] ?? 0 }}</div>
                <div class="stat-label">Total Properties</div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
        </div>
    </div>

    <div class="stat-card danger">
        <div class="stat-card-header">
            <div>
                <div class="stat-value">{{ \App\Models\User::rejectedLandlords()->count() }}</div>
                <div class="stat-label">Rejected Applications</div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-user-times"></i>
            </div>
        </div>
    </div>
</div>

<!-- Content Grid -->
<div class="content-grid">
    <!-- Pending Landlords -->
    <div class="content-card">
        <div class="content-card-header">
            <h3 class="content-card-title">
                <i class="fas fa-user-clock"></i>
                Pending Approvals
            </h3>
            @if($pendingLandlords->count() > 0)
                <a href="{{ route('super-admin.pending-landlords') }}" class="view-all-link">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            @endif
        </div>
        @php
            // Filter to ensure only truly pending landlords are shown
            $trulyPending = $pendingLandlords->filter(function($landlord) {
                return $landlord->landlordProfile && $landlord->landlordProfile->status === 'pending';
            });
        @endphp
        @if($trulyPending->count() > 0)
            <ul class="activity-list">
                @foreach($trulyPending->take(5) as $landlord)
                    <li class="activity-item">
                        <div class="activity-avatar">
                            {{ substr($landlord->name, 0, 1) }}
                        </div>
                        <div class="activity-content">
                            <div class="activity-name">{{ $landlord->name }}</div>
                            <div class="activity-meta">{{ $landlord->email }} • {{ $landlord->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="activity-status status-pending">Pending</span>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <p>No pending approvals</p>
            </div>
        @endif
    </div>

    <!-- Recent Users -->
    <div class="content-card">
        <div class="content-card-header">
            <h3 class="content-card-title">
                <i class="fas fa-user-plus"></i>
                Recent Users
            </h3>
            <a href="{{ route('super-admin.users') }}" class="view-all-link">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        @if($recentUsers->count() > 0)
            <ul class="activity-list">
                @foreach($recentUsers->take(5) as $user)
                    <li class="activity-item">
                        <div class="activity-avatar" style="background: linear-gradient(135deg, 
                            @if($user->role === 'landlord') #10b981, #059669
                            @elseif($user->role === 'tenant') #8b5cf6, #7c3aed
                            @elseif($user->role === 'super_admin') #ef4444, #dc2626
                            @else #64748b, #475569
                            @endif
                        );">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div class="activity-content">
                            <div class="activity-name">{{ $user->name }}</div>
                            <div class="activity-meta">{{ ucfirst($user->role) }} • {{ $user->created_at->diffForHumans() }}</div>
                        </div>
                        @if($user->role === 'landlord' && $user->landlordProfile)
                            <span class="activity-status status-{{ $user->landlordProfile->status }}">
                                {{ ucfirst($user->landlordProfile->status) }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>No recent users</p>
            </div>
        @endif
    </div>
</div>
@endsection
