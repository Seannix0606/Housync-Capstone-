<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\TenantAssignment;
use App\Models\TenantDocument;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    /**
     * Apply for a property as a tenant (from explore page)
     */
    public function applyForProperty(Request $request, $propertyId)
    {
        /** @var \App\Models\User $tenant */
        $tenant = Auth::user();

        $personalDocuments = TenantDocument::where('tenant_id', $tenant->id)->get();

        if ($personalDocuments->isEmpty()) {
            return back()->with('error', 'You must upload your personal documents before applying for a property. Please visit your profile or upload documents page to add required documents.');
        }

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

            $property = Property::findOrFail($propertyId);

            $unit = $property->getUnit();

            if (! $unit || $unit->status !== 'available') {
                $unit = Unit::whereHas('property', function ($query) use ($property) {
                    $query->where('landlord_id', $property->landlord_id);
                })
                    ->where('status', 'available')
                    ->with('property.landlord')
                    ->first();
            }

            if (! $unit) {
                Log::warning('No available units found for application', [
                    'tenant_id' => $tenant->id,
                    'property_id' => $propertyId,
                    'landlord_id' => $property->landlord_id,
                    'timestamp' => now(),
                ]);

                return back()->with('error', 'This property listing does not have units configured yet. This may be a showcase listing. Please contact the landlord directly to inquire about availability.');
            }

            if (! $unit->relationLoaded('property')) {
                $unit->load('property.landlord');
            }

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
                'landlord_id' => $property->landlord_id,
            ]);

            DB::transaction(function () use ($request, $unit, $tenant) {
                if ($tenant->tenantProfile) {
                    $tenant->tenantProfile->update([
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'address' => $request->address,
                    ]);
                }

                TenantAssignment::create([
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
            });

            Log::info('Tenant application submitted', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'property_id' => $property->id,
                'unit_id' => $unit->id,
                'landlord_id' => $unit->property->landlord_id,
                'property_name' => $unit->property->name,
                'documents_count' => $personalDocuments->count(),
                'timestamp' => now(),
            ]);

            return redirect()->route('explore')
                ->with('success', 'Your application has been submitted successfully! The landlord will review it shortly.');

        } catch (\Exception $exception) {
            Log::error('Tenant application failed', [
                'tenant_id' => Auth::id(),
                'property_id' => $propertyId,
                'error' => $exception->getMessage(),
                'timestamp' => now(),
            ]);

            return back()->with('error', 'Failed to submit application. Please try again.');
        }
    }

    /**
     * Apply for a specific unit directly
     */
    public function applyForUnit(Request $request, $unitId)
    {
        /** @var \App\Models\User $tenant */
        $tenant = Auth::user();

        $personalDocuments = TenantDocument::where('tenant_id', $tenant->id)->get();

        if ($personalDocuments->isEmpty()) {
            return back()->with('error', 'You must upload your personal documents before applying for a unit. Please visit your profile or upload documents page to add required documents.');
        }

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
            $unit = Unit::with(['property.landlord'])->findOrFail($unitId);

            if ($unit->status !== 'available') {
                return back()->with('error', 'This unit is no longer available for application.');
            }

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
                'landlord_id' => $unit->property->landlord_id,
            ]);

            DB::transaction(function () use ($request, $unit, $tenant) {
                if ($tenant->tenantProfile) {
                    $tenant->tenantProfile->update([
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'address' => $request->address,
                    ]);
                }

                TenantAssignment::create([
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

            Log::info('Tenant application submitted for unit', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->tenantProfile->name ?? $tenant->name,
                'unit_id' => $unit->id,
                'property_id' => $unit->property_id,
                'landlord_id' => $unit->property->landlord_id,
                'property_name' => $unit->property->name,
                'documents_count' => $personalDocuments->count(),
                'timestamp' => now(),
            ]);

            return redirect()->route('explore')
                ->with('success', 'Your application has been submitted successfully! The landlord will review it shortly.');

        } catch (\Exception $exception) {
            Log::error('Tenant unit application failed', [
                'tenant_id' => Auth::id(),
                'unit_id' => $unitId,
                'error' => $exception->getMessage(),
                'timestamp' => now(),
            ]);

            return back()->with('error', 'Failed to submit application. Please try again.');
        }
    }
}
