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
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
                    <div class="stat-value">{{ $pendingLandlords->count() }}</div>
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
                        <p class="section-subtitle">Review applications and approve or reject landlord accounts</p>
                    </div>
                </div>

                @if($pendingLandlords->count() > 0)
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingLandlords as $landlord)
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
                                            <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $landlord->business_info }}">
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
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-primary btn-sm" onclick="showDocumentsModal({{ $landlord->id }}, '{{ $landlord->name }}')">
                                                    <i class="fas fa-file-alt"></i> View Docs
                                                </button>
                                                @if($landlord->landlordProfile && $landlord->landlordProfile->status === 'pending')
                                                    <form method="POST" action="{{ route('super-admin.approve-landlord', $landlord->id) }}" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this landlord?')">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-danger btn-sm" onclick="showRejectModal({{ $landlord->id }}, '{{ $landlord->name }}')">
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
        function showDocumentsModal(landlordId, landlordName) {
            document.getElementById('documentsModal').style.display = 'block';
            
            // Show loading state
            document.getElementById('documentsContent').innerHTML = `
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #3b82f6;"></i>
                    <p style="margin-top: 1rem;">Loading documents...</p>
                </div>
            `;
            
            // Fetch documents via AJAX
            fetch(`/super-admin/landlords/${landlordId}/documents`)
                .then(response => response.text())
                .then(html => {
                    // Extract the documents content from the response
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const documentsSection = doc.querySelector('.documents-section');
                    
                    if (documentsSection) {
                        document.getElementById('documentsContent').innerHTML = documentsSection.outerHTML;
                    } else {
                        document.getElementById('documentsContent').innerHTML = `
                            <div style="text-align: center; padding: 2rem;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #f59e0b;"></i>
                                <p style="margin-top: 1rem;">No documents found for this landlord.</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading documents:', error);
                    document.getElementById('documentsContent').innerHTML = `
                        <div style="text-align: center; padding: 2rem;">
                            <i class="fas fa-exclamation-circle" style="font-size: 2rem; color: #ef4444;"></i>
                            <p style="margin-top: 1rem;">Error loading documents. Please try again.</p>
                        </div>
                    `;
                });
        }

        function closeDocumentsModal() {
            document.getElementById('documentsModal').style.display = 'none';
        }

        function showRejectModal(landlordId, landlordName) {
            document.getElementById('rejectModal').style.display = 'block';
            document.getElementById('rejectForm').action = '/super-admin/reject-landlord/' + landlordId;
            document.getElementById('landlordName').value = landlordName;
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
            document.getElementById('rejectForm').reset();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const documentsModal = document.getElementById('documentsModal');
            const rejectModal = document.getElementById('rejectModal');
            
            if (event.target == documentsModal) {
                closeDocumentsModal();
            }
            if (event.target == rejectModal) {
                closeRejectModal();
            }
        }
    </script>
@endsection