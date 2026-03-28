<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HouseSync') - Landlord Portal</title>
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
        .dashboard-container { display: flex; min-height: 100vh; }
        aside.sidebar {
            background: linear-gradient(180deg, #ea580c 0%, #dc2626 100%);
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
        .profile-avatar{width:32px;height:32px;border-radius:50%;background:#ea580c;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600}
        .dropdown{position:relative}
        .dropdown-menu{position:absolute;right:0;top:calc(100% + 8px);background:#fff;border:1px solid #e2e8f0;border-radius:10px;min-width:220px;box-shadow:0 8px 24px rgba(0,0,0,.08);display:none;z-index:1050;padding:.4rem}
        .dropdown-menu.show{display:block}
        .dropdown-item{display:flex;align-items:center;gap:.6rem;padding:.6rem .8rem;border-radius:8px;color:#1e293b;text-decoration:none}
        .dropdown-item:hover{background:#f8fafc;text-decoration:none}
        /* --- GLOBAL DASHBOARD STYLES --- */
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
            background: linear-gradient(135deg, #f97316, #ea580c);
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
            border-left: 4px solid #f97316;
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
            border-left: 4px solid #f97316;
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
            border-left: 4px solid #a855f7;
        }
        .main-content .occupancy-percentage { font-size: 2rem; font-weight: 700; color: #a855f7; }
        .main-content .occupancy-label { font-size: 0.875rem; color: #64748b; }
        .main-content .badge-count { background: #ef4444; color: white; border-radius: 9999px; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600; margin-left: 8px; }
        .main-content .activity-section, .main-content .quick-actions, .main-content .property-summary { background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1.5rem; }
        .main-content .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
        .main-content .section-title { font-size: 1.25rem; font-weight: 600; color: #1e293b; }
        .main-content .btn-primary { background: #f97316; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.90rem; font-weight: 500; transition: all 0.2s; }
        .main-content .btn-primary:hover { background: #ea580c; color: #fff; }
        .main-content .status-badge.status-available { background: #d1fae5; color: #059669; }
        .main-content .status-badge.status-occupied { background: #dbeafe; color: #2563eb; }
        .main-content .status-badge.status-maintenance { background: #fef3c7; color: #d97706; }
        /* Responsive Tweaks */
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
            border-color: #f97316 !important;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1) !important;
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

        /* ========== COMPREHENSIVE DARK MODE - Page Sections ========== */
        body.dark-mode .page-section,
        body.dark-mode .form-container,
        body.dark-mode .bulk-creation-container .form-container,
        body.dark-mode .settings-section {
            background: #1e293b !important;
            color: #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .section-header {
            border-color: #334155 !important;
        }

        body.dark-mode .section-title,
        body.dark-mode .page-title,
        body.dark-mode .form-section-title {
            color: #f1f5f9 !important;
        }

        body.dark-mode .section-subtitle,
        body.dark-mode .page-subtitle {
            color: #94a3b8 !important;
        }

        /* ========== Form Sections & Inputs ========== */
        body.dark-mode .form-section {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        body.dark-mode .form-section-title {
            border-color: #334155 !important;
        }

        body.dark-mode .form-help,
        body.dark-mode .form-text,
        body.dark-mode .form-text.text-muted,
        body.dark-mode small.form-text {
            color: #94a3b8 !important;
        }

        body.dark-mode .form-select {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode input[disabled],
        body.dark-mode input:disabled,
        body.dark-mode .form-control:disabled,
        body.dark-mode .form-control[disabled] {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
            cursor: not-allowed;
        }

        /* ========== Breadcrumbs ========== */
        body.dark-mode .breadcrumb {
            color: #94a3b8 !important;
        }

        body.dark-mode .breadcrumb a {
            color: #f97316 !important;
        }

        body.dark-mode .breadcrumb-item.active {
            color: #94a3b8 !important;
        }

        body.dark-mode .page-title-box {
            color: #e2e8f0;
        }

        body.dark-mode .page-title-box .page-title {
            color: #f1f5f9 !important;
        }

        /* ========== Bootstrap Cards ========== */
        body.dark-mode .card {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0;
        }

        body.dark-mode .card-body {
            color: #e2e8f0;
        }

        body.dark-mode .card-header {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }

        /* ========== Tables (Bootstrap + Custom) ========== */
        body.dark-mode .table {
            color: #e2e8f0 !important;
        }

        body.dark-mode .table-light th,
        body.dark-mode .table thead th,
        body.dark-mode thead th {
            background: #0f172a !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }

        body.dark-mode .table td,
        body.dark-mode .table th {
            border-color: #334155 !important;
        }

        body.dark-mode .table-hover tbody tr:hover {
            background: #0f172a !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .table-striped tbody tr:nth-of-type(odd) {
            background: rgba(15, 23, 42, 0.4) !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .table-warning {
            background: rgba(120, 53, 15, 0.3) !important;
            color: #fbbf24 !important;
        }

        body.dark-mode .activity-table th {
            background: #0f172a !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }

        body.dark-mode .activity-table td {
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        /* ========== Properties List (apartments.blade.php) ========== */
        body.dark-mode .properties-list {
            background: #1e293b !important;
        }

        body.dark-mode .list-header {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }

        body.dark-mode .list-row {
            border-color: #334155 !important;
            color: #e2e8f0;
        }

        body.dark-mode .list-row:hover {
            background: #0f172a !important;
        }

        body.dark-mode .btn-icon {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }

        body.dark-mode .btn-icon:hover {
            background: #334155 !important;
            color: #f1f5f9 !important;
        }

        /* ========== Progress Indicator ========== */
        body.dark-mode .progress-indicator {
            background: #1e293b !important;
        }

        body.dark-mode .progress-step i {
            background: #334155;
            color: #94a3b8;
        }

        body.dark-mode .progress-step span {
            color: #94a3b8;
        }

        body.dark-mode .progress-connector {
            background: #334155;
        }

        /* ========== Amenity Items ========== */
        body.dark-mode .amenity-item {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        body.dark-mode .amenity-item:hover {
            border-color: #f97316 !important;
            background: rgba(249, 115, 22, 0.1) !important;
        }

        body.dark-mode .amenity-item label {
            color: #e2e8f0 !important;
        }

        /* ========== Property Info / Preview Boxes ========== */
        body.dark-mode .property-info {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        body.dark-mode .property-info h4 {
            color: #f1f5f9 !important;
        }

        body.dark-mode .property-info p {
            color: #94a3b8 !important;
        }

        body.dark-mode .property-preview {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        body.dark-mode .preview-title {
            color: #f1f5f9 !important;
        }

        body.dark-mode .preview-item {
            color: #94a3b8 !important;
        }

        /* ========== Empty State ========== */
        body.dark-mode .empty-state {
            color: #94a3b8;
        }

        body.dark-mode .empty-title {
            color: #f1f5f9 !important;
        }

        body.dark-mode .empty-text {
            color: #94a3b8 !important;
        }

        /* ========== Quick Actions ========== */
        body.dark-mode .action-btn {
            background: #0f172a !important;
            border: 1px solid #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .action-btn:hover {
            background: #334155 !important;
            color: #f97316 !important;
        }

        /* ========== Billing & Financial Summary ========== */
        body.dark-mode .billing-content .page-header {
            color: #e2e8f0;
        }

        body.dark-mode .billing-content .page-header h2 {
            color: #f1f5f9 !important;
        }

        body.dark-mode .billing-content .page-header p {
            color: #94a3b8 !important;
        }

        body.dark-mode .financial-summary .summary-card {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        body.dark-mode .financial-summary .summary-card h3 {
            color: #94a3b8 !important;
        }

        body.dark-mode .financial-summary .summary-card .summary-value {
            color: #f1f5f9 !important;
        }

        body.dark-mode .payments-section {
            background: #1e293b !important;
        }

        body.dark-mode .payments-section .section-header {
            border-color: #334155 !important;
        }

        body.dark-mode .payments-section .section-header h3 {
            color: #f1f5f9 !important;
        }

        body.dark-mode .payments-table table {
            color: #e2e8f0;
        }

        body.dark-mode .payments-table th {
            background: #0f172a !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }

        body.dark-mode .payments-table td {
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .payments-table tbody tr:hover {
            background: #0f172a !important;
        }

        body.dark-mode .billing-sidebar {
            background: #1e293b !important;
        }

        body.dark-mode .billing-sidebar h4 {
            color: #f1f5f9 !important;
        }

        body.dark-mode .billing-sidebar ul {
            color: #94a3b8 !important;
        }

        body.dark-mode .billing-sidebar .quick-actions {
            background: #1e293b !important;
        }

        /* Billing status badges */
        body.dark-mode .status.paid { background: #064e3b !important; color: #6ee7b7 !important; }
        body.dark-mode .status.unpaid { background: #7f1d1d !important; color: #fca5a5 !important; }
        body.dark-mode .status.partially_paid { background: #78350f !important; color: #fbbf24 !important; }
        body.dark-mode .status.overdue { background: #450a0a !important; color: #fca5a5 !important; }

        /* ========== Settings Page ========== */
        body.dark-mode .settings-section {
            border-color: #334155 !important;
        }

        body.dark-mode .settings-section h3 {
            color: #f1f5f9 !important;
            border-color: #334155 !important;
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

        /* ========== Avatar Titles ========== */
        body.dark-mode .avatar-title {
            color: #e2e8f0;
        }

        body.dark-mode .avatar-title.bg-soft-primary {
            background: rgba(59, 130, 246, 0.15) !important;
        }

        body.dark-mode .avatar-title.bg-soft-success {
            background: rgba(16, 185, 129, 0.15) !important;
        }

        body.dark-mode .avatar-title.bg-soft-warning {
            background: rgba(245, 158, 11, 0.15) !important;
        }

        body.dark-mode .avatar-title.bg-soft-info {
            background: rgba(6, 182, 212, 0.15) !important;
        }

        /* ========== Credentials Box ========== */
        body.dark-mode .credentials-box {
            background: #0f172a !important;
            border-color: #065f46 !important;
        }

        /* ========== Force Delete / Custom Modals ========== */
        body.dark-mode #force-delete-modal > div {
            background: #1e293b !important;
            color: #e2e8f0;
            border: 1px solid #334155;
        }

        body.dark-mode #force-delete-modal p {
            color: #94a3b8 !important;
        }

        body.dark-mode #force-delete-modal {
            background: rgba(0, 0, 0, 0.65) !important;
        }

        body.dark-mode .force-delete-inner {
            background: #1e293b !important;
            color: #e2e8f0;
            border: 1px solid #334155;
        }

        body.dark-mode .force-delete-text {
            color: #94a3b8 !important;
        }

        /* ========== Bootstrap Modal Dark Mode ========== */
        body.dark-mode .modal-content {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0;
        }

        body.dark-mode .modal-header {
            border-color: #334155 !important;
            color: #f1f5f9;
        }

        body.dark-mode .modal-title {
            color: #f1f5f9 !important;
        }

        body.dark-mode .modal-footer {
            border-color: #334155 !important;
        }

        body.dark-mode .modal-body {
            color: #e2e8f0;
        }

        body.dark-mode .modal-body .text-muted {
            color: #94a3b8 !important;
        }

        body.dark-mode .modal-body .form-label {
            color: #94a3b8 !important;
        }

        body.dark-mode .modal-body p {
            color: #e2e8f0;
        }

        body.dark-mode .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* ========== Bootstrap Overrides for Dark Mode ========== */
        body.dark-mode .bg-light {
            background: #0f172a !important;
        }

        body.dark-mode .bg-opacity-10,
        body.dark-mode .bg-opacity-25 {
            background-color: transparent !important;
        }

        body.dark-mode .bg-info.bg-opacity-10 {
            background-color: rgba(6, 182, 212, 0.1) !important;
        }

        body.dark-mode .bg-success.bg-opacity-10 {
            background-color: rgba(16, 185, 129, 0.1) !important;
        }

        body.dark-mode .bg-warning.bg-opacity-25 {
            background-color: rgba(245, 158, 11, 0.15) !important;
        }

        body.dark-mode .border {
            border-color: #334155 !important;
        }

        body.dark-mode .border-start {
            border-color: inherit !important;
        }

        body.dark-mode hr {
            border-color: #334155 !important;
            opacity: 0.5;
        }

        body.dark-mode .dropdown-menu {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        body.dark-mode .dropdown-item {
            color: #e2e8f0 !important;
        }

        body.dark-mode .dropdown-item:hover {
            background: #334155 !important;
        }

        body.dark-mode .dropdown-divider {
            border-color: #334155 !important;
        }

        body.dark-mode .input-group .btn-outline-primary {
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }

        body.dark-mode .input-group .btn-outline-primary:hover {
            background: #334155 !important;
            color: #f1f5f9 !important;
        }

        body.dark-mode code {
            background: #0f172a;
            color: #f97316;
        }

        body.dark-mode .border-start.border-4 {
            border-left-color: inherit !important;
        }

        /* ========== Inline color overrides ========== */
        body.dark-mode [style*="color: #1e293b"],
        body.dark-mode [style*="color: #0f172a"],
        body.dark-mode [style*="color:#1e293b"],
        body.dark-mode [style*="color:#0f172a"] {
            color: #f1f5f9 !important;
        }

        body.dark-mode [style*="color: #64748b"],
        body.dark-mode [style*="color:#64748b"],
        body.dark-mode [style*="color: #666"],
        body.dark-mode [style*="color:#666"] {
            color: #94a3b8 !important;
        }

        body.dark-mode [style*="color: #94a3b8"],
        body.dark-mode [style*="color:#94a3b8"] {
            color: #64748b !important;
        }

        body.dark-mode [style*="background: white"],
        body.dark-mode [style*="background:white"],
        body.dark-mode [style*="background-color: white"],
        body.dark-mode [style*="background-color:white"],
        body.dark-mode [style*="background: #fff"],
        body.dark-mode [style*="background:#fff"] {
            background: #1e293b !important;
        }

        body.dark-mode [style*="border: 1px solid #e2e8f0"],
        body.dark-mode [style*="border:1px solid #e2e8f0"] {
            border-color: #334155 !important;
        }

        body.dark-mode [style*="background: #f8fafc"],
        body.dark-mode [style*="background:#f8fafc"],
        body.dark-mode [style*="background-color: #f8fafc"],
        body.dark-mode [style*="background: #f1f5f9"],
        body.dark-mode [style*="background:#f1f5f9"] {
            background: #0f172a !important;
        }

        body.dark-mode .form-actions {
            border-color: #334155 !important;
        }

        /* ========== Form Labels & Controls (Global) ========== */
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
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15) !important;
        }

        body.dark-mode .form-control::placeholder {
            color: #64748b !important;
        }

        body.dark-mode textarea.form-control {
            background: #0f172a !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .form-check-label {
            color: #e2e8f0 !important;
        }

        body.dark-mode .form-container {
            color: #e2e8f0;
        }

        body.dark-mode .form-grid label {
            color: #94a3b8 !important;
        }

        /* ========== Chat Page ========== */
        body.dark-mode .chat-container,
        body.dark-mode .chat-sidebar,
        body.dark-mode .chat-main {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        body.dark-mode .chat-header {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        body.dark-mode .chat-messages {
            background: #0f172a !important;
        }

        body.dark-mode .chat-input-container {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        body.dark-mode .conversation-item {
            border-color: #334155 !important;
        }

        body.dark-mode .conversation-item:hover,
        body.dark-mode .conversation-item.active {
            background: #0f172a !important;
        }

        body.dark-mode .message-bubble {
            background: #334155 !important;
            color: #e2e8f0;
        }

        body.dark-mode .message-bubble.sent {
            background: #f97316 !important;
            color: #fff;
        }

        /* ========== Security Page ========== */
        body.dark-mode .rfid-card {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        body.dark-mode .access-log-item {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        /* ========== Sort/Filter Dropdowns ========== */
        body.dark-mode .sort-dropdown select,
        body.dark-mode #apartmentFilter,
        body.dark-mode #propertySort,
        body.dark-mode #unitSort {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        /* ========== Bootstrap Buttons in Dark Mode ========== */
        body.dark-mode .btn-secondary {
            background: #475569 !important;
            border-color: #475569 !important;
        }

        body.dark-mode .btn-outline-secondary {
            border-color: #475569 !important;
            color: #94a3b8 !important;
        }

        body.dark-mode .btn-outline-secondary:hover {
            background: #475569 !important;
            color: #f1f5f9 !important;
        }

        /* ========== Occupancy Bar Background ========== */
        body.dark-mode .occupancy-bar,
        body.dark-mode [style*="background: #e2e8f0"] {
            background: #334155 !important;
        }

        /* ========== Chevron / Icons Inline ========== */
        body.dark-mode .fa-chevron-down,
        body.dark-mode [style*="color:#64748b"] i {
            color: #94a3b8 !important;
        }

        /* ========== Bulk Edit / Tenant History / Other Pages ========== */
        body.dark-mode .bulk-creation-container .property-info,
        body.dark-mode .bulk-creation-container .alert-info {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0;
        }

        body.dark-mode [style*="border: 2px dashed #cbd5e1"] {
            border-color: #334155 !important;
            background: #0f172a !important;
        }

        /* ========== Spinner ========== */
        body.dark-mode .spinner-border {
            color: #f97316;
        }

        /* ========== Bootstrap Progress Bar ========== */
        body.dark-mode .progress {
            background: #334155 !important;
        }

        /* ========== Font Color Overrides for h5.text-muted ========== */
        body.dark-mode h5.text-muted {
            color: #94a3b8 !important;
        }

        body.dark-mode .font-14 {
            color: #f1f5f9 !important;
        }

        /* ========== Maintenance Page Overrides ========== */
        body.dark-mode .maintenance-content,
        body.dark-mode .maintenance-card {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0;
        }

        body.dark-mode .maintenance-header {
            border-color: #334155 !important;
        }

        /* ========== Chat / Messages Dark Mode ========== */
        body.dark-mode .chat-container {
            background: #1e293b !important;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .chat-sidebar,
        body.dark-mode .chat-main {
            background: #1e293b !important;
        }

        body.dark-mode .chat-sidebar-header,
        body.dark-mode .chat-header,
        body.dark-mode .chat-input-area {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        body.dark-mode .chat-messages {
            background: #0f172a !important;
        }

        body.dark-mode .conversation-item:hover,
        body.dark-mode .conversation-item.active {
            background: #334155 !important;
        }

        body.dark-mode .conversation-name {
            color: #f1f5f9 !important;
        }

        body.dark-mode .conversation-preview {
            color: #94a3b8 !important;
        }

        body.dark-mode .chat-header-details h3,
        body.dark-mode .chat-header-details p {
            color: #f1f5f9 !important;
        }

        body.dark-mode .chat-header-details p {
            color: #94a3b8 !important;
        }

        body.dark-mode .message.received .message-content {
            background: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .message-text {
            color: inherit;
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

        body.dark-mode .chat-input,
        body.dark-mode .chat-input-area textarea {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .chat-input::placeholder {
            color: #64748b !important;
        }

        body.dark-mode .header-action-btn {
            background: #334155 !important;
            border-color: #475569 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .header-action-btn:hover {
            background: #475569 !important;
        }

        body.dark-mode .input-action-btn {
            background: #334155 !important;
            color: #94a3b8 !important;
        }

        body.dark-mode .input-action-btn:hover {
            background: #475569 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .attachment-preview,
        body.dark-mode .attachment-preview-item {
            background: #1e293b !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .back-btn {
            color: #94a3b8 !important;
        }

        body.dark-mode .back-btn:hover {
            background: #334155 !important;
            color: #e2e8f0 !important;
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
    </style>
    @stack('styles')
</head>
<body>
<div class="dashboard-container" id="dashboardContainer">
    <aside class="sidebar">
        <div class="sidebar-header">
            <button class="sidebar-hamburger" id="sidebarCollapseBtn" aria-label="Toggle navigation"><i class="fas fa-bars"></i></button>
            <span class="portal-title-label">Landlord Portal</span>
        </div>
        <nav class="sidebar-nav">
            <a class="nav-link{{ request()->routeIs('landlord.dashboard') ? ' active' : '' }}" href="{{ route('landlord.dashboard') }}">
                <span class="nav-icon"><i class="fas fa-home"></i></span> <span class="nav-label">Dashboard</span>
            </a>
            <a class="nav-link{{ request()->routeIs('landlord.apartments') ? ' active' : '' }}" href="{{ route('landlord.apartments') }}">
                <span class="nav-icon"><i class="fas fa-building"></i></span> <span class="nav-label">My Properties</span>
            </a>
            <a class="nav-link{{ request()->routeIs('landlord.units') ? ' active' : '' }}" href="{{ route('landlord.units') }}">
                <span class="nav-icon"><i class="fas fa-door-open"></i></span> <span class="nav-label">My Units</span>
            </a>
            <a class="nav-link{{ request()->routeIs('landlord.tenant-assignments') ? ' active' : '' }}" href="{{ route('landlord.tenant-assignments') }}">
                <span class="nav-icon"><i class="fas fa-users"></i></span> <span class="nav-label">Tenant Assignments</span>
            </a>
            <a class="nav-link{{ request()->routeIs('landlord.tenant-history') ? ' active' : '' }}" href="{{ route('landlord.tenant-history') }}">
                <span class="nav-icon"><i class="fas fa-history"></i></span> <span class="nav-label">Tenant History</span>
            </a>
            <a class="nav-link{{ request()->routeIs('landlord.staff*') ? ' active' : '' }}" href="{{ route('landlord.staff') }}">
                <span class="nav-icon"><i class="fas fa-tools"></i></span> <span class="nav-label">Staff</span>
            </a>
            <a class="nav-link{{ request()->routeIs('landlord.security*') ? ' active' : '' }}" href="{{ route('landlord.security.index') }}">
                <span class="nav-icon"><i class="fas fa-shield-alt"></i></span> <span class="nav-label">Security</span>
            </a>
            <a class="nav-link{{ request()->routeIs('landlord.chat*') ? ' active' : '' }}" href="{{ route('landlord.chat') }}">
                <span class="nav-icon"><i class="fas fa-comments"></i></span> 
                <span class="nav-label">Messages</span>
                @if(auth()->user()->total_unread_messages > 0)
                    <span class="badge-count">{{ auth()->user()->total_unread_messages }}</span>
                @endif
            </a>
            <a class="nav-link{{ request()->routeIs('landlord.payments') ? ' active' : '' }}" href="{{ route('landlord.payments') }}">
                <span class="nav-icon"><i class="fas fa-credit-card"></i></span> <span class="nav-label">Payments</span>
            </a>
            <a class="nav-link{{ request()->routeIs('landlord.maintenance*') ? ' active' : '' }}" href="{{ route('landlord.maintenance.index') }}"><span class="nav-icon"><i class="fas fa-wrench"></i></span> <span class="nav-label">Maintenance</span></a>
            <a class="nav-link{{ request()->routeIs('landlord.announcements*') ? ' active' : '' }}" href="{{ route('landlord.announcements.index') }}"><span class="nav-icon"><i class="fas fa-bullhorn"></i></span> <span class="nav-label">Announcements</span></a>
            <a class="nav-link{{ request()->routeIs('landlord.reports*') ? ' active' : '' }}" href="{{ route('landlord.reports.index') }}"><span class="nav-icon"><i class="fas fa-chart-bar"></i></span> <span class="nav-label">Reports</span></a>
        </nav>
        <div class="sidebar-footer mt-auto"></div>
    </aside>
    <main class="main-content">
        <div class="topbar">
            @include('partials.notification-bell')
            <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode">
                <i class="fas fa-moon" id="darkModeIcon"></i>
            </button>
            <div class="dropdown" id="llProfileDropdown">
                <div class="profile-btn" id="llProfileBtn">
                    @php 
                        $user = auth()->user();
                        // Query profile directly from database to ensure fresh data
                        $landlordProfile = \App\Models\LandlordProfile::where('user_id', $user->id)->first();
                        // Get name directly from profile, checking if it exists and is not empty or "User" or "New User"
                        $profileName = $landlordProfile ? trim($landlordProfile->name ?? '') : '';
                        $landlordName = (!empty($profileName) && $profileName !== 'User' && $profileName !== 'New User') 
                            ? $profileName 
                            : ($user->email ?? 'Landlord');
                    @endphp
                    <div class="profile-avatar">{{ mb_substr($landlordName, 0, 1) }}</div>
                    <span class="d-none d-sm-inline">{{ $landlordName }}</span>
                    <i class="fas fa-chevron-down" style="font-size:.85rem;color:#64748b"></i>
                </div>
                <div class="dropdown-menu" id="llDropdownMenu">
                    <a href="{{ route('landlord.settings') }}" class="dropdown-item"><i class="fas fa-user-cog"></i> Settings</a>
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
            try { localStorage.setItem('landlordSidebarCollapsed', cont.classList.contains('collapsed') ? '1' : '0'); } catch (e) {}
        }
    };
    // Respect stored state
    (function() {
        var cont = document.getElementById('dashboardContainer');
        try { if (localStorage.getItem('landlordSidebarCollapsed') === '1') cont.classList.add('collapsed'); } catch (e) {}
    })();
    (function(){
        var btn=document.getElementById('llProfileBtn');
        var menu=document.getElementById('llDropdownMenu');
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