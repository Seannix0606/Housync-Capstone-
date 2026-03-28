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
use Illuminate\Support\Facades\Hash;

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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|regex:/^[0-9]+$/|max:20',
            'address' => 'required|string|max:500',
            'business_info' => 'required|string|max:1000',
            'documents' => 'required|array|min:1',
            'documents.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'document_types' => 'required|array',
            'document_types.*' => 'required|string|in:business_permit,mayors_permit,bir_certificate,barangay_clearance,lease_contract_sample,valid_id,other',
        ]);

        $landlord = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'landlord',
        ]);

        LandlordProfile::create([
            'user_id' => $landlord->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'business_info' => $request->business_info,
            'status' => 'pending',
        ]);

        $supabase = new SupabaseService;

        foreach ($request->file('documents') as $index => $file) {
            $docType = $request->document_types[$index] ?? 'other';
            $extension = $file->getClientOriginalExtension();
            $fileName = 'landlord-doc-'.$landlord->id.'-'.time().'-'.$index.'-'.uniqid().'.'.$extension;
            $path = 'landlord-documents/'.$fileName;

            $uploadResult = $supabase->uploadFile('house-sync', $path, $file->getRealPath());

            if ($uploadResult['success']) {
                LandlordDocument::create([
                    'landlord_id' => $landlord->id,
                    'document_type' => $docType,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $uploadResult['url'],
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_at' => now(),
                    'verification_status' => 'pending',
                ]);
            }
        }

        event(new Registered($landlord));

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
