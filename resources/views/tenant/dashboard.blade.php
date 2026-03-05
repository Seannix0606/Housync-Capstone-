@extends('layouts.app')

@section('title', 'Tenant Dashboard')

@section('content')
<div class="page-title-box">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="fw-bold">Tenant Dashboard</h1>
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Unit Assignment Details -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Unit Assignment Details</h5>
            <span class="badge bg-primary">{{ $assignments->count() }} {{ $assignments->count() === 1 ? 'Assignment' : 'Assignments' }}</span>
        </div>
        @foreach($assignments as $assignment)
        <div class="card mb-3 {{ $assignment->status === 'pending_approval' ? 'border-warning' : ($assignment->status === 'active' ? 'border-success' : 'border-secondary') }}">
            <div class="card-header bg-{{ $assignment->status === 'pending_approval' ? 'warning' : ($assignment->status === 'active' ? 'success' : 'secondary') }} bg-opacity-10">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-building me-2"></i>{{ $assignment->unit->apartment->name }} - Unit {{ $assignment->unit->unit_number }}</h6>
                    <span class="badge bg-{{ $assignment->status_badge_class }}">{{ $assignment->status === 'pending_approval' ? 'Pending Approval' : ucfirst($assignment->status) }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Unit Information</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%"><strong>Unit Number:</strong></td>
                                <td>{{ $assignment->unit->unit_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Property:</strong></td>
                                <td>{{ $assignment->unit->apartment->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Unit Type:</strong></td>
                                <td>{{ $assignment->unit->unit_type }}</td>
                            </tr>
                            <tr>
                                <td><strong>Bedrooms:</strong></td>
                                <td>{{ $assignment->unit->bedrooms }}</td>
                            </tr>
                            <tr>
                                <td><strong>Bathrooms:</strong></td>
                                <td>{{ $assignment->unit->bathrooms }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        @if($assignment->status === 'pending_approval')
                            <h6 class="text-muted mb-3">Application Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Applied On:</strong></td>
                                    <td>{{ $assignment->created_at->format('M d, Y') }}</td>
                                </tr>
                                @if($assignment->occupation)
                                <tr>
                                    <td><strong>Occupation:</strong></td>
                                    <td>{{ $assignment->occupation }}</td>
                                </tr>
                                @endif
                                @if($assignment->monthly_income)
                                <tr>
                                    <td><strong>Monthly Income:</strong></td>
                                    <td>₱{{ number_format($assignment->monthly_income, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Expected Rent:</strong></td>
                                    <td>₱{{ number_format($assignment->rent_amount, 2) }}/month</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Awaiting Landlord Approval
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        @else
                            <h6 class="text-muted mb-3">Lease Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Lease Start:</strong></td>
                                    <td>{{ $assignment->lease_start_date ? $assignment->lease_start_date->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Lease End:</strong></td>
                                    <td>{{ $assignment->lease_end_date ? $assignment->lease_end_date->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Monthly Rent:</strong></td>
                                    <td>₱{{ number_format($assignment->rent_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Security Deposit:</strong></td>
                                    <td>₱{{ number_format($assignment->security_deposit, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $assignment->status_badge_class }}">
                                            {{ ucfirst($assignment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </div>
                </div>

                @if($assignment->notes)
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Additional Notes:</h6>
                            <p class="mb-0">{{ $assignment->notes }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Quick Actions for this Assignment -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="border-top pt-3">
                            <h6 class="mb-3"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @if($assignment->status === 'pending_approval')
                                    <button class="btn btn-sm btn-outline-primary" disabled>
                                        <i class="fas fa-clock me-1"></i> Awaiting Approval
                                    </button>
                                @else
                                    <a href="{{ route('tenant.upload-documents') }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-upload me-1"></i> Upload Documents
                                    </a>
                                    
                                    <button class="btn btn-sm btn-outline-secondary" onclick="viewAssignmentDocuments({{ $assignment->id }})">
                                        <i class="fas fa-file-alt me-1"></i> View Documents
                                    </button>
                                    
                                    <button class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-envelope me-1"></i> Contact Landlord
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tenant Documents -->
                @if(Auth::user()->documents && Auth::user()->documents->count() > 0)
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="border-top pt-3">
                            <h6 class="mb-3"><i class="fas fa-folder-open me-2"></i>Your Personal Documents ({{ Auth::user()->documents->count() }})</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Document Type</th>
                                            <th>File Name</th>
                                            <th>Uploaded</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(Auth::user()->documents as $document)
                                        <tr>
                                            <td>{{ $document->document_type_label }}</td>
                                            <td>{{ $document->file_name }}</td>
                                            <td>{{ $document->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    @if(in_array($document->mime_type, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']))
                                                        <button type="button" class="btn btn-outline-info" onclick="viewImage('{{ document_url($document->file_path) }}', '{{ $document->file_name }}')">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    @elseif($document->mime_type === 'application/pdf')
                                                        <button type="button" class="btn btn-outline-info" onclick="viewPDF('{{ document_url($document->file_path) }}', '{{ $document->file_name }}')">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-outline-info" onclick="viewFile('{{ document_url($document->file_path) }}', '{{ $document->file_name }}')">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    <a href="{{ route('tenant.download-document', $document->id) }}" class="btn btn-outline-primary">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    
                                                    <button type="button" class="btn btn-outline-danger" onclick="deleteDocument({{ $document->id }}, '{{ $document->file_name }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Required Documents Info -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Required Documents</h5>
        
        <div class="row">
            <div class="col-md-6">
                <h6>Essential Documents:</h6>
                <ul>
                    <li><strong>Government ID:</strong> Passport, Driver's License, or any valid government-issued ID</li>
                    <li><strong>Proof of Income:</strong> Recent payslips, employment contract, or business registration</li>
                    <li><strong>Bank Statement:</strong> Last 3 months of bank statements</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Additional Documents:</h6>
                <ul>
                    <li><strong>Character Reference:</strong> Letter from employer, colleague, or community leader</li>
                    <li><strong>Rental History:</strong> Previous rental agreements or landlord references (if applicable)</li>
                    <li><strong>Other:</strong> Any additional documents that may be required</li>
                </ul>
            </div>
        </div>

        <div class="alert alert-info mt-3 mb-0">
            <h6 class="alert-heading">Document Guidelines:</h6>
            <ul class="mb-0">
                <li>All documents should be clear and legible</li>
                <li>Accepted formats: PDF, JPG, JPEG, PNG</li>
                <li>Maximum file size: 5MB per document</li>
                <li>Documents will be reviewed within 2-3 business days</li>
            </ul>
        </div>
    </div>
</div>
</div>

<!-- Delete Document Modal -->
<div class="modal fade" id="deleteDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <h6 class="alert-heading">⚠️ Warning</h6>
                    <p class="mb-2">You are about to delete: <strong id="documentToDelete"></strong></p>
                    <p class="mb-0">This action cannot be undone. The document will be permanently removed.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteDocumentForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Document
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imagePreview" src="" alt="Document Preview" class="img-fluid" style="max-height: 70vh;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="imageDownloadLink" href="" class="btn btn-primary" download>
                    <i class="fas fa-download me-1"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<!-- PDF Viewer Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfModalTitle">PDF Viewer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <iframe id="pdfViewer" src="" width="100%" height="600px" frameborder="0"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="pdfDownloadLink" href="" class="btn btn-primary" download>
                    <i class="fas fa-download me-1"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<!-- File Viewer Modal -->
<div class="modal fade" id="fileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileModalTitle">File Viewer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="alert alert-info">
                    <i class="fas fa-file-alt me-2"></i>
                    This file type cannot be previewed directly. Please download the file to view it.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="fileDownloadLink" href="" class="btn btn-primary" download>
                    <i class="fas fa-download me-1"></i> Download File
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function deleteDocument(documentId, fileName) {
    document.getElementById('documentToDelete').textContent = fileName;
    document.getElementById('deleteDocumentForm').action = `/tenant/delete-document/${documentId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteDocumentModal'));
    modal.show();
}

function viewImage(imageUrl, fileName) {
    document.getElementById('imagePreview').src = imageUrl;
    document.getElementById('imageModalTitle').textContent = fileName;
    document.getElementById('imageDownloadLink').href = imageUrl;
    
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}

function viewPDF(pdfUrl, fileName) {
    document.getElementById('pdfViewer').src = pdfUrl;
    document.getElementById('pdfModalTitle').textContent = fileName;
    document.getElementById('pdfDownloadLink').href = pdfUrl;
    
    const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
    modal.show();
}

function viewFile(fileUrl, fileName) {
    document.getElementById('fileModalTitle').textContent = fileName;
    document.getElementById('fileDownloadLink').href = fileUrl;
    
    const modal = new bootstrap.Modal(document.getElementById('fileModal'));
    modal.show();
}

function viewAssignmentDocuments(assignmentId) {
    // Scroll to documents section for this assignment
    const assignmentCard = event.target.closest('.card');
    if (assignmentCard) {
        const documentsSection = assignmentCard.querySelector('.table-responsive');
        if (documentsSection) {
            documentsSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}
</script>
@endpush
