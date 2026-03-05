<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Housesync</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Super Admin</h2>
                <p>{{ auth()->user()->name }}</p>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('super-admin.dashboard') }}" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="{{ route('super-admin.pending-landlords') }}" class="nav-item">
                    <i class="fas fa-user-clock"></i> Pending Landlords
                </a>
                <a href="{{ route('super-admin.users') }}" class="nav-item active">
                    <i class="fas fa-users"></i> All Users
                </a>
                <a href="{{ route('super-admin.apartments') }}" class="nav-item">
                    <i class="fas fa-building"></i> Apartments
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

        <div class="main-content">
            <div class="content-header">
                <h1>Edit User</h1>
                <p>Update user information - {{ $user->name }}</p>
            </div>

            @if($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="section">
                <form method="POST" action="{{ route('super-admin.update-user', $user->id) }}" class="user-form">
                    @csrf
                    @method('PUT')
                    
                    <div class="user-status-info">
                        <div class="status-grid">
                            <div class="status-item">
                                <label>Current Role:</label>
                                <span class="badge badge-{{ $user->role }}">{{ str_replace('_', ' ', ucfirst($user->role)) }}</span>
                            </div>
                            <div class="status-item">
                                <label>Current Status:</label>
                                <span class="badge badge-{{ $user->status }}">{{ ucfirst($user->status) }}</span>
                            </div>
                            <div class="status-item">
                                <label>Member Since:</label>
                                <span>{{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($user->approved_at)
                                <div class="status-item">
                                    <label>Approved:</label>
                                    <span>{{ $user->approved_at->format('M d, Y') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="role">Role <span class="required">*</span></label>
                            <select id="role" name="role" class="form-control" required onchange="toggleRoleFields()">
                                <option value="">Select Role</option>
                                <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="landlord" {{ old('role', $user->role) == 'landlord' ? 'selected' : '' }}>Landlord</option>
                                <option value="tenant" {{ old('role', $user->role) == 'tenant' ? 'selected' : '' }}>Tenant</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3">{{ old('address', $user->address) }}</textarea>
                    </div>

                    <div class="form-group full-width" id="business-info-group" style="display: {{ old('role', $user->role) == 'landlord' ? 'block' : 'none' }};">
                        <label for="business_info">Business Information</label>
                        <textarea id="business_info" name="business_info" class="form-control" rows="4" placeholder="Enter business details, company name, registration info, etc.">{{ old('business_info', $user->business_info) }}</textarea>
                    </div>

                    <div class="password-section">
                        <h3>Password Update</h3>
                        <p class="form-text">Leave blank to keep current password</p>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="password">New Password</label>
                                <input type="password" id="password" name="password" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation">Confirm New Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>

                    @if($user->role === 'landlord')
                        <div class="landlord-info">
                            <h3>Landlord Information</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Total Apartments:</label>
                                    <span>{{ $user->apartments->count() }}</span>
                                </div>
                                <div class="info-item">
                                    <label>Total Units:</label>
                                    <span>{{ $user->apartments->sum(function($apartment) { return $apartment->units->count(); }) }}</span>
                                </div>
                                <div class="info-item">
                                    <label>Monthly Revenue:</label>
                                    <span>${{ number_format($user->apartments->sum(function($apartment) { return $apartment->getTotalRevenue(); }), 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($user->rejection_reason)
                        <div class="rejection-info">
                            <h3>Rejection Information</h3>
                            <div class="rejection-reason">
                                <strong>Reason:</strong> {{ $user->rejection_reason }}
                            </div>
                        </div>
                    @endif

                    <div class="form-actions">
                        <a href="{{ route('super-admin.users') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #1f2937;
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #374151;
        }
        
        .sidebar-header h2 {
            margin: 0;
            font-size: 18px;
        }
        
        .sidebar-header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #9ca3af;
        }
        
        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #d1d5db;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        
        .nav-item:hover, .nav-item.active {
            background-color: #374151;
            color: white;
        }
        
        .nav-item i {
            margin-right: 10px;
            width: 16px;
        }
        
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid #374151;
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            color: #d1d5db;
            text-decoration: none;
            padding: 10px;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        
        .logout-btn:hover {
            background-color: #374151;
        }
        
        .logout-btn i {
            margin-right: 8px;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            background-color: #f9fafb;
            min-height: 100vh;
        }
        
        .content-header {
            margin-bottom: 30px;
        }
        
        .content-header h1 {
            margin: 0;
            color: #1f2937;
        }
        
        .content-header p {
            margin: 5px 0 0 0;
            color: #6b7280;
        }
        
        .section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .user-form {
            max-width: 800px;
        }
        
        .user-status-info {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #e5e7eb;
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .status-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .status-item label {
            font-weight: 500;
            color: #6b7280;
            font-size: 14px;
        }
        
        .status-item span {
            font-weight: 600;
            color: #1f2937;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            margin-bottom: 5px;
            font-weight: 500;
            color: #374151;
        }
        
        .required {
            color: #ef4444;
        }
        
        .form-control {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-control:invalid {
            border-color: #ef4444;
        }
        
        .form-text {
            color: #6b7280;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .password-section {
            margin: 30px 0;
            padding: 20px;
            background-color: #fffbeb;
            border-radius: 8px;
            border: 1px solid #fbbf24;
        }
        
        .password-section h3 {
            margin-bottom: 5px;
            color: #92400e;
        }
        
        .landlord-info,
        .rejection-info {
            margin: 30px 0;
            padding: 20px;
            background-color: #f0f9ff;
            border-radius: 8px;
            border: 1px solid #0ea5e9;
        }
        
        .landlord-info h3,
        .rejection-info h3 {
            margin-bottom: 15px;
            color: #0c4a6e;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-item label {
            font-weight: 500;
            color: #0369a1;
            font-size: 14px;
        }
        
        .info-item span {
            font-weight: 600;
            color: #0c4a6e;
        }
        
        .rejection-info {
            background-color: #fef2f2;
            border-color: #fca5a5;
        }
        
        .rejection-info h3 {
            color: #991b1b;
        }
        
        .rejection-reason {
            color: #7f1d1d;
            font-weight: 500;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
        }
        
        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .badge-super_admin {
            background-color: #7c3aed;
            color: white;
        }
        
        .badge-landlord {
            background-color: #3b82f6;
            color: white;
        }
        
        .badge-tenant {
            background-color: #10b981;
            color: white;
        }
        
        .badge-pending {
            background-color: #f59e0b;
            color: white;
        }
        
        .badge-approved {
            background-color: #10b981;
            color: white;
        }
        
        .badge-active {
            background-color: #10b981;
            color: white;
        }
        
        .badge-rejected {
            background-color: #ef4444;
            color: white;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert-error ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .alert-error li {
            margin: 2px 0;
        }

        /* Dark Mode Styles */
        body.dark-mode {
            background-color: #0f172a !important;
            color: #e2e8f0;
        }

        body.dark-mode .main-content {
            background-color: #0f172a !important;
        }

        body.dark-mode .content-header h1 {
            color: #f1f5f9 !important;
        }

        body.dark-mode .content-header p {
            color: #94a3b8 !important;
        }

        body.dark-mode .section {
            background: #1e293b !important;
            color: #e2e8f0;
        }

        body.dark-mode .user-status-info {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }

        body.dark-mode .status-item label {
            color: #94a3b8 !important;
        }

        body.dark-mode .status-item span {
            color: #e2e8f0 !important;
        }

        body.dark-mode .form-group label {
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

        body.dark-mode .form-text {
            color: #94a3b8 !important;
        }

        body.dark-mode .password-section {
            background-color: #78350f !important;
            border-color: #92400e !important;
        }

        body.dark-mode .password-section h3 {
            color: #fbbf24 !important;
        }

        body.dark-mode .landlord-info,
        body.dark-mode .rejection-info {
            background-color: #0c4a6e !important;
            border-color: #075985 !important;
        }

        body.dark-mode .landlord-info h3,
        body.dark-mode .rejection-info h3 {
            color: #bae6fd !important;
        }

        body.dark-mode .info-item label {
            color: #7dd3fc !important;
        }

        body.dark-mode .info-item span {
            color: #bae6fd !important;
        }

        body.dark-mode .rejection-info {
            background-color: #7f1d1d !important;
            border-color: #991b1b !important;
        }

        body.dark-mode .rejection-info h3 {
            color: #fca5a5 !important;
        }

        body.dark-mode .rejection-reason {
            color: #fca5a5 !important;
        }

        body.dark-mode .alert-error {
            background-color: #7f1d1d !important;
            color: #fca5a5 !important;
            border-color: #991b1b !important;
        }
    </style>

    <script>
        function toggleRoleFields() {
            const roleSelect = document.getElementById('role');
            const businessInfoGroup = document.getElementById('business-info-group');
            
            if (roleSelect.value === 'landlord') {
                businessInfoGroup.style.display = 'block';
            } else {
                businessInfoGroup.style.display = 'none';
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleRoleFields();
        });
    </script>
</body>
</html> 