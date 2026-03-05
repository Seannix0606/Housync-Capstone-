<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Housesync</title>
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
                <h1>Create New User</h1>
                <p>Add a new user to the system</p>
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
                <form method="POST" action="{{ route('super-admin.store-user') }}" class="user-form">
                    @csrf
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="role">Role <span class="required">*</span></label>
                            <select id="role" name="role" class="form-control" required onchange="toggleRoleFields()">
                                <option value="">Select Role</option>
                                <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="landlord" {{ old('role') == 'landlord' ? 'selected' : '' }}>Landlord</option>
                                <option value="tenant" {{ old('role') == 'tenant' ? 'selected' : '' }}>Tenant</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password <span class="required">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                    </div>

                    <div class="form-group full-width" id="business-info-group" style="display: none;">
                        <label for="business_info">Business Information</label>
                        <textarea id="business_info" name="business_info" class="form-control" rows="4" placeholder="Enter business details, company name, registration info, etc.">{{ old('business_info') }}</textarea>
                    </div>

                    <div class="form-group full-width landlord-options" id="landlord-options" style="display: none;">
                        <div class="checkbox-group">
                            <input type="checkbox" id="approve_immediately" name="approve_immediately" value="1" {{ old('approve_immediately') ? 'checked' : '' }}>
                            <label for="approve_immediately">Approve landlord immediately</label>
                        </div>
                        <small class="form-text">If unchecked, landlord will need approval before accessing the system.</small>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('super-admin.users') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Create User
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
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }
        
        .form-text {
            color: #6b7280;
            font-size: 13px;
            margin-top: 5px;
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
        
        .landlord-options {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
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

        body.dark-mode .landlord-options {
            background-color: #0f172a !important;
            border-color: #334155 !important;
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
            const landlordOptions = document.getElementById('landlord-options');
            
            if (roleSelect.value === 'landlord') {
                businessInfoGroup.style.display = 'block';
                landlordOptions.style.display = 'block';
            } else {
                businessInfoGroup.style.display = 'none';
                landlordOptions.style.display = 'none';
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleRoleFields();
        });
    </script>
</body>
</html> 