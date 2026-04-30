<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use App\Models\User;
use App\Services\SupabaseService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    /**
     * Show the landlord registration form.
     */
    public function register()
    {
        return view('landlord.register');
    }

    /**
     * Process landlord registration with documents.
     */
    public function storeRegistration(Request $request)
    {
        $request->merge([
            'phone' => static::normalizePhone((string) $request->input('phone', '')),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|regex:/^[0-9]+$/|min:7|max:20',
            'address' => 'required|string|max:500',
            'business_info' => 'required|string|max:1000',
            'doc_barangay_clearance' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'doc_mayors_permit' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'doc_fire_safety_certificate' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'doc_tax_registration' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'property_is_newly_built' => 'nullable|boolean',
            'doc_certificate_of_occupancy' => [
                Rule::requiredIf(fn () => $request->boolean('property_is_newly_built')),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ], [
            'doc_certificate_of_occupancy.required' => 'Certificate of Occupancy is required when your property is newly built.',
        ]);

        $supabase = app(SupabaseService::class);

        $documentSlots = [
            ['file' => $request->file('doc_barangay_clearance'), 'type' => 'barangay_clearance'],
            ['file' => $request->file('doc_mayors_permit'), 'type' => 'mayors_permit'],
            ['file' => $request->file('doc_fire_safety_certificate'), 'type' => 'fire_safety_certificate'],
            ['file' => $request->file('doc_tax_registration'), 'type' => 'bir_certificate'],
        ];

        if ($request->boolean('property_is_newly_built') && $request->hasFile('doc_certificate_of_occupancy')) {
            $documentSlots[] = [
                'file' => $request->file('doc_certificate_of_occupancy'),
                'type' => 'certificate_of_occupancy',
            ];
        }

        try {
            $landlord = DB::transaction(function () use ($request, $supabase, $documentSlots) {
                $landlord = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                    'role' => 'landlord',
                ]);

                $profile = $landlord->landlordProfile;

                if ($profile) {
                    $profile->update([
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'address' => $request->address,
                        'business_info' => $request->business_info,
                        'status' => 'pending',
                    ]);
                } else {
                    LandlordProfile::create([
                        'user_id' => $landlord->id,
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'address' => $request->address,
                        'business_info' => $request->business_info,
                        'status' => 'pending',
                    ]);
                }

                foreach ($documentSlots as $index => $slot) {
                    $file = $slot['file'];
                    $docType = $slot['type'];
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'landlord-doc-'.$landlord->id.'-'.time().'-'.$index.'-'.uniqid().'.'.$extension;
                    $remotePath = 'landlord-documents/'.$fileName;

                    $fileUrl = null;

                    try {
                        $uploadResult = $supabase->uploadFile(
                            config('services.supabase.bucket'),
                            $remotePath,
                            $file->getRealPath(),
                        );

                        if (! empty($uploadResult['success'])) {
                            $fileUrl = $uploadResult['url'] ?? null;
                        } else {
                            throw new \RuntimeException($uploadResult['message'] ?? 'Supabase upload failed');
                        }
                    } catch (\Throwable $supabaseException) {
                        Log::warning('Supabase landlord-document upload failed, falling back to local storage', [
                            'error' => $supabaseException->getMessage(),
                            'doc_type' => $docType,
                            'index' => $index,
                        ]);

                        $storedPath = $file->storeAs('landlord-documents', $fileName, 'public');
                        $fileUrl = $storedPath ? asset('storage/'.$storedPath) : null;
                    }

                    if (! $fileUrl) {
                        throw new \RuntimeException('Failed to save document "'.$file->getClientOriginalName().'".');
                    }

                    LandlordDocument::create([
                        'landlord_id' => $landlord->id,
                        'document_type' => $docType,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $fileUrl,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_at' => now(),
                        'verification_status' => 'pending',
                    ]);
                }

                return $landlord;
            });
        } catch (\Exception $e) {
            Log::error('Landlord registration failed', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            return back()->with('registration_error', true)->withInput();
        }

        event(new Registered($landlord));
        Cache::forget('super_admin.pending_landlords_count');

        return redirect()->route('landlord.pending')->with('success', 'Registration submitted successfully. Please wait for admin approval.');
    }

    /**
     * Show pending approval page for unverified landlords.
     */
    public function pending()
    {
        return view('landlord.pending');
    }

    /**
     * Show rejected page for landlords whose application was rejected.
     */
    public function rejected()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return view('landlord.rejected', compact('user'));
    }
}
