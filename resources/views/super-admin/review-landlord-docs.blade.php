<div class="documents-section">
    <style>
        .documents-section { font-family: 'Inter', sans-serif; }
        .documents-section h3 { color: #1e293b; margin-bottom: 1rem; font-size: 1.25rem; font-weight: 600; }
        .documents-section p { color: #64748b; margin-bottom: 1.5rem; }
        .documents-section table { border-collapse: collapse; width: 100%; margin-bottom: 1rem; }
        .documents-section th, .documents-section td { border: 1px solid #e2e8f0; padding: 0.75rem; text-align: left; }
        .documents-section th { background: #f8fafc; color: #64748b; font-weight: 600; font-size: 0.875rem; }
        .documents-section .status { font-weight: 600; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; }
        .documents-section .pending { background: #fef3c7; color: #d97706; }
        .documents-section .verified { background: #d1fae5; color: #059669; }
        .documents-section .rejected { background: #fee2e2; color: #dc2626; }
        .documents-section .btn { padding: 0.375rem 0.75rem; border: none; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500; cursor: pointer; margin-right: 0.5rem; }
        .documents-section .btn-success { background: #10b981; color: white; }
        .documents-section .btn-danger { background: #ef4444; color: white; }
        .documents-section .btn-primary { background: #3b82f6; color: white; }
        .documents-section .btn:hover { opacity: 0.9; }
        .documents-section input[type="text"] { padding: 0.375rem 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.75rem; margin-right: 0.5rem; }
        .documents-section .file-link { color: #3b82f6; text-decoration: none; font-weight: 500; }
        .documents-section .file-link:hover { text-decoration: underline; }
        .documents-section .empty-state { text-align: center; padding: 2rem; color: #64748b; }

        /* Dark Mode Styles */
        body.dark-mode .documents-section h3 { color: #f1f5f9 !important; }
        body.dark-mode .documents-section p { color: #94a3b8 !important; }
        body.dark-mode .documents-section th { background: #0f172a !important; color: #94a3b8 !important; border-color: #334155 !important; }
        body.dark-mode .documents-section td { border-color: #334155 !important; color: #e2e8f0 !important; }
        body.dark-mode .documents-section .pending { background: #78350f !important; color: #fbbf24 !important; }
        body.dark-mode .documents-section .verified { background: #064e3b !important; color: #6ee7b7 !important; }
        body.dark-mode .documents-section .rejected { background: #7f1d1d !important; color: #fca5a5 !important; }
        body.dark-mode .documents-section input[type="text"] { background: #0f172a !important; border-color: #334155 !important; color: #e2e8f0 !important; }
        body.dark-mode .documents-section .file-link { color: #60a5fa !important; }
        body.dark-mode .documents-section .empty-state { color: #94a3b8 !important; }
    </style>
    
    <h3>Documents for {{ $landlord->name }}</h3>
    <p>{{ $landlord->email }}</p>

    @if($documents->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>File</th>
                    <th>Uploaded</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documents as $doc)
                    <tr>
                        <td>{{ str_replace('_',' ', ucfirst($doc->document_type)) }}</td>
                        <td>
                            <a href="{{ document_url($doc->file_path) }}" target="_blank" class="file-link">
                                <i class="fas fa-file"></i> {{ $doc->file_name }}
                            </a>
                        </td>
                        <td>{{ $doc->uploaded_at->format('M d, Y H:i') }}</td>
                        <td>
                            <span class="status {{ $doc->verification_status }}">
                                {{ ucfirst($doc->verification_status) }}
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('super-admin.verify-landlord-document', $doc->id) }}" style="display: inline;">
                                @csrf
                                <input type="hidden" name="status" value="verified">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Verify
                                </button>
                            </form>
                            <form method="POST" action="{{ route('super-admin.verify-landlord-document', $doc->id) }}" style="display: inline;">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <input type="text" name="notes" placeholder="Reason (optional)" style="width: 120px;" />
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">
            <i class="fas fa-file-alt" style="font-size: 2rem; margin-bottom: 1rem; color: #94a3b8;"></i>
            <p>No documents uploaded by this landlord.</p>
        </div>
    @endif
</div>


