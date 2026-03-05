@extends('layouts.super-admin-app')

@section('title', 'User Management')

@push('styles')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .content-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }


        .page-section {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }

        .section-subtitle {
            color: #64748b;
            font-size: 1rem;
            margin-top: 0.25rem;
        }

        /* Search and Filters */
        .filters-section {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid #3b82f6;
            text-align: center;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .data-table th {
            background: #f8fafc;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #f1f5f9;
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            color: white;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-super_admin {
            background: #e0e7ff;
            color: #3730a3;
        }

        .role-landlord {
            background: #fef3c7;
            color: #92400e;
        }

        .role-tenant {
            background: #d1fae5;
            color: #065f46;
        }

        /* Dark Mode Role Badge Styles */
        body.dark-mode .role-super_admin {
            background: #6366f1;
            color: #e0e7ff;
        }

        body.dark-mode .role-landlord {
            background: #f59e0b;
            color: #fff;
        }

        body.dark-mode .role-tenant {
            background: #10b981;
            color: #fff;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-approved {
            background: #d1fae5;
            color: #059669;
        }

        .status-rejected {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-active {
            background: #dbeafe;
            color: #2563eb;
        }

        /* Dark Mode Status Badge Styles */
        body.dark-mode .status-active {
            background: #1e40af;
            color: #bfdbfe;
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

        /* Additional Dark Mode Styles for Users Page */
        body.dark-mode .content-header h1 {
            color: #f1f5f9 !important;
        }

        body.dark-mode .page-section {
            background: #1e293b !important;
            color: #e2e8f0;
        }

        body.dark-mode .section-title {
            color: #f1f5f9 !important;
        }

        body.dark-mode .section-subtitle {
            color: #94a3b8 !important;
        }

        body.dark-mode .stat-card {
            background: #1e293b !important;
            color: #e2e8f0;
        }

        body.dark-mode .stat-value {
            color: #f1f5f9 !important;
        }

        body.dark-mode .stat-label {
            color: #94a3b8 !important;
        }

        body.dark-mode .data-table th {
            background: #0f172a !important;
            color: #94a3b8 !important;
            border-bottom-color: #334155 !important;
        }

        body.dark-mode .data-table td {
            border-bottom-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .data-table tbody tr:hover {
            background: #0f172a !important;
        }

        body.dark-mode .form-label {
            color: #e2e8f0 !important;
        }

        body.dark-mode .form-control {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .form-control:focus {
            border-color: #3b82f6 !important;
            background: #0f172a !important;
        }

        body.dark-mode .alert-success {
            background: #064e3b !important;
            border-color: #065f46 !important;
            color: #6ee7b7 !important;
        }

        body.dark-mode .pagination a,
        body.dark-mode .pagination span {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .pagination a:hover {
            background: #334155 !important;
            border-color: #475569 !important;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #047857;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            text-decoration: none;
            color: #374151;
            font-size: 0.875rem;
        }

        .pagination a:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .pagination .active span {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }

        .pagination .disabled span {
            color: #9ca3af;
            cursor: not-allowed;
        }
    </style>
@endpush

@section('content')
            <!-- Header -->
            <div class="content-header">
                <div>
                    <h1>User Management</h1>
                    <p style="color: #64748b; margin-top: 0.5rem;">Manage all users in the system</p>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ \App\Models\User::count() }}</div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ \App\Models\User::where('role', 'super_admin')->count() }}</div>
                    <div class="stat-label">Super Admins</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ \App\Models\User::where('role', 'landlord')->count() }}</div>
                    <div class="stat-label">Landlords</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ \App\Models\User::where('role', 'tenant')->count() }}</div>
                    <div class="stat-label">Tenants</div>
                </div>
            </div>

            <!-- Users Section -->
            <div class="page-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">All Users</h2>
                        <p class="section-subtitle">Search, filter, and manage user accounts</p>
                    </div>
                    <a href="{{ route('super-admin.create-user') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
                </div>

                <!-- Search and Filters -->
                <form method="GET" action="{{ route('super-admin.users') }}">
                    <div class="filters-section">
                        <div class="form-group">
                            <label class="form-label">Search Users</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by name or email..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control">
                                <option value="">All Roles</option>
                                <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="landlord" {{ request('role') == 'landlord' ? 'selected' : '' }}>Landlord</option>
                                <option value="tenant" {{ request('role') == 'tenant' ? 'selected' : '' }}>Tenant</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="user-profile" style="background: transparent; padding: 0; box-shadow: none;">
                                            <div class="user-avatar" style="width: 32px; height: 32px; margin-right: 0.5rem; font-size: 0.875rem;">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 600;">{{ $user->name }}</div>
                                                <div style="font-size: 0.75rem; color: #64748b;">ID: #{{ $user->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="status-badge role-{{ $user->role }}">
                                            {{ str_replace('_', ' ', ucfirst($user->role)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $user->status }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>{{ $user->created_at->format('M d, Y') }}</div>
                                        <div style="font-size: 0.75rem; color: #64748b;">{{ $user->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('super-admin.edit-user', $user->id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @if($user->role === 'landlord' && $user->status === 'pending')
                                                <form method="POST" action="{{ route('super-admin.approve-landlord', $user->id) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this landlord?')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                            @endif
                                            @if($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('super-admin.delete-user', $user->id) }}" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 3rem; color: #64748b;">
                                        <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                        <div style="font-size: 1.125rem; font-weight: 500; margin-bottom: 0.5rem;">No users found</div>
                                        <div>Try adjusting your search or filter criteria</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="pagination">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
@endsection