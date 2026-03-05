@extends('layouts.landlord-app')

@section('title', 'Settings')

@push('styles')
<style>
    .settings-container {
        max-width: 800px;
    }
    .settings-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .settings-section h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .form-group {
        margin-bottom: 1.25rem;
    }
    .form-label {
        display: block;
        font-weight: 500;
        color: #475569;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    .form-control {
        width: 100%;
        padding: 0.65rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
    }
    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }
    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    @media (max-width: 600px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
    .btn-save {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
        border: none;
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-save:hover {
        background: linear-gradient(135deg, #ea580c, #dc2626);
        transform: translateY(-1px);
    }
    .alert {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }
    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }
    .profile-avatar-section {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .profile-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 600;
    }
    .profile-info h4 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    .profile-info p {
        color: #64748b;
        font-size: 0.9rem;
        margin: 0;
    }
    .password-requirements {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 0.5rem;
    }
    .password-requirements li {
        margin-bottom: 0.25rem;
    }

    /* ===== Dark Mode ===== */
    body.dark-mode .settings-section {
        background: #1e293b !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }

    body.dark-mode .settings-section h3 {
        color: #f1f5f9 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .form-label {
        color: #94a3b8 !important;
    }

    body.dark-mode .form-control {
        background: #0f172a !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .form-control:focus {
        border-color: #f97316 !important;
    }

    body.dark-mode .form-control:disabled,
    body.dark-mode .form-control[disabled] {
        background: #1e293b !important;
        color: #94a3b8 !important;
    }

    body.dark-mode .form-control[style*="background: #f1f5f9"] {
        background: #0f172a !important;
        color: #94a3b8 !important;
    }

    body.dark-mode .form-control[style*="background: #d1fae5"] {
        background: #064e3b !important;
        color: #6ee7b7 !important;
    }

    body.dark-mode .profile-avatar-section {
        border-color: #334155 !important;
    }

    body.dark-mode .profile-info h4 {
        color: #f1f5f9 !important;
    }

    body.dark-mode .profile-info p {
        color: #94a3b8 !important;
    }

    body.dark-mode .password-requirements {
        color: #94a3b8 !important;
    }
</style>
@endpush

@section('content')
<div class="settings-container">
    <div class="content-header" style="margin-bottom: 1.5rem;">
        <h1><i class="fas fa-cog me-2"></i>Settings</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Profile Section -->
    <div class="settings-section">
        <div class="profile-avatar-section">
            <div class="profile-avatar-large">
                {{ mb_substr($profile->name ?? $user->email ?? 'L', 0, 1) }}
            </div>
            <div class="profile-info">
                <h4>{{ $profile->name ?? $user->email }}</h4>
                <p>{{ $user->email }}</p>
                <p style="font-size: 0.8rem; color: #94a3b8;">Landlord Account</p>
            </div>
        </div>

        <h3><i class="fas fa-user me-2"></i>Profile Information</h3>
        
        <form method="POST" action="{{ route('landlord.settings.update') }}">
            @csrf
            @method('PUT')
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" 
                           value="{{ old('name', $profile->name ?? '') }}" 
                           placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" 
                           value="{{ old('phone', $profile->phone ?? '') }}" 
                           placeholder="e.g. +63 917 123 4567">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" 
                       value="{{ old('address', $profile->address ?? '') }}" 
                       placeholder="Enter your address">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" 
                           value="{{ old('company_name', $profile->company_name ?? '') }}" 
                           placeholder="Your company or business name">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Business Information</label>
                <textarea name="business_info" class="form-control" 
                          placeholder="Tell us about your property management business...">{{ old('business_info', $profile->business_info ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn-save">
                <i class="fas fa-save me-2"></i>Save Profile
            </button>
        </form>
    </div>

    <!-- Password Section -->
    <div class="settings-section">
        <h3><i class="fas fa-lock me-2"></i>Change Password</h3>
        
        <form method="POST" action="{{ route('landlord.settings.password') }}">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label">Current Password *</label>
                <input type="password" name="current_password" class="form-control" 
                       placeholder="Enter your current password" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">New Password *</label>
                    <input type="password" name="password" class="form-control" 
                           placeholder="Enter new password" required minlength="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" class="form-control" 
                           placeholder="Confirm new password" required minlength="8">
                </div>
            </div>

            <ul class="password-requirements">
                <li>Minimum 8 characters</li>
                <li>Passwords must match</li>
            </ul>

            <button type="submit" class="btn-save" style="background: linear-gradient(135deg, #475569, #334155);">
                <i class="fas fa-key me-2"></i>Change Password
            </button>
        </form>
    </div>

    <!-- Account Information -->
    <div class="settings-section">
        <h3><i class="fas fa-info-circle me-2"></i>Account Information</h3>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" value="{{ $user->email }}" disabled 
                       style="background: #f1f5f9; cursor: not-allowed;">
                <small style="color: #94a3b8; font-size: 0.8rem;">Contact support to change your email.</small>
            </div>
            <div class="form-group">
                <label class="form-label">Account Status</label>
                <input type="text" class="form-control" value="{{ ucfirst($user->status ?? 'Active') }}" disabled 
                       style="background: #d1fae5; color: #065f46; cursor: not-allowed; font-weight: 500;">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Member Since</label>
                <input type="text" class="form-control" value="{{ $user->created_at->format('F d, Y') }}" disabled 
                       style="background: #f1f5f9; cursor: not-allowed;">
            </div>
            <div class="form-group">
                <label class="form-label">Last Updated</label>
                <input type="text" class="form-control" value="{{ $profile?->updated_at?->format('F d, Y') ?? 'Never' }}" disabled 
                       style="background: #f1f5f9; cursor: not-allowed;">
            </div>
        </div>
    </div>
</div>
@endsection

