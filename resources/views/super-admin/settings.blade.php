@extends('layouts.super-admin-app')

@section('title', 'System Settings')

@php
    $darkModeEnabled = \App\Models\Setting::get('dark_mode', false);
@endphp

@push('styles')
<style>
    .settings-container {
        max-width: 1200px;
    }

    .settings-header {
        margin-bottom: 2rem;
    }

    .settings-header h1 {
        font-size: 2.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .settings-header p {
        color: #64748b;
        font-size: 1.1rem;
    }

    .settings-tabs {
        display: flex;
        gap: 0.5rem;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 2rem;
        overflow-x: auto;
    }

    .settings-tab {
        padding: 1rem 1.5rem;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        color: #64748b;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .settings-tab:hover {
        color: #3b82f6;
        background: #f8fafc;
    }

    .settings-tab.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
        background: #f8fafc;
    }

    .settings-tab i {
        font-size: 1.1rem;
    }

    .settings-content {
        display: none;
    }

    .settings-content.active {
        display: block;
    }

    .settings-form {
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .form-section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-label .required {
        color: #ef4444;
    }

    .form-description {
        font-size: 0.875rem;
        color: #64748b;
        margin-top: 0.25rem;
        margin-bottom: 0.5rem;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.95rem;
        transition: all 0.2s;
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-control:disabled {
        background: #f8fafc;
        cursor: not-allowed;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .form-check-input {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #3b82f6;
    }

    .form-check-label {
        font-weight: 500;
        color: #1e293b;
        cursor: pointer;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding-top: 1.5rem;
        border-top: 2px solid #f1f5f9;
        margin-top: 2rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.5rem;
        font-weight: 500;
        cursor: pointer;
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
    }

    .btn-secondary {
        background: #e2e8f0;
        color: #1e293b;
    }

    .btn-secondary:hover {
        background: #cbd5e1;
    }

    .alert {
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .alert-success {
        background: #d1fae5;
        border: 1px solid #a7f3d0;
        color: #047857;
    }

    .alert-error {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #64748b;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .settings-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
        }

        .settings-form {
            padding: 1.5rem;
        }
    }

    /* Dark Mode Styles */
    body.dark-mode {
        background-color: #0f172a;
        color: #e2e8f0;
    }

    body.dark-mode .settings-header h1 {
        color: #f1f5f9;
    }

    body.dark-mode .settings-header p {
        color: #94a3b8;
    }

    body.dark-mode .settings-tabs {
        border-bottom-color: #334155;
    }

    body.dark-mode .settings-tab {
        color: #94a3b8;
    }

    body.dark-mode .settings-tab:hover {
        color: #60a5fa;
        background: #1e293b;
    }

    body.dark-mode .settings-tab.active {
        color: #60a5fa;
        border-bottom-color: #60a5fa;
        background: #1e293b;
    }

    body.dark-mode .settings-form {
        background: #1e293b;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    body.dark-mode .form-section-title {
        color: #f1f5f9;
        border-bottom-color: #334155;
    }

    body.dark-mode .form-label {
        color: #e2e8f0;
    }

    body.dark-mode .form-description {
        color: #94a3b8;
    }

    body.dark-mode .form-control {
        background: #0f172a;
        border-color: #334155;
        color: #e2e8f0;
    }

    body.dark-mode .form-control:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
    }

    body.dark-mode .form-control:disabled {
        background: #1e293b;
        color: #64748b;
    }

    body.dark-mode .form-check-label {
        color: #e2e8f0;
    }

    body.dark-mode .form-actions {
        border-top-color: #334155;
    }

    body.dark-mode .btn-secondary {
        background: #334155;
        color: #e2e8f0;
    }

    body.dark-mode .btn-secondary:hover {
        background: #475569;
    }

    body.dark-mode .alert-success {
        background: #064e3b;
        border-color: #047857;
        color: #6ee7b7;
    }

    body.dark-mode .alert-error {
        background: #7f1d1d;
        border-color: #991b1b;
        color: #fca5a5;
    }

    body.dark-mode .empty-state {
        color: #94a3b8;
    }

    body.dark-mode .empty-state i {
        opacity: 0.3;
    }
</style>
@endpush

@section('content')
<div class="settings-container">
    <div class="settings-header">
        <h1>System Settings</h1>
        <p>Manage application settings and configurations</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="settings-tabs" id="settingsTabs">
        <button class="settings-tab active" data-tab="general">
            <i class="fas fa-cog"></i> General
        </button>
        <button class="settings-tab" data-tab="email">
            <i class="fas fa-envelope"></i> Email
        </button>
        <button class="settings-tab" data-tab="security">
            <i class="fas fa-shield-alt"></i> Security
        </button>
        <button class="settings-tab" data-tab="features">
            <i class="fas fa-toggle-on"></i> Features
        </button>
        <button class="settings-tab" data-tab="notifications">
            <i class="fas fa-bell"></i> Notifications
        </button>
        <button class="settings-tab" data-tab="system">
            <i class="fas fa-server"></i> System
        </button>
    </div>

    @foreach($groups as $group)
        <div class="settings-content {{ $loop->first ? 'active' : '' }}" id="tab-{{ $group }}">
            <form method="POST" action="{{ route('super-admin.settings.group.update', $group) }}" class="settings-form">
                @csrf
                
                @php
                    $groupSettings = $settings->get($group, collect());
                @endphp

                @if($groupSettings->count() > 0)
                    @if($group === 'general')
                        <div class="form-section">
                            <h3 class="form-section-title">General Information</h3>
                            
                            @foreach($groupSettings as $setting)
                                <div class="form-group">
                                    <label class="form-label">{{ ucwords(str_replace('_', ' ', $setting->key)) }}</label>
                                    @if($setting->description)
                                        <div class="form-description">{{ $setting->description }}</div>
                                    @endif
                                    <input type="text" 
                                           name="settings[{{ $setting->key }}]" 
                                           value="{{ old("settings.{$setting->key}", $setting->value) }}"
                                           class="form-control"
                                           placeholder="Enter {{ str_replace('_', ' ', $setting->key) }}">
                                </div>
                            @endforeach
                        </div>

                    @elseif($group === 'email')
                        <div class="form-section">
                            <h3 class="form-section-title">Email Configuration</h3>
                            
                            @foreach($groupSettings as $setting)
                                <div class="form-group">
                                    <label class="form-label">{{ ucwords(str_replace('_', ' ', $setting->key)) }}</label>
                                    @if($setting->description)
                                        <div class="form-description">{{ $setting->description }}</div>
                                    @endif
                                    <input type="{{ $setting->key === 'mail_from_address' ? 'email' : 'text' }}" 
                                           name="settings[{{ $setting->key }}]" 
                                           value="{{ old("settings.{$setting->key}", $setting->value) }}"
                                           class="form-control"
                                           placeholder="Enter {{ str_replace('_', ' ', $setting->key) }}">
                                </div>
                            @endforeach
                        </div>

                    @elseif($group === 'security')
                        <div class="form-section">
                            <h3 class="form-section-title">Security Settings</h3>
                            
                            @foreach($groupSettings as $setting)
                                <div class="form-group">
                                    @if($setting->type === 'boolean')
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   name="settings[{{ $setting->key }}]" 
                                                   value="1"
                                                   id="setting_{{ $setting->key }}"
                                                   {{ old("settings.{$setting->key}", $setting->value) == 'true' || old("settings.{$setting->key}", $setting->value) == '1' ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label class="form-check-label" for="setting_{{ $setting->key }}">
                                                {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                            </label>
                                        </div>
                                        @if($setting->description)
                                            <div class="form-description">{{ $setting->description }}</div>
                                        @endif
                                    @else
                                        <label class="form-label">{{ ucwords(str_replace('_', ' ', $setting->key)) }}</label>
                                        @if($setting->description)
                                            <div class="form-description">{{ $setting->description }}</div>
                                        @endif
                                        <input type="number" 
                                               name="settings[{{ $setting->key }}]" 
                                               value="{{ old("settings.{$setting->key}", $setting->value) }}"
                                               class="form-control"
                                               min="1"
                                               placeholder="Enter {{ str_replace('_', ' ', $setting->key) }}">
                                    @endif
                                </div>
                            @endforeach
                        </div>

                    @elseif($group === 'features')
                        <div class="form-section">
                            <h3 class="form-section-title">Feature Toggles</h3>
                            
                            @foreach($groupSettings as $setting)
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               name="settings[{{ $setting->key }}]" 
                                               value="1"
                                               id="setting_{{ $setting->key }}"
                                               {{ old("settings.{$setting->key}", $setting->value) == 'true' || old("settings.{$setting->key}", $setting->value) == '1' ? 'checked' : '' }}
                                               class="form-check-input">
                                        <label class="form-check-label" for="setting_{{ $setting->key }}">
                                            {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        </label>
                                    </div>
                                    @if($setting->description)
                                        <div class="form-description">{{ $setting->description }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                    @elseif($group === 'notifications')
                        <div class="form-section">
                            <h3 class="form-section-title">Notification Preferences</h3>
                            
                            @foreach($groupSettings as $setting)
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               name="settings[{{ $setting->key }}]" 
                                               value="1"
                                               id="setting_{{ $setting->key }}"
                                               {{ old("settings.{$setting->key}", $setting->value) == 'true' || old("settings.{$setting->key}", $setting->value) == '1' ? 'checked' : '' }}
                                               class="form-check-input">
                                        <label class="form-check-label" for="setting_{{ $setting->key }}">
                                            {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        </label>
                                    </div>
                                    @if($setting->description)
                                        <div class="form-description">{{ $setting->description }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                    @elseif($group === 'system')
                        <div class="form-section">
                            <h3 class="form-section-title">System Configuration</h3>
                            <div class="empty-state">
                                <i class="fas fa-cog"></i>
                                <p>System settings coming soon</p>
                            </div>
                        </div>
                    @endif

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="location.reload()">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save {{ ucfirst($group) }} Settings
                        </button>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-cog"></i>
                        <p>No settings available for this category</p>
                    </div>
                @endif
            </form>
        </div>
    @endforeach
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching functionality
        const tabs = document.querySelectorAll('.settings-tab');
        const contents = document.querySelectorAll('.settings-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');

                // Remove active class from all tabs and contents
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));

                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                document.getElementById('tab-' + targetTab).classList.add('active');
            });
        });

        // Dark mode functionality
        function applyDarkMode(isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                document.documentElement.setAttribute('data-theme', 'light');
            }
        }

        // Check dark mode setting on page load
        const darkModeCheckbox = document.getElementById('setting_dark_mode');
        if (darkModeCheckbox) {
            const isDarkMode = darkModeCheckbox.checked;
            applyDarkMode(isDarkMode);

            // Listen for changes to dark mode checkbox
            darkModeCheckbox.addEventListener('change', function() {
                applyDarkMode(this.checked);
                
                // Save to localStorage for immediate effect across all pages
                localStorage.setItem('darkMode', this.checked ? 'true' : 'false');
                
                // Trigger storage event for other tabs/pages
                window.dispatchEvent(new StorageEvent('storage', {
                    key: 'darkMode',
                    newValue: this.checked ? 'true' : 'false'
                }));
            });
        }

        // Also check localStorage for immediate dark mode on page load
        const savedDarkMode = localStorage.getItem('darkMode');
        if (savedDarkMode === 'true' && darkModeCheckbox) {
            darkModeCheckbox.checked = true;
            applyDarkMode(true);
        }

        // Apply dark mode when form is submitted (before page reload)
        const forms = document.querySelectorAll('.settings-form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const darkModeInput = document.getElementById('setting_dark_mode');
                if (darkModeInput) {
                    localStorage.setItem('darkMode', darkModeInput.checked ? 'true' : 'false');
                }
            });
        });
    });
</script>
@endsection

