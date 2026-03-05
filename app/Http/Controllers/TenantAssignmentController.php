<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\TenantAssignment;
use App\Models\TenantDocument;
use App\Models\User;
use App\Models\Property;
use App\Services\TenantAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantAssignmentController extends Controller
{
    protected $assignmentService;

    public function __construct(TenantAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Show tenant assignments for landlord
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status']);
        $assignments = $this->assignmentService->getLandlordAssignments(Auth::id(), $filters);
        $stats = $this->assignmentService->getLandlordStats(Auth::id());

        return view('landlord.tenant-assignments', compact('assignments', 'stats', 'filters'));
    }
    
    public function store(Request $request, $unitId)
    {
        // Enhanced validation rules
        $request->validate([
            'email' => 'required|email',
            'phone' => 'nullable|regex:/^[0-9]+$/|max:20',
            'address' => 'nullable|string|max:500',
            'lease_start_date' => 'required|date|after_or_equal:today',
            'lease_end_date' => 'required|date|after:lease_start_date|before:2 years',
            'rent_amount' => 'required|numeric|min:1000|max:100000',
            'security_deposit' => 'nullable|numeric|min:0|max:50000',
            'notes' => 'nullable|string|max:1000',
        ], [
            'phone.regex' => 'Phone must contain digits only',
            'lease_end_date.before' => 'Lease cannot exceed 2 years',
            'rent_amount.min' => 'Rent must be at least ₱1,000',
            'rent_amount.max' => 'Rent cannot exceed ₱100,000',
            'security_deposit.max' => 'Security deposit cannot exceed ₱50,000',
        ]);

        try {
            // Use database transaction to ensure data consistency
            $result = DB::transaction(function() use ($request, $unitId) {
                return $this->assignmentService->assignTenantToUnit(
                    $unitId,
                    $request->all(),
                    Auth::id()
                );
            });

            if ($result['success']) {
                // Audit log for successful assignment
                Log::info('Tenant assigned successfully', [
                    'landlord_id' => Auth::id(),
                    'unit_id' => $unitId,
                    'tenant_email' => $request->email,
                    'lease_start_date' => $request->lease_start_date,
                    'lease_end_date' => $request->lease_end_date,
                    'rent_amount' => $request->rent_amount,
                    'timestamp' => now()
                ]);

                return redirect()->route('landlord.tenant-assignments')
                    ->with('success', 'Tenant assigned successfully!');
            } else {
                return back()->withInput()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            // Detailed error logging
            Log::error('Tenant assignment failed', [
                'landlord_id' => Auth::id(),
                'unit_id' => $unitId,
                'tenant_name' => $request->name,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'request_data' => $request->except(['_token']),
                'timestamp' => now()
            ]);

            return back()->withInput()->with('error', 'Failed to assign tenant. Please try again.');
        }
    }

    /**
     * Reassign a previously vacated tenant (existing tenant) to a new unit
     */
    public function reassign(Request $request, $assignmentId)
    {
        // Enhanced validation rules for reassignment
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'lease_start_date' => 'required|date|after_or_equal:today',
            'lease_end_date' => 'required|date|after:lease_start_date|before:2 years',
            'rent_amount' => 'required|numeric|min:1000|max:100000',
            'security_deposit' => 'nullable|numeric|min:0|max:50000',
            'notes' => 'nullable|string|max:1000',
        ], [
            'lease_end_date.before' => 'Lease cannot exceed 2 years',
            'rent_amount.min' => 'Rent must be at least ₱1,000',
            'rent_amount.max' => 'Rent cannot exceed ₱100,000',
            'security_deposit.max' => 'Security deposit cannot exceed ₱50,000',
        ]);

        try {
            // Use database transaction with race condition protection
            $result = DB::transaction(function() use ($request, $assignmentId) {
                // Fetch the vacated assignment within landlord scope
                $assignment = TenantAssignment::where('landlord_id', Auth::id())
                    ->with(['tenant', 'unit'])
                    ->findOrFail($assignmentId);

                if ($assignment->status !== 'terminated') {
                    throw new \Exception('Only vacated tenants can be reassigned.');
                }

                // Get the old unit to free it up
                $oldUnit = $assignment->unit;
                
                // Race condition protection: Lock the target unit and check availability
                $newUnit = Unit::whereHas('property', function($q) {
                    $q->where('landlord_id', Auth::id());
                })
                ->where('id', $request->unit_id)
                ->where('status', 'available')
                ->lockForUpdate() // This prevents race conditions
                ->first();

                if (!$newUnit) {
                    throw new \Exception('Selected unit is not available or does not belong to you.');
                }

                // Free up the old unit (make it available again)
                $oldUnit->update([
                    'status' => 'available',
                    'tenant_count' => 0,
                ]);

                // Update the existing assignment with new unit and details
                $assignment->update([
                    'unit_id' => $newUnit->id,
                    'lease_start_date' => $request->lease_start_date,
                    'lease_end_date' => $request->lease_end_date,
                    'rent_amount' => $request->rent_amount,
                    'security_deposit' => $request->security_deposit ?? 0,
                    'status' => 'active',
                    'notes' => $request->notes ?? null,
                ]);

                // Update new unit status to occupied
                $newUnit->update([
                    'status' => 'occupied',
                    'tenant_count' => 1,
                ]);

                return [
                    'assignment' => $assignment,
                    'old_unit' => $oldUnit,
                    'new_unit' => $newUnit
                ];
            });

            // Audit log for successful reassignment
            Log::info('Tenant reassigned successfully', [
                'landlord_id' => Auth::id(),
                'assignment_id' => $assignmentId,
                'old_unit_id' => $result['old_unit']->id,
                'new_unit_id' => $result['new_unit']->id,
                'tenant_id' => $result['assignment']->tenant_id,
                'tenant_name' => $result['assignment']->tenant->name,
                'lease_start_date' => $request->lease_start_date,
                'lease_end_date' => $request->lease_end_date,
                'rent_amount' => $request->rent_amount,
                'timestamp' => now()
            ]);

            return redirect()->route('landlord.tenant-assignments')
                ->with('success', 'Tenant reassigned successfully. Credentials remain the same.');

        } catch (\Exception $e) {
            // Detailed error logging
            Log::error('Tenant reassignment failed', [
                'landlord_id' => Auth::id(),
                'assignment_id' => $assignmentId,
                'unit_id' => $request->unit_id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'request_data' => $request->except(['_token']),
                'timestamp' => now()
            ]);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show tenant assignment details
     */
    public function show($id)
    {
        $assignment = $this->assignmentService->getAssignmentDetails($id, Auth::id());
        return view('landlord.assignment-details', compact('assignment'));
    }

    /**
     * Update assignment status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,terminated',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            // Get the assignment to log the old status
            $assignment = TenantAssignment::where('landlord_id', Auth::id())
                ->with('tenant')
                ->findOrFail($id);

            $oldStatus = $assignment->status;

            // Update the assignment status
            $updatedAssignment = $this->assignmentService->updateAssignmentStatus(
                $id,
                $request->status,
                Auth::id()
            );

            // Audit log for status change
            Log::info('Assignment status updated', [
                'landlord_id' => Auth::id(),
                'assignment_id' => $id,
                'tenant_id' => $assignment->tenant_id,
                'tenant_name' => $assignment->tenant->name,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'reason' => $request->reason ?? 'No reason provided',
                'timestamp' => now()
            ]);

            return back()->with('success', 'Assignment status updated successfully.');

        } catch (\Exception $e) {
            // Detailed error logging
            Log::error('Assignment status update failed', [
                'landlord_id' => Auth::id(),
                'assignment_id' => $id,
                'new_status' => $request->status,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'timestamp' => now()
            ]);

            return back()->with('error', 'Failed to update assignment status. Please try again.');
        }
    }

    /**
     * Show tenant dashboard
     */
    public function tenantDashboard()
    {
        /** @var \App\Models\User $tenant */
        $tenant = Auth::user();
        $assignments = $tenant->tenantAssignments()
            ->with(['unit.apartment', 'tenant.documents']) // Documents are now at tenant level
            ->orderByRaw("FIELD(status, 'active', 'pending_approval', 'terminated')")
            ->orderBy('created_at', 'desc')
            ->get();

        if ($assignments->isEmpty()) {
            // Show a lightweight tenant dashboard explaining next steps instead of redirecting
            return view('tenant.no-assignment');
        }

        return view('tenant.dashboard', compact('assignments'));
    }

    /**
     * Show document upload form for tenant
     */
    public function uploadDocuments()
    {
        /** @var \App\Models\User $tenant */
        $tenant = Auth::user();
        
        // Get the active assignment if one exists (optional now)
        $assignment = $tenant->tenantAssignments()->with(['unit.apartment', 'documents'])->first();
        
        // Get tenant's personal documents (uploaded before assignment)
        $personalDocuments = $tenant->documents()->orderBy('created_at', 'desc')->get();

        return view('tenant.upload-documents', compact('assignment', 'personalDocuments'));
    }

    /**
     * Store uploaded documents (personal documents for tenant profile)
     */
    public function storeDocuments(Request $request)
    {
        // Log the incoming request for debugging
        Log::info('=== DOCUMENT UPLOAD START ===', [
            'has_documents' => $request->hasFile('documents'),
            'documents_count' => $request->hasFile('documents') ? count($request->file('documents')) : 0,
            'document_types' => $request->input('document_types', []),
            'user_id' => Auth::id(),
            'request_size' => $request->header('Content-Length'),
            'user_agent' => $request->header('User-Agent'),
        ]);
    
        // Enhanced validation rules
        try {
            $request->validate([
                'documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'document_types.*' => 'required|string|in:government_id,proof_of_income,employment_contract,bank_statement,character_reference,rental_history,other',
            ], [
                'documents.*.required' => 'Please select at least one document to upload',
                'documents.*.file' => 'The uploaded file is not valid',
                'documents.*.mimes' => 'Only PDF, JPG, JPEG, and PNG files are allowed',
                'documents.*.max' => 'Each file must not exceed 5MB',
                'document_types.*.required' => 'Please select a document type for each file',
                'document_types.*.in' => 'Invalid document type selected',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Document upload validation failed', [
                'tenant_id' => Auth::id(),
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            throw $e;
        }
    
        /** @var \App\Models\User $tenant */
        $tenant = Auth::user();
        
        try {
            $uploadedDocuments = [];
            
            // --- START FIX: Determine Storage Mechanism ---
            // Force local storage when in local environment, regardless of Supabase config
            $useSupabase = config('app.env') !== 'local' && config('services.supabase.key');
    
            if ($useSupabase) {
                $supabase = new \App\Services\SupabaseService();
                Log::info('Using Supabase for document upload.');
            } else {
                Log::info('Using Local Storage (public disk) for document upload.');
            }
            $supabaseInstance = $useSupabase ? $supabase : null;
            DB::transaction(function() use ($request, $tenant, $useSupabase, $supabaseInstance, &$uploadedDocuments) {
                $files = $request->file('documents', []);
                if (empty($files)) {
                    throw new \Exception('No files uploaded');
                }
                
                $documentTypes = $request->input('document_types', []);
                if (count($files) !== count($documentTypes)) {
                    throw new \Exception('Number of files must match number of document types');
                }
                
                foreach ($files as $index => $file) {
                    $documentType = $documentTypes[$index];
                    
                    // Generate unique filename
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'tenant-doc-' . $tenant->id . '-' . time() . '-' . $index . '-' . uniqid() . '.' . $extension;
                    $uploadResult = ['success' => false, 'message' => 'Upload failed'];
    
                    if ($useSupabase) {
                        $path = 'tenant-documents/' . $fileName;
                        // Upload to Supabase
                        $uploadResult = $supabaseInstance->uploadFile('house-sync', $path, $file->getRealPath());
                        
                        // Enhanced error handling for Supabase uploads
                        if (!$uploadResult['success']) {
                            Log::error('Supabase upload failed', [
                                'tenant_id' => $tenant->id,
                                'file_name' => $fileName,
                                'file_size' => $file->getSize(),
                                'mime_type' => $file->getMimeType(),
                                'supabase_error' => $uploadResult['error'] ?? 'Unknown Supabase error',
                                'supabase_response' => $uploadResult['response'] ?? null,
                                'status_code' => $uploadResult['status_code'] ?? null
                            ]);
                        }
                    } else {
                        // Upload to local disk: storage/app/public/tenant-documents
                        $storagePath = 'tenant-documents';
                        $filePathOnDisk = $file->storeAs($storagePath, $fileName, 'public');
                        
                        $uploadResult = [
                            'success' => true,
                            // This path will be asset('storage/' . path) handled by document_url()
                            'url' => $filePathOnDisk, 
                            'message' => 'Uploaded to local storage'
                        ];
                    }
    
                    // --- START FIX: Remove debug echo statement ---
                    // The debug echo statements were the cause of the page refresh issue.
                    // Log the result instead of echoing to the browser.
                    Log::info('Tenant document upload result', [
                        'tenant_id' => $tenant->id,
                        'index' => $index,
                        'type' => $documentType,
                        'result_summary' => ['success' => $uploadResult['success'], 'message' => $uploadResult['message']]
                    ]);
                    // --- END FIX ---
                    
                    // Only create record if successful
                    if ($uploadResult['success']) {
                        $document = TenantDocument::create([
                            'tenant_id' => $tenant->id,
                            'document_type' => $documentType,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $uploadResult['url'], // Now holds either Supabase URL or local storage path
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                            'uploaded_at' => now(),
                            'verification_status' => 'pending',
                        ]);
    
                        $uploadedDocuments[] = $document;
                    } else {
                        Log::error('Failed to upload tenant document', [
                            'tenant_id' => $tenant->id,
                            'index' => $index,
                            'result' => $uploadResult
                        ]);
                        throw new \Exception('Failed to upload document: ' . ($uploadResult['message'] ?? 'Unknown error'));
                    }
                }
            });
    
            // Audit log for successful document upload
            Log::info('Personal documents uploaded successfully', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'documents_count' => count($uploadedDocuments),
                'document_types' => $request->document_types,
                'total_size' => array_sum(array_map(fn($doc) => $doc->file_size, $uploadedDocuments)),
                'timestamp' => now()
            ]);
    
            return back()->with('success', 'Documents uploaded successfully! They will be available when you apply for properties.');
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors specifically
            $errorCode = 'DOC_VALIDATION_' . strtoupper(substr(md5($e->getMessage() . time()), 0, 8));
            
            Log::error('Document upload validation failed', [
                'error_code' => $errorCode,
                'tenant_id' => $tenant->id,
                'validation_errors' => $e->errors(),
            ]);
            
            return back()->withErrors($e->errors())->with('error', "Validation failed. Please check the form and try again. Error Code: {$errorCode}");
            
        } catch (\Exception $e) {
            // Generate error code for tracking
            $errorCode = 'DOC_UPLOAD_' . strtoupper(substr(md5($e->getMessage() . time()), 0, 8));
            
            // Enhanced error logging with more details
            $files = $request->file('documents', []);
            $fileDetails = [];
            foreach ($files as $index => $file) {
                $fileDetails[] = [
                    'index' => $index,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'extension' => $file->getClientOriginalExtension(),
                ];
            }
            
            Log::error('Personal document upload failed', [
                'error_code' => $errorCode,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'files_count' => count($files),
                'file_details' => $fileDetails,
                'document_types' => $request->input('document_types', []),
                'supabase_config' => [
                    'url' => config('services.supabase.url'),
                    'has_key' => !empty(config('services.supabase.key')),
                    'has_service_key' => !empty(config('services.supabase.service_key')),
                ],
                'timestamp' => now()
            ]);
    
            // Determine user-friendly error message based on error type
            $userMessage = $this->getUserFriendlyErrorMessage($e, $errorCode);
    
            return back()->with('error', $userMessage);
        }
    }

    /**
     * Generate user-friendly error messages based on exception type
     */
    private function getUserFriendlyErrorMessage(\Exception $e, string $errorCode)
    {
        $baseMessage = "Failed to upload documents. Error Code: {$errorCode}";
        
        // Check for specific error patterns
        if (str_contains($e->getMessage(), 'No files uploaded')) {
            return "Please select at least one document to upload. {$baseMessage}";
        }
        
        if (str_contains($e->getMessage(), 'Number of files must match')) {
            return "Please ensure each file has a corresponding document type selected. {$baseMessage}";
        }
        
        if (str_contains($e->getMessage(), 'Failed to upload document')) {
            $uploadError = $e->getMessage();
            if (str_contains($uploadError, 'Supabase')) {
                return "Upload service temporarily unavailable. Please try again in a few minutes. {$baseMessage}";
            }
            if (str_contains($uploadError, 'storage')) {
                return "File storage error. Please check your file size and format. {$baseMessage}";
            }
            if (str_contains($uploadError, '401') || str_contains($uploadError, 'Unauthorized')) {
                return "Authentication error with upload service. Please refresh the page and try again. {$baseMessage}";
            }
            if (str_contains($uploadError, '413') || str_contains($uploadError, 'too large')) {
                return "File too large. Please ensure each file is under 5MB. {$baseMessage}";
            }
            if (str_contains($uploadError, '415') || str_contains($uploadError, 'Unsupported Media Type')) {
                return "File format not supported. Please use PDF, JPG, or PNG files only. {$baseMessage}";
            }
            if (str_contains($uploadError, '500') || str_contains($uploadError, 'Internal Server Error')) {
                return "Server error occurred. Please try again in a few minutes. {$baseMessage}";
            }
            return "File upload failed. Please check your file size (max 5MB) and format (PDF, JPG, PNG). {$baseMessage}";
        }
        
        if (str_contains($e->getMessage(), 'validation')) {
            return "File validation failed. Please ensure files are PDF, JPG, or PNG format and under 5MB. {$baseMessage}";
        }
        
        if (str_contains($e->getMessage(), 'database')) {
            return "Database error occurred. Please try again. If the problem persists, contact support. {$baseMessage}";
        }
        
        if (str_contains($e->getMessage(), 'permission')) {
            return "Permission denied. Please ensure you have the right to upload documents. {$baseMessage}";
        }
        
        // Default message for unknown errors
        return "An unexpected error occurred during upload. Please try again. If the problem persists, contact support with error code: {$errorCode}";
    }

    public function downloadDocument($documentId)
    {
        $document = TenantDocument::with(['tenant'])->findOrFail($documentId);
        
        // Check if user has access to this document
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $hasAccess = false;
        
        if ($user->isLandlord()) {
            // Landlord can view if document belongs to their tenant
            // We need to check through tenant assignments
            $tenantAssignments = $user->landlordAssignments()->pluck('tenant_id');
            if ($tenantAssignments->contains($document->tenant_id)) {
                $hasAccess = true;
            }
        } elseif ($user->isTenant()) {
            // Tenant can view their own documents
            if ($document->tenant_id === $user->id) {
                $hasAccess = true;
            }
        }
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to document');
        }

        // Check if it's a Supabase URL or local storage path
        if (str_starts_with($document->file_path, 'http')) {
            // It's a Supabase URL, redirect directly
            return redirect($document->file_path);
        } else {
            // It's a local storage path, serve the file
            $filePath = storage_path('app/public/' . $document->file_path);
            
            if (!file_exists($filePath)) {
                abort(404, 'File not found');
            }
            
            return response()->file($filePath);
        }
    }

    /**
     * Delete document (tenant only)
     */
    public function deleteDocument($documentId)
    {
        try {
            $document = TenantDocument::with(['tenantAssignment.tenant', 'tenant'])->findOrFail($documentId);
            
            // Check if user is the tenant who uploaded this document
            // Check both tenant_id (for profile documents) and assignment tenant_id
            if ($document->tenant_id !== Auth::id() && 
                (!$document->tenantAssignment || $document->tenantAssignment->tenant_id !== Auth::id())) {
                abort(403, 'Unauthorized access to document.');
            }

            $assignment = $document->tenantAssignment;
            $documentType = $document->document_type;
            $fileName = $document->file_name;
            $fileSize = $document->file_size;

            // Use database transaction for document deletion
            DB::transaction(function() use ($document, $assignment) {
                // Delete the document record
                $document->delete();

                // Update assignment status based on remaining documents (if assignment exists)
                if ($assignment) {
                    $remainingDocuments = $assignment->documents()->count();
                    
                    if ($remainingDocuments === 0) {
                        // No documents left, mark as not uploaded
                        $assignment->update([
                            'documents_uploaded' => false,
                            'documents_verified' => false,
                        ]);
                    } else {
                        // Check if all remaining documents are verified
                        $pendingDocuments = $assignment->documents()->where('verification_status', 'pending')->count();
                        $allVerified = $pendingDocuments === 0;
                        
                        $assignment->update([
                            'documents_uploaded' => true,
                            'documents_verified' => $allVerified,
                        ]);
                    }
                }
            });

            // Audit log for document deletion
            Log::info('Document deleted successfully', [
                'tenant_id' => Auth::id(),
                'tenant_name' => $document->tenant->name ?? ($assignment ? $assignment->tenant->name : 'Unknown'),
                'document_id' => $documentId,
                'assignment_id' => $assignment?->id,
                'unit_id' => $assignment?->unit_id,
                'landlord_id' => $assignment?->landlord_id,
                'document_type' => $documentType,
                'document_name' => $fileName,
                'file_size' => $fileSize,
                'verification_status' => $document->verification_status,
                'remaining_documents' => $assignment ? $assignment->documents()->count() : 0,
                'timestamp' => now()
            ]);

            return back()->with('success', 'Document deleted successfully.');

        } catch (\Exception $e) {
            // Detailed error logging
            Log::error('Document deletion failed', [
                'tenant_id' => Auth::id(),
                'document_id' => $documentId,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'timestamp' => now()
            ]);

            return back()->with('error', 'Failed to delete document. Please try again.');
        }
    }

    /**
     * Get tenant credentials
     */
    public function getCredentials($id)
    {
        $assignment = TenantAssignment::where('landlord_id', Auth::id())
            ->with('tenant')
            ->findOrFail($id);

        return response()->json([
            'email' => $assignment->tenant->email,
            'password' => $assignment->generated_password ?? 'Password not available'
        ]);
    }

    /**
     * Get available units for assignment
     */
    public function getAvailableUnits()
    {
        $units = Unit::whereHas('property', function($query) {
            $query->where('landlord_id', Auth::id());
        })->where('status', 'available')
        ->with('property')
        ->get();

        return response()->json($units);
    }

    /**
     * Delete tenant assignment (landlord only)
     */
    public function destroy($id)
    {
        try {
            // Use database transaction for assignment deletion
            $result = DB::transaction(function() use ($id) {
                $assignment = TenantAssignment::where('landlord_id', Auth::id())
                    ->with(['tenant.documents', 'unit'])
                    ->findOrFail($id);

                $tenantName = $assignment->tenant->name;
                $tenantId = $assignment->tenant_id;
                $unitId = $assignment->unit_id;
                $documentsCount = $assignment->tenant->documents->count();
                $totalFileSize = $assignment->tenant->documents->sum('file_size');

                // Delete all associated documents first
                foreach ($assignment->documents as $document) {
                    // Note: Files are stored in Supabase, not local storage
                    // Supabase files will remain accessible unless explicitly deleted from Supabase
                    // Delete the document record
                    $document->delete();
                }

                // Update the unit status back to available
                $assignment->unit->update([
                    'status' => 'available',
                    'tenant_count' => 0
                ]);

                // Delete the assignment only
                $assignment->delete();

                return [
                    'tenant_name' => $tenantName,
                    'tenant_id' => $tenantId,
                    'unit_id' => $unitId,
                    'documents_count' => $documentsCount,
                    'total_file_size' => $totalFileSize
                ];
            });

            // Audit log for assignment deletion
            Log::info('Tenant assignment deleted successfully', [
                'landlord_id' => Auth::id(),
                'assignment_id' => $id,
                'tenant_id' => $result['tenant_id'],
                'tenant_name' => $result['tenant_name'],
                'unit_id' => $result['unit_id'],
                'documents_deleted' => $result['documents_count'],
                'total_file_size_deleted' => $result['total_file_size'],
                'timestamp' => now()
            ]);

            return redirect()->route('landlord.tenant-assignments')
                ->with('success', 'Tenant assignment deleted successfully. Unit is now available for new assignments.');

        } catch (\Exception $e) {
            // Detailed error logging
            Log::error('Tenant assignment deletion failed', [
                'landlord_id' => Auth::id(),
                'assignment_id' => $id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'timestamp' => now()
            ]);

            return back()->with('error', 'Failed to delete tenant assignment. Please try again.');
        }
    }

    /**
     * Show tenant profile page
     */
    public function tenantProfile()
    {
        try {
            /** @var \App\Models\User $tenant */
            $tenant = Auth::user();
            
            if (!$tenant) {
                return redirect()->route('login')->with('error', 'Please log in to access your profile.');
            }
            
            $assignment = $tenant->tenantAssignments()
                ->with([
                    'unit.apartment.landlord', 
                    'landlord'
                ])
                ->where('status', 'active')
                ->first();

            // Get all documents belonging to this tenant
            $personalDocuments = TenantDocument::where('tenant_id', $tenant->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Get RFID cards if available
            $rfidCards = collect();
            if ($assignment && class_exists('\App\Models\RfidCard')) {
                try {
                    $rfidCards = \App\Models\RfidCard::where('tenant_id', $tenant->id)
                        ->where('property_id', $assignment->unit->property->id)
                        ->get();
                } catch (\Exception $e) {
                    // RFID functionality might not be fully implemented yet
                    $rfidCards = collect();
                }
            }

            return view('tenant-profile', compact('tenant', 'assignment', 'rfidCards', 'personalDocuments'));
            
        } catch (\Exception $e) {
            Log::error('Tenant profile error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('tenant.dashboard')->with('error', 'Unable to load profile. Please try again.');
        }
    }

    // Removed getTenantPassword - tenants create their own passwords during registration

    /**
     * Show tenant lease page
     */
    public function tenantLease()
    {
        try {
            /** @var \App\Models\User $tenant */
            $tenant = Auth::user();
            
            if (!$tenant) {
                return redirect()->route('login')->with('error', 'Please log in to access your lease information.');
            }
            
            $assignment = $tenant->tenantAssignments()
                ->with([
                    'unit.apartment.landlord', 
                    'documents',
                    'landlord'
                ])
                ->where('status', 'active')
                ->first();

            return view('tenant-lease', compact('tenant', 'assignment'));
            
        } catch (\Exception $e) {
            Log::error('Tenant lease error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('tenant.dashboard')->with('error', 'Unable to load lease information. Please try again.');
        }
    }

    /**
     * Update tenant password (only if documents are verified)
     */
    public function updatePassword(Request $request)
    {
        try {
            /** @var \App\Models\User $tenant */
            $tenant = Auth::user();
            
            if (!$tenant) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Check if tenant's documents are verified
            $assignment = $tenant->tenantAssignments()
                ->where('status', 'active')
                ->first();

            if (!$assignment) {
                return response()->json([
                    'error' => 'No active assignment found.'
                ], 403);
            }

            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            // Verify current password
            if (!Hash::check($request->current_password, $tenant->password)) {
                return response()->json(['error' => 'Current password is incorrect.'], 400);
            }

            // Update password
            $tenant->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Log the password change
            Log::info('Tenant password updated', [
                'tenant_id' => $tenant->id,
                'tenant_email' => $tenant->email,
                'updated_at' => now()
            ]);

            return response()->json(['success' => 'Password updated successfully!']);

        } catch (\Exception $e) {
            Log::error('Password update error: ' . $e->getMessage(), [
                'tenant_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'An error occurred while updating your password.'], 500);
        }
    }

    /**
     * Apply for a property as a tenant (from explore page)
     */
    public function applyForProperty(Request $request, $propertyId)
    {
        /** @var \App\Models\User $tenant */
        $tenant = Auth::user();
        
        // Check if tenant has uploaded personal documents
        $personalDocuments = TenantDocument::where('tenant_id', $tenant->id)->get();
        
        if ($personalDocuments->isEmpty()) {
            return back()->with('error', 'You must upload your personal documents before applying for a property. Please visit your profile or upload documents page to add required documents.');
        }
        
        // Validate the application data (no documents required in form anymore)
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|regex:/^[0-9]+$/|max:20',
            'address' => 'required|string|max:500',
            'occupation' => 'required|string|max:255',
            'monthly_income' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $tenant = Auth::user();
            
            // Get the property from explore page
            $property = \App\Models\Property::findOrFail($propertyId);
            
            // First, try to get the unit directly linked to this property (via slug)
            $unit = $property->getUnit();
            
            // If no direct link, try to find an available unit from this landlord's apartments
            if (!$unit || $unit->status !== 'available') {
                $unit = Unit::whereHas('property', function($q) use ($property) {
                    $q->where('landlord_id', $property->landlord_id);
                })
                ->where('status', 'available')
                ->with('property.landlord')
                ->first();
            }
            
            if (!$unit) {
                Log::warning('No available units found for application', [
                    'tenant_id' => $tenant->id,
                    'property_id' => $propertyId,
                    'landlord_id' => $property->landlord_id,
                    'timestamp' => now()
                ]);
                
                // Provide more helpful error message with next steps
                return back()->with('error', 'This property listing does not have units configured yet. This may be a showcase listing. Please contact the landlord directly to inquire about availability.');
            }
            
            // Ensure apartment relationship is loaded
            if (!$unit->relationLoaded('apartment')) {
                $unit->load('apartment.landlord');
            }
            
            // Check if tenant already has an application for this specific unit
            $existingApplicationForUnit = TenantAssignment::where('tenant_id', $tenant->id)
                ->where('unit_id', $unit->id)
                ->whereIn('status', ['active', 'pending_approval'])
                ->first();

            if ($existingApplicationForUnit) {
                return back()->with('error', 'You already have an active or pending application for this unit.');
            }
            
            Log::info('Found unit for application', [
                'unit_id' => $unit->id,
                'property_id' => $unit->property_id,
                'landlord_id' => $property->landlord_id
            ]);

            // Create the tenant assignment with pending_approval status
            DB::transaction(function() use ($request, $unit, $tenant, $property, $personalDocuments) {
                // Update user info if provided (profile-centric)
                if ($tenant->tenantProfile) {
                    $tenant->tenantProfile->update([
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'address' => $request->address,
                    ]);
                }

                // Create the assignment as pending approval
                $assignment = TenantAssignment::create([
                    'tenant_id' => $tenant->id,
                    'unit_id' => $unit->id,
                    'landlord_id' => $unit->property->landlord_id,
                    'status' => 'pending_approval',
                    'lease_start_date' => null,
                    'lease_end_date' => null,
                    'rent_amount' => $unit->rent_amount ?? 0,
                    'security_deposit' => 0,
                    'occupation' => $request->occupation,
                    'monthly_income' => $request->monthly_income,
                    'notes' => $request->notes,
                ]);

                // Documents remain at tenant level - they are NOT linked to assignments
                // Landlord will view the tenant's personal documents when reviewing the application
            });

            // Audit log
            Log::info('Tenant application submitted', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'property_id' => $property->id,
                'unit_id' => $unit->id,
                'landlord_id' => $unit->property->landlord_id,
                'property_name' => $unit->property->name,
                'documents_count' => $personalDocuments->count(),
                'timestamp' => now()
            ]);

            return redirect()->route('explore')
                ->with('success', 'Your application has been submitted successfully! The landlord will review it shortly.');

        } catch (\Exception $e) {
            Log::error('Tenant application failed', [
                'tenant_id' => Auth::id(),
                'property_id' => $propertyId,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);

            // Show detailed error in development
            $errorMessage = config('app.debug') 
                ? 'Failed to submit application: ' . $e->getMessage()
                : 'Failed to submit application. Please try again.';

            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Apply for a specific unit directly
     */
    public function applyForUnit(Request $request, $unitId)
    {
        /** @var \App\Models\User $tenant */
        $tenant = Auth::user();
        
        // Check if tenant has uploaded personal documents
        $personalDocuments = TenantDocument::where('tenant_id', $tenant->id)->get();
        
        if ($personalDocuments->isEmpty()) {
            return back()->with('error', 'You must upload your personal documents before applying for a unit. Please visit your profile or upload documents page to add required documents.');
        }
        
        // Validate the application data
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|regex:/^[0-9]+$/|max:20',
            'address' => 'required|string|max:500',
            'occupation' => 'required|string|max:255',
            'monthly_income' => 'required|numeric|min:0',
            'move_in_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Get the unit directly
            $unit = Unit::with(['property.landlord'])->findOrFail($unitId);
            
            if ($unit->status !== 'available') {
                return back()->with('error', 'This unit is no longer available for application.');
            }
            
            // Check if tenant already has an application for this specific unit
            $existingApplicationForUnit = TenantAssignment::where('tenant_id', $tenant->id)
                ->where('unit_id', $unit->id)
                ->whereIn('status', ['active', 'pending_approval'])
                ->first();

            if ($existingApplicationForUnit) {
                return back()->with('error', 'You already have an active or pending application for this unit.');
            }
            
            Log::info('Tenant applying for unit directly', [
                'tenant_id' => $tenant->id,
                'unit_id' => $unit->id,
                'property_id' => $unit->property_id,
                'landlord_id' => $unit->property->landlord_id
            ]);

            // Create the tenant assignment with pending_approval status
            DB::transaction(function() use ($request, $unit, $tenant, $personalDocuments) {
                // Update user info if provided (profile-centric)
                if ($tenant->tenantProfile) {
                    $tenant->tenantProfile->update([
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'address' => $request->address,
                    ]);
                }

                // Create the assignment as pending approval
                $assignment = TenantAssignment::create([
                    'tenant_id' => $tenant->id,
                    'unit_id' => $unit->id,
                    'landlord_id' => $unit->property->landlord_id,
                    'status' => 'pending_approval',
                    'lease_start_date' => $request->move_in_date,
                    'lease_end_date' => null,
                    'rent_amount' => $unit->rent_amount ?? 0,
                    'security_deposit' => 0,
                    'occupation' => $request->occupation,
                    'monthly_income' => $request->monthly_income,
                    'notes' => $request->notes,
                ]);
            });

            // Audit log
            Log::info('Tenant application submitted for unit', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->tenantProfile->name ?? $tenant->name,
                'unit_id' => $unit->id,
                'property_id' => $unit->property_id,
                'landlord_id' => $unit->property->landlord_id,
                'property_name' => $unit->property->name,
                'documents_count' => $personalDocuments->count(),
                'timestamp' => now()
            ]);

            return redirect()->route('explore')
                ->with('success', 'Your application has been submitted successfully! The landlord will review it shortly.');

        } catch (\Exception $e) {
            Log::error('Tenant unit application failed', [
                'tenant_id' => Auth::id(),
                'unit_id' => $unitId,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);

            // Show detailed error in development
            $errorMessage = config('app.debug') 
                ? 'Failed to submit application: ' . $e->getMessage()
                : 'Failed to submit application. Please try again.';

            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Approve tenant application
     */
    public function approveApplication($id)
    {
        try {
            DB::transaction(function() use ($id) {
                $assignment = TenantAssignment::where('landlord_id', Auth::id())
                    ->where('status', 'pending_approval')
                    ->with(['unit', 'tenant'])
                    ->findOrFail($id);

                // Update assignment status to active
                $assignment->update([
                    'status' => 'active',
                    'lease_start_date' => now(),
                    'lease_end_date' => now()->addYear(),
                ]);

                // Update unit status to occupied
                $assignment->unit->update([
                    'status' => 'occupied',
                    'tenant_count' => 1
                ]);
            });

            Log::info('Tenant application approved', [
                'landlord_id' => Auth::id(),
                'assignment_id' => $id,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application approved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Application approval failed', [
                'landlord_id' => Auth::id(),
                'assignment_id' => $id,
                'error' => $e->getMessage(),
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve application'
            ], 500);
        }
    }

    /**
     * Reject tenant application
     */
    public function rejectApplication(Request $request, $id)
    {
        try {
            DB::transaction(function() use ($request, $id) {
                $assignment = TenantAssignment::where('landlord_id', Auth::id())
                    ->where('status', 'pending_approval')
                    ->with(['unit', 'tenant.documents'])
                    ->findOrFail($id);

                // NOTE: We DO NOT delete tenant's personal documents when rejecting
                // Documents belong to the tenant and can be used for other applications

                // Store rejection reason in notes
                $assignment->update([
                    'notes' => 'Application rejected. Reason: ' . ($request->reason ?? 'No reason provided'),
                ]);

                // Delete the assignment only
                $assignment->delete();
            });

            Log::info('Tenant application rejected', [
                'landlord_id' => Auth::id(),
                'assignment_id' => $id,
                'reason' => $request->reason ?? 'No reason provided',
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application rejected successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Application rejection failed', [
                'landlord_id' => Auth::id(),
                'assignment_id' => $id,
                'error' => $e->getMessage(),
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject application'
            ], 500);
        }
    }
} 