@extends('layouts.super-admin-app')

@section('title', 'Pending Approvals')

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
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
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

        .table-container {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-table {
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
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
            vertical-align: top;
            word-break: normal;
            overflow-wrap: normal;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
            vertical-align: top;
            word-break: normal;
            overflow-wrap: normal;
        }

        /* Long unbroken strings only where needed */
        .data-table th:nth-child(2),
        .data-table td:nth-child(2),
        .data-table th:nth-child(4),
        .data-table td:nth-child(4) {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .data-table td:nth-child(3) {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 0;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
        }

        .data-table th:nth-child(1),
        .data-table td:nth-child(1) { width: 12%; min-width: 4.5rem; }

        .data-table th:nth-child(2),
        .data-table td:nth-child(2) { width: 16%; min-width: 6rem; }

        .data-table th:nth-child(3),
        .data-table td:nth-child(3) { width: 9%; min-width: 4.5rem; }

        .data-table th:nth-child(4),
        .data-table td:nth-child(4) { width: 14%; min-width: 5rem; }

        .data-table th:nth-child(5),
        .data-table td:nth-child(5) { width: 11%; min-width: 5rem; }

        .data-table th:nth-child(6),
        .data-table td:nth-child(6) { width: 9%; min-width: 6.75rem; }

        .data-table th:nth-child(7),
        .data-table td:nth-child(7) { width: 11%; min-width: 7.75rem; }

        .data-table th:nth-child(8),
        .data-table td:nth-child(8) { width: 10%; min-width: 9.5rem; }

        .data-table th:nth-child(6),
        .data-table th:nth-child(7) {
            white-space: nowrap;
        }

        .cell-business-info {
            max-width: 100%;
            line-height: 1.35;
        }

        .landlord-actions {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
            min-width: min(100%, 9.25rem);
        }

        .landlord-actions form {
            display: block;
            margin: 0;
            width: 100%;
        }

        .landlord-actions .btn {
            width: 100%;
            justify-content: center;
            box-sizing: border-box;
            white-space: nowrap;
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

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            min-height: 1.625rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            text-transform: uppercase;
            white-space: nowrap;
            max-width: 100%;
            word-break: normal;
            overflow-wrap: normal;
            letter-spacing: 0.025em;
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

        .status-docs-complete {
            background: #d1fae5;
            color: #059669;
        }

        .status-docs-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .btn-success:disabled,
        .btn-success[disabled] {
            opacity: 0.55;
            cursor: not-allowed;
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

        .alert-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .alert-warning {
            background: #fef3c7;
            border: 1px solid #fde68a;
            color: #d97706;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            font-size: 4rem;
            color: #94a3b8;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .empty-text {
            color: #64748b;
            margin-bottom: 2rem;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 2rem;
            border-radius: 1rem;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .preview-modal-content {
            max-width: 1000px !important;
            width: 95% !important;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            margin: 3vh auto !important;
            padding: 1rem 1rem 0.75rem 1rem !important;
        }

        .preview-container {
            flex: 1;
            min-height: 60vh;
            max-height: 70vh;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            overflow: hidden;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-container img,
        .preview-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .preview-scroll-area {
            width: 100%;
            height: 100%;
            overflow: auto;
        }

        .preview-scroll-area img {
            width: 100%;
            height: auto;
            display: block;
            transform-origin: top center;
            transition: transform 0.15s ease;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        .close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #1e293b;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        @media (max-width: 1200px) {
            .page-section {
                padding: 1.35rem;
            }

            .data-table th,
            .data-table td {
                padding: 0.65rem 0.5rem;
                font-size: 0.8125rem;
            }
        }

        @media (max-width: 768px) {
            .content-header h1 {
                font-size: 1.5rem;
            }

            .page-section {
                padding: 1rem;
            }

            .section-title {
                font-size: 1.2rem;
            }

            .data-table th,
            .data-table td {
                padding: 0.5rem 0.35rem;
                font-size: 0.72rem;
            }

            .data-table th {
                letter-spacing: 0.02em;
            }

            .status-badge {
                font-size: 0.65rem;
                min-height: 1.35rem;
                padding: 0.2rem 0.45rem;
                letter-spacing: 0.02em;
            }

            .btn-sm {
                padding: 0.35rem 0.5rem;
                font-size: 0.7rem;
            }
        }

        /* Dark Mode Styles */
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

        body.dark-mode .status-pending {
            background: #78350f !important;
            color: #fbbf24 !important;
        }

        body.dark-mode .status-approved {
            background: #064e3b !important;
            color: #6ee7b7 !important;
        }

        body.dark-mode .status-rejected {
            background: #7f1d1d !important;
            color: #fca5a5 !important;
        }

        body.dark-mode .status-docs-complete {
            background: #064e3b !important;
            color: #6ee7b7 !important;
        }

        body.dark-mode .status-docs-pending {
            background: #78350f !important;
            color: #fbbf24 !important;
        }

        body.dark-mode .alert-success {
            background: #064e3b !important;
            border-color: #065f46 !important;
            color: #6ee7b7 !important;
        }

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

        body.dark-mode .empty-icon {
            color: #475569 !important;
        }

        body.dark-mode .empty-title {
            color: #f1f5f9 !important;
        }

        body.dark-mode .empty-text {
            color: #94a3b8 !important;
        }

        body.dark-mode .modal-content {
            background: #1e293b !important;
            color: #e2e8f0;
        }

        body.dark-mode .modal-title {
            color: #f1f5f9 !important;
        }

        body.dark-mode .close {
            color: #94a3b8 !important;
        }

        body.dark-mode .close:hover {
            color: #e2e8f0 !important;
        }
    </style>
@endpush

@section('content')
            <!-- Header -->
            <div class="content-header">
                <div>
                    <h1>Pending Landlord Approvals</h1>
                    <p style="color: #64748b; margin-top: 0.5rem;">Review and approve landlord registration requests</p>
                </div>
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

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ \App\Models\User::pendingLandlords()->count() }}</div>
                    <div class="stat-label">Pending Approvals</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ \App\Models\User::approvedLandlords()->count() }}</div>
                    <div class="stat-label">Approved Landlords</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ \App\Models\User::rejectedLandlords()->count() }}</div>
                    <div class="stat-label">Rejected Applications</div>
                </div>
            </div>

            <!-- Pending Landlords Section -->
            <div class="page-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Landlord Registration Requests</h2>
                        <p class="section-subtitle">Review applications, verify documents, and reject landlord accounts when needed</p>
                    </div>
                </div>

                @php
                    $visiblePendingLandlords = $pendingLandlords;
                @endphp
                @if($visiblePendingLandlords->count() > 0)
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Business Info</th>
                                    <th>Registered</th>
                                    <th>Status</th>
                                    <th>Documents</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($visiblePendingLandlords as $landlord)
                                    <tr>
                                        <td>
                                            <div>
                                                <div style="font-weight: 600;">{{ $landlord->name }}</div>
                                                <div style="font-size: 0.75rem; color: #64748b;">ID: #{{ $landlord->id }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $landlord->email }}</td>
                                        <td>{{ $landlord->phone ?? 'N/A' }}</td>
                                        <td>
                                            <div class="cell-business-info" title="{{ $landlord->business_info }}">
                                                {{ $landlord->business_info ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $landlord->created_at->format('M d, Y') }}</div>
                                            <div style="font-size: 0.75rem; color: #64748b;">{{ $landlord->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ $landlord->status }}">
                                                {{ ucfirst($landlord->status) }}
                                            </span>
                                        </td>
                                        @php
                                            $docRows = $landlord->landlordDocuments;
                                            $docTotal = $docRows->count();
                                            $docVerified = $docRows->where('verification_status', 'verified')->count();
                                            $docsAllVerified = $landlord->landlordDocumentsFullyVerified();
                                        @endphp
                                        <td>
                                            @if($docTotal === 0)
                                                <span class="status-badge status-docs-pending" title="No files on record">No uploads</span>
                                            @elseif($docsAllVerified)
                                                <span class="status-badge status-docs-complete">All verified</span>
                                            @else
                                                <span class="status-badge status-docs-pending" title="Verify each file in Verify Documents">{{ $docVerified }}/{{ $docTotal }} verified</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="landlord-actions">
                                                <button type="button" class="btn btn-primary btn-sm" onclick='showDocumentsModal({{ $landlord->id }}, @json($landlord->name))'>
                                                    <i class="fas fa-file-alt"></i> Verify Documents
                                                </button>
                                                @if($landlord->landlordProfile)
                                                    <button type="button" class="btn btn-danger btn-sm" onclick='showRejectModal({{ $landlord->id }}, @json($landlord->name))'>
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                @else
                                                    <span class="text-muted" style="font-size: 0.75rem; color: #64748b;">Already processed</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($pendingLandlords->hasPages())
                        <div style="margin-top: 2rem;">
                            {{ $pendingLandlords->links() }}
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h3 class="empty-title">No Pending Approvals</h3>
                        <p class="empty-text">All landlord applications have been reviewed. New applications will appear here.</p>
                        <a href="{{ route('super-admin.users') }}" class="btn btn-primary">
                            <i class="fas fa-users"></i> View All Users
                        </a>
                    </div>
                @endif
            </div>
    <!-- Documents Modal -->
    <div id="documentsModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title">Landlord Documents</h3>
                <button type="button" class="close" onclick="closeDocumentsModal()">&times;</button>
            </div>
            <div id="documentsContent">
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #3b82f6;"></i>
                    <p style="margin-top: 1rem;">Loading documents...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Preview Modal -->
    <div id="filePreviewModal" class="modal">
        <div class="modal-content preview-modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="filePreviewTitle">Document Preview</h3>
                <button type="button" class="close" onclick="closeFilePreviewModal()">&times;</button>
            </div>
            <div id="filePreviewContent" class="preview-container">
                <div style="color: #64748b;">Loading preview...</div>
            </div>
            <div style="padding: 0.75rem 0.25rem 0.5rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="scrollPreviewContent(-320)">
                    <i class="fas fa-arrow-up"></i> Scroll Up
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="scrollPreviewContent(320)">
                    <i class="fas fa-arrow-down"></i> Scroll Down
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="zoomPreviewImage(0.1)">
                    <i class="fas fa-search-plus"></i> Zoom In
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="zoomPreviewImage(-0.1)">
                    <i class="fas fa-search-minus"></i> Zoom Out
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetPreviewZoom()">
                    <i class="fas fa-compress"></i> Reset Zoom
                </button>
                <a id="filePreviewDownload" href="#" class="btn btn-primary btn-sm" download>
                    <i class="fas fa-download"></i> Download File
                </a>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeFilePreviewModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Reject Landlord Application</h3>
                <button type="button" class="close" onclick="closeRejectModal()">&times;</button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Landlord Name</label>
                    <input type="text" id="landlordName" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Rejection Reason *</label>
                    <textarea name="reason" class="form-control" placeholder="Please provide a reason for rejection..." required></textarea>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject Application
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentPreviewObjectUrl = null;
        let currentImageZoom = 1;
        let currentPreviewController = null;
        let documentsModalLandlordId = null;

        function getFileExtension(fileName, fileUrl) {
            const source = (fileName || fileUrl || '').split('?')[0].toLowerCase();
            const parts = source.split('.');
            return parts.length > 1 ? parts.pop() : '';
        }

        function openFilePreviewModal(fileUrl, fileName) {
            const modal = document.getElementById('filePreviewModal');
            const title = document.getElementById('filePreviewTitle');
            const content = document.getElementById('filePreviewContent');
            const downloadBtn = document.getElementById('filePreviewDownload');

            if (!modal || !title || !content || !downloadBtn) return;

            // Cancel any in-flight request to avoid stale responses
            if (currentPreviewController) {
                currentPreviewController.abort();
                currentPreviewController = null;
            }

            // Sanitize fileName for safe display (not for HTML insertion)
            const safeFileName = fileName ? String(fileName).replace(/[<>"'&]/g, '') : 'Document Preview';
            title.textContent = safeFileName;
            downloadBtn.href = fileUrl;
            downloadBtn.setAttribute('download', safeFileName);
            currentImageZoom = 1;

            modal.style.display = 'block';
            content.innerHTML = '<div style="color: #64748b;">Loading preview...</div>';

            const ext = getFileExtension(fileName, fileUrl);
            const isImageExt = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg'].includes(ext);
            const isPdfExt = ext === 'pdf';

            // Try direct src first (avoids CORS issues for same-origin URLs)
            if (isImageExt) {
                const img = document.createElement('img');
                img.src = fileUrl;
                img.alt = safeFileName; // Set via property, not innerHTML
                img.onerror = () => {
                    // Fallback to blob method if direct src fails
                    loadPreviewAsBlob(fileUrl, safeFileName, 'image');
                };
                content.innerHTML = '';
                content.appendChild(img);
                resetPreviewZoom();
            } else if (isPdfExt) {
                const iframe = document.createElement('iframe');
                iframe.src = fileUrl;
                iframe.title = safeFileName; // Set via property, not innerHTML
                iframe.onerror = () => {
                    // Fallback to blob method if direct src fails
                    loadPreviewAsBlob(fileUrl, safeFileName, 'pdf');
                };
                content.innerHTML = '';
                content.appendChild(iframe);
            } else {
                // Non-previewable file type
                content.innerHTML = `
                    <div style="text-align:center; padding: 2rem;">
                        <i class="fas fa-file" style="font-size:2rem; color:#64748b; margin-bottom: 0.75rem;"></i>
                        <p style="margin:0; color:#64748b;">This file type cannot be previewed inline.</p>
                    </div>
                `;
            }
        }

        function loadPreviewAsBlob(fileUrl, safeFileName, fileType) {
            // Create new AbortController for this request
            currentPreviewController = new AbortController();
            
            fetch(fileUrl, { signal: currentPreviewController.signal })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Unable to load file preview.');
                    }
                    return response.blob();
                })
                .then((blob) => {
                    if (currentPreviewObjectUrl) {
                        URL.revokeObjectURL(currentPreviewObjectUrl);
                    }
                    currentPreviewObjectUrl = URL.createObjectURL(blob);

                    const content = document.getElementById('filePreviewContent');
                    if (!content) return;

                    if (fileType === 'image') {
                        // Use DOM API to safely set alt attribute (prevents XSS)
                        const scrollArea = document.createElement('div');
                        scrollArea.className = 'preview-scroll-area';

                        const img = document.createElement('img');
                        img.src = currentPreviewObjectUrl;
                        img.alt = safeFileName; // Set via property, not string interpolation

                        scrollArea.appendChild(img);
                        content.innerHTML = '';
                        content.appendChild(scrollArea);
                        resetPreviewZoom();
                    } else if (fileType === 'pdf') {
                        // Use DOM API to safely set title attribute (prevents XSS)
                        const iframe = document.createElement('iframe');
                        iframe.src = currentPreviewObjectUrl;
                        iframe.title = safeFileName; // Set via property, not string interpolation

                        content.innerHTML = '';
                        content.appendChild(iframe);
                    }
                })
                .catch((err) => {
                    if (err.name === 'AbortError') {
                        return; // Ignore cancelled requests
                    }
                    const content = document.getElementById('filePreviewContent');
                    if (content) {
                        content.innerHTML = `
                            <div style="text-align:center; padding: 2rem;">
                                <i class="fas fa-exclamation-circle" style="font-size:2rem; color:#ef4444; margin-bottom: 0.75rem;"></i>
                                <p style="margin:0; color:#64748b;">Could not load preview. Use "Download File".</p>
                            </div>
                        `;
                    }
                });
        }

        function closeFilePreviewModal() {
            const modal = document.getElementById('filePreviewModal');
            const content = document.getElementById('filePreviewContent');
            if (modal) modal.style.display = 'none';
            if (content) content.innerHTML = '<div style="color: #64748b;">Loading preview...</div>';
            if (currentPreviewObjectUrl) {
                URL.revokeObjectURL(currentPreviewObjectUrl);
                currentPreviewObjectUrl = null;
            }
            currentImageZoom = 1;
        }

        function scrollPreviewContent(delta) {
            const content = document.getElementById('filePreviewContent');
            if (!content) return;

            const scrollArea = content.querySelector('.preview-scroll-area');
            if (scrollArea) {
                scrollArea.scrollBy({ top: delta, behavior: 'smooth' });
                return;
            }

            const frame = content.querySelector('iframe');
            if (frame) {
                try {
                    frame.contentWindow.scrollBy({ top: delta, behavior: 'smooth' });
                } catch (_) {
                    content.scrollBy({ top: delta, behavior: 'smooth' });
                }
                return;
            }

            content.scrollBy({ top: delta, behavior: 'smooth' });
        }

        function getPreviewImageElement() {
            const content = document.getElementById('filePreviewContent');
            if (!content) return null;
            return content.querySelector('.preview-scroll-area img');
        }

        function applyPreviewImageZoom() {
            const img = getPreviewImageElement();
            if (!img) return;
            img.style.transform = `scale(${currentImageZoom})`;
        }

        function zoomPreviewImage(step) {
            const img = getPreviewImageElement();
            if (!img) return;
            currentImageZoom = Math.max(0.5, Math.min(3, currentImageZoom + step));
            applyPreviewImageZoom();
        }

        function resetPreviewZoom() {
            const img = getPreviewImageElement();
            if (!img) return;
            currentImageZoom = 1;
            applyPreviewImageZoom();
        }

        function loadDocumentsIntoModal(landlordId, opts) {
            opts = opts || {};
            const container = document.getElementById('documentsContent');
            if (!container) {
                return Promise.reject(new Error('Missing documents container'));
            }

            if (opts.showLoading) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 2rem;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #3b82f6;"></i>
                        <p style="margin-top: 1rem;">Loading documents...</p>
                    </div>
                `;
            }

            return fetch(`/super-admin/landlords/${landlordId}/documents`, { credentials: 'same-origin' })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Failed to load documents');
                    }
                    return response.text();
                })
                .then((html) => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const documentsSection = doc.querySelector('.documents-section');

                    container.textContent = '';

                    if (opts.flash) {
                        const bar = document.createElement('div');
                        const isWarning = opts.flashVariant === 'warning';
                        bar.style.cssText = isWarning
                            ? 'padding:0.5rem 1rem;margin-bottom:0.75rem;background:#fef3c7;color:#92400e;border-radius:0.375rem;font-size:0.875rem;'
                            : 'padding:0.5rem 1rem;margin-bottom:0.75rem;background:#d1fae5;color:#047857;border-radius:0.375rem;font-size:0.875rem;';
                        bar.textContent = opts.flash;
                        container.appendChild(bar);
                        setTimeout(() => {
                            try {
                                bar.remove();
                            } catch (e) {
                                /* ignore */
                            }
                        }, 4000);
                    }

                    if (documentsSection) {
                        const wrapper = document.createElement('div');
                        wrapper.innerHTML = documentsSection.outerHTML;
                        while (wrapper.firstChild) {
                            container.appendChild(wrapper.firstChild);
                        }
                    } else {
                        const empty = document.createElement('div');
                        empty.style.cssText = 'text-align: center; padding: 2rem;';
                        empty.innerHTML =
                            '<i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #f59e0b;"></i><p style="margin-top: 1rem;">No documents found for this landlord.</p>';
                        container.appendChild(empty);
                    }
                });
        }

        function showDocumentsModal(landlordId, landlordName) {
            documentsModalLandlordId = landlordId;
            document.getElementById('documentsModal').style.display = 'block';

            loadDocumentsIntoModal(landlordId, { showLoading: true }).catch((error) => {
                console.error('Error loading documents:', error);
                const c = document.getElementById('documentsContent');
                if (c) {
                    c.innerHTML = `
                        <div style="text-align: center; padding: 2rem;">
                            <i class="fas fa-exclamation-circle" style="font-size: 2rem; color: #ef4444;"></i>
                            <p style="margin-top: 1rem;">Error loading documents. Please try again.</p>
                        </div>
                    `;
                }
            });
        }

        function closeDocumentsModal() {
            document.getElementById('documentsModal').style.display = 'none';
            documentsModalLandlordId = null;
        }

        (function attachLandlordDocVerifySubmit() {
            const modal = document.getElementById('documentsModal');
            if (!modal) {
                return;
            }
            modal.addEventListener('submit', function (event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }
                if (!form.classList.contains('js-landlord-doc-verify-form')) {
                    return;
                }

                event.preventDefault();

                const landlordIdForRefresh = documentsModalLandlordId;
                if (landlordIdForRefresh == null) {
                    return;
                }

                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                }

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                })
                    .then(async (response) => {
                        const ct = response.headers.get('content-type') || '';
                        const data = ct.includes('application/json') ? await response.json() : {};
                        if (!response.ok) {
                            const msg =
                                data.message ||
                                (data.errors
                                    ? Object.values(data.errors)
                                          .flat()
                                          .join(' ')
                                    : 'Could not update document.');
                            throw new Error(msg);
                        }
                        return data;
                    })
                    .then((data) => {
                        if (
                            documentsModalLandlordId == null ||
                            documentsModalLandlordId !== landlordIdForRefresh
                        ) {
                            return;
                        }
                        return loadDocumentsIntoModal(landlordIdForRefresh, {
                            showLoading: false,
                            flash: data.message || 'Document updated.',
                            flashVariant: data.variant || 'success',
                        });
                    })
                    .catch((err) => {
                        alert(err.message || 'Could not update document.');
                    })
                    .finally(() => {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                        }
                    });
            });
        })();

        function showRejectModal(landlordId, landlordName) {
            document.getElementById('rejectModal').style.display = 'block';
            document.getElementById('rejectForm').action = '/super-admin/reject-landlord/' + landlordId;
            document.getElementById('landlordName').value = landlordName;
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
            document.getElementById('rejectForm').reset();
        }

        document.addEventListener('click', function(event) {
            const clickedNode = event.target;
            const clickedElement =
                clickedNode instanceof Element ? clickedNode : clickedNode?.parentElement;
            const previewLink = clickedElement
                ? clickedElement.closest('.js-preview-landlord-doc')
                : null;
            if (!previewLink) return;

            event.preventDefault();
            const fileUrl = previewLink.getAttribute('data-file-url') || previewLink.getAttribute('href');
            const fileName = previewLink.getAttribute('data-file-name') || previewLink.textContent.trim();
            openFilePreviewModal(fileUrl, fileName);
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const documentsModal = document.getElementById('documentsModal');
            const rejectModal = document.getElementById('rejectModal');
            const filePreviewModal = document.getElementById('filePreviewModal');
            
            if (event.target == documentsModal) {
                closeDocumentsModal();
            }
            if (event.target == rejectModal) {
                closeRejectModal();
            }
            if (event.target == filePreviewModal) {
                closeFilePreviewModal();
            }
        }
    </script>
@endsection