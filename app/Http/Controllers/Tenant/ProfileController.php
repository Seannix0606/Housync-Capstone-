<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Show tenant profile page
     */
    public function profile()
    {
        try {
            /** @var \App\Models\User $tenant */
            $tenant = Auth::user();

            if (! $tenant) {
                return redirect()->route('login')->with('error', 'Please log in to access your profile.');
            }

            $assignment = $tenant->tenantAssignments()
                ->with([
                    'unit.property.landlord',
                    'landlord',
                ])
                ->where('status', 'active')
                ->first();

            $personalDocuments = TenantDocument::where('tenant_id', $tenant->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $rfidCards = collect();
            if ($assignment && class_exists('\App\Models\RfidCard')) {
                try {
                    $rfidCards = \App\Models\RfidCard::where('tenant_id', $tenant->id)
                        ->where('property_id', $assignment->unit->property->id)
                        ->get();
                } catch (\Exception $exception) {
                    $rfidCards = collect();
                }
            }

            return view('tenant-profile', compact('tenant', 'assignment', 'rfidCards', 'personalDocuments'));

        } catch (\Exception $exception) {
            Log::error('Tenant profile error', [
                'user_id' => Auth::id(),
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('tenant.dashboard')->with('error', 'Unable to load profile. Please try again.');
        }
    }

    /**
     * Show document upload form for tenant
     */
    public function uploadDocuments()
    {
        /** @var \App\Models\User $tenant */
        $tenant = Auth::user();

        $assignment = $tenant->tenantAssignments()->with(['unit.property', 'documents'])->first();

        $personalDocuments = $tenant->documents()->orderBy('created_at', 'desc')->get();

        return view('tenant.upload-documents', compact('assignment', 'personalDocuments'));
    }

    /**
     * Store uploaded documents (personal documents for tenant profile)
     */
    public function storeDocuments(Request $request)
    {
        Log::info('=== DOCUMENT UPLOAD START ===', [
            'has_documents' => $request->hasFile('documents'),
            'documents_count' => $request->hasFile('documents') ? count($request->file('documents')) : 0,
            'document_types' => $request->input('document_types', []),
            'user_id' => Auth::id(),
            'request_size' => $request->header('Content-Length'),
            'user_agent' => $request->header('User-Agent'),
        ]);

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
        } catch (\Illuminate\Validation\ValidationException $exception) {
            Log::error('Document upload validation failed', [
                'tenant_id' => Auth::id(),
                'errors' => $exception->errors(),
                'input' => $request->all(),
            ]);
            throw $exception;
        }

        /** @var \App\Models\User $tenant */
        $tenant = Auth::user();

        try {
            $uploadedDocuments = [];

            $useSupabase = config('app.env') !== 'local' && config('services.supabase.key');

            if ($useSupabase) {
                $supabase = new \App\Services\SupabaseService;
                Log::info('Using Supabase for document upload.');
            } else {
                Log::info('Using Local Storage (public disk) for document upload.');
            }
            $supabaseInstance = $useSupabase ? $supabase : null;
            DB::transaction(function () use ($request, $tenant, $useSupabase, $supabaseInstance, &$uploadedDocuments) {
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

                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'tenant-doc-'.$tenant->id.'-'.time().'-'.$index.'-'.uniqid().'.'.$extension;
                    $uploadResult = ['success' => false, 'message' => 'Upload failed'];

                    if ($useSupabase) {
                        $path = 'tenant-documents/'.$fileName;
                        $uploadResult = $supabaseInstance->uploadFile(config('services.supabase.bucket'), $path, $file->getRealPath());

                        if (! $uploadResult['success']) {
                            Log::error('Supabase upload failed', [
                                'tenant_id' => $tenant->id,
                                'file_name' => $fileName,
                                'file_size' => $file->getSize(),
                                'mime_type' => $file->getMimeType(),
                                'supabase_error' => $uploadResult['error'] ?? 'Unknown Supabase error',
                                'supabase_response' => $uploadResult['response'] ?? null,
                                'status_code' => $uploadResult['status_code'] ?? null,
                            ]);
                        }
                    } else {
                        $storagePath = 'tenant-documents';
                        $filePathOnDisk = $file->storeAs($storagePath, $fileName, 'public');

                        $uploadResult = [
                            'success' => true,
                            'url' => $filePathOnDisk,
                            'message' => 'Uploaded to local storage',
                        ];
                    }

                    Log::info('Tenant document upload result', [
                        'tenant_id' => $tenant->id,
                        'index' => $index,
                        'type' => $documentType,
                        'result_summary' => ['success' => $uploadResult['success'], 'message' => $uploadResult['message']],
                    ]);

                    if ($uploadResult['success']) {
                        $document = TenantDocument::create([
                            'tenant_id' => $tenant->id,
                            'document_type' => $documentType,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $uploadResult['url'],
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
                            'result' => $uploadResult,
                        ]);
                        throw new \Exception('Failed to upload document: '.($uploadResult['message'] ?? 'Unknown error'));
                    }
                }
            });

            Log::info('Personal documents uploaded successfully', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'documents_count' => count($uploadedDocuments),
                'document_types' => $request->document_types,
                'total_size' => array_sum(array_map(fn ($doc) => $doc->file_size, $uploadedDocuments)),
                'timestamp' => now(),
            ]);

            return back()->with('success', 'Documents uploaded successfully! They will be available when you apply for properties.');

        } catch (\Illuminate\Validation\ValidationException $exception) {
            $errorCode = 'DOC_VALIDATION_'.strtoupper(substr(md5($exception->getMessage().time()), 0, 8));

            Log::error('Document upload validation failed', [
                'error_code' => $errorCode,
                'tenant_id' => $tenant->id,
                'validation_errors' => $exception->errors(),
            ]);

            return back()->withErrors($exception->errors())->with('error', "Validation failed. Please check the form and try again. Error Code: {$errorCode}");

        } catch (\Exception $exception) {
            $errorCode = 'DOC_UPLOAD_'.strtoupper(substr(md5($exception->getMessage().time()), 0, 8));

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
                'error' => $exception->getMessage(),
                'files_count' => count($files),
                'timestamp' => now(),
            ]);

            $userMessage = $this->getUserFriendlyErrorMessage($exception, $errorCode);

            return back()->with('error', $userMessage);
        }
    }

    /**
     * Generate user-friendly error messages based on exception type
     */
    private function getUserFriendlyErrorMessage(\Exception $exception, string $errorCode)
    {
        $baseMessage = "Failed to upload documents. Error Code: {$errorCode}";

        if (str_contains($exception->getMessage(), 'No files uploaded')) {
            return "Please select at least one document to upload. {$baseMessage}";
        }

        if (str_contains($exception->getMessage(), 'Number of files must match')) {
            return "Please ensure each file has a corresponding document type selected. {$baseMessage}";
        }

        if (str_contains($exception->getMessage(), 'Failed to upload document')) {
            $uploadError = $exception->getMessage();
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

        if (str_contains($exception->getMessage(), 'validation')) {
            return "File validation failed. Please ensure files are PDF, JPG, or PNG format and under 5MB. {$baseMessage}";
        }

        if (str_contains($exception->getMessage(), 'database')) {
            return "Database error occurred. Please try again. If the problem persists, contact support. {$baseMessage}";
        }

        if (str_contains($exception->getMessage(), 'permission')) {
            return "Permission denied. Please ensure you have the right to upload documents. {$baseMessage}";
        }

        return "An unexpected error occurred during upload. Please try again. If the problem persists, contact support with error code: {$errorCode}";
    }

    /**
     * Download document
     */
    public function downloadDocument($documentId)
    {
        $document = TenantDocument::with(['tenant'])->findOrFail($documentId);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($document->tenant_id !== $user->id) {
            abort(403, 'Unauthorized access to document');
        }

        if (str_starts_with($document->file_path, 'http')) {
            return redirect($document->file_path);
        } else {
            $filePath = storage_path('app/public/'.$document->file_path);

            if (! file_exists($filePath)) {
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
            $document = TenantDocument::with(['tenant'])->findOrFail($documentId);

            if ($document->tenant_id !== Auth::id()) {
                abort(403, 'Unauthorized access to document.');
            }

            $assignment = null;
            $documentType = $document->document_type;
            $fileName = $document->file_name;
            $fileSize = $document->file_size;

            DB::transaction(function () use ($document, $assignment) {
                $document->delete();

                if ($assignment) {
                    $remainingDocuments = $assignment->documents()->count();

                    if ($remainingDocuments === 0) {
                        $assignment->update([
                            'documents_uploaded' => false,
                            'documents_verified' => false,
                        ]);
                    } else {
                        $pendingDocuments = $assignment->documents()->where('verification_status', 'pending')->count();
                        $allVerified = $pendingDocuments === 0;

                        $assignment->update([
                            'documents_uploaded' => true,
                            'documents_verified' => $allVerified,
                        ]);
                    }
                }
            });

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
                'timestamp' => now(),
            ]);

            return back()->with('success', 'Document deleted successfully.');

        } catch (\Exception $exception) {
            Log::error('Document deletion failed', [
                'tenant_id' => Auth::id(),
                'document_id' => $documentId,
                'error' => $exception->getMessage(),
                'timestamp' => now(),
            ]);

            return back()->with('error', 'Failed to delete document. Please try again.');
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

            if (! $tenant) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $assignment = $tenant->tenantAssignments()
                ->where('status', 'active')
                ->first();

            if (! $assignment) {
                return response()->json([
                    'error' => 'No active assignment found.',
                ], 403);
            }

            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            if (! Hash::check($request->current_password, $tenant->password)) {
                return response()->json(['error' => 'Current password is incorrect.'], 400);
            }

            $tenant->update([
                'password' => Hash::make($request->new_password),
            ]);

            Log::info('Tenant password updated', [
                'tenant_id' => $tenant->id,
                'tenant_email' => $tenant->email,
                'updated_at' => now(),
            ]);

            return response()->json(['success' => 'Password updated successfully!']);

        } catch (\Exception $exception) {
            Log::error('Password update error', [
                'tenant_id' => Auth::id(),
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['error' => 'An error occurred while updating your password.'], 500);
        }
    }
}
