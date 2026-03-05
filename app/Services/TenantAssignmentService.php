<?php

namespace App\Services;

use App\Models\User;
use App\Models\Unit;
use App\Models\TenantAssignment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantAssignmentService
{
    /**
     * Assign a tenant to a unit. Requires the tenant to already have an account.
     * Removes auto-generation of tenant credentials.
     */
    public function assignTenantToUnit($unitId, $tenantData, $landlordId)
    {
        try {
            // Check if unit is available
            $unit = Unit::findOrFail($unitId);
            if ($unit->status !== 'available') {
                throw new \Exception('Unit is not available for assignment.');
            }

            // Find existing tenant by email
            if (empty($tenantData['email'])) {
                throw new \Exception('Tenant email is required. Ask the tenant to register first.');
            }
            $tenant = User::where('email', $tenantData['email'])->where('role', 'tenant')->first();
            if (!$tenant) {
                throw new \Exception('No tenant account found for this email. Please have the tenant register.');
            }

            // Create tenant assignment
            $assignment = TenantAssignment::create([
                'unit_id' => $unitId,
                'tenant_id' => $tenant->id,
                'landlord_id' => $landlordId,
                'lease_start_date' => $tenantData['lease_start_date'],
                'lease_end_date' => $tenantData['lease_end_date'],
                'rent_amount' => $tenantData['rent_amount'],
                'security_deposit' => $tenantData['security_deposit'] ?? 0,
                'status' => 'active',
                'notes' => $tenantData['notes'] ?? null,
                'generated_password' => null,
            ]);

            // Update unit status
            $unit->update([
                'status' => 'occupied',
                'tenant_count' => 1,
            ]);

            return [
                'success' => true,
                'tenant' => $tenant,
                'assignment' => $assignment,
                'credentials' => null,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // Removed email/password generators; tenants self-register now.

    /**
     * Get tenant assignments for a landlord
     */
    public function getLandlordAssignments($landlordId, $filters = [])
    {
        $query = TenantAssignment::with(['tenant', 'unit.apartment'])
            ->where('landlord_id', $landlordId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Get tenant assignment details
     */
    public function getAssignmentDetails($assignmentId, $landlordId = null)
    {
        // Load tenant with their personal documents (not assignment-specific documents)
        $query = TenantAssignment::with([
            'tenant.documents', // Load all tenant documents
            'unit.apartment'
        ]);
        
        if ($landlordId) {
            $query->where('landlord_id', $landlordId);
        }
        
        return $query->findOrFail($assignmentId);
    }

    /**
     * Update assignment status
     */
    public function updateAssignmentStatus($assignmentId, $status, $landlordId)
    {
        $assignment = TenantAssignment::where('landlord_id', $landlordId)
            ->findOrFail($assignmentId);

        $assignment->update(['status' => $status]);

        // Update unit status if assignment is terminated
        if ($status === 'terminated') {
            $assignment->unit->update([
                'status' => 'available',
                'tenant_count' => 0,
            ]);
        }

        return $assignment;
    }

    /**
     * Get statistics for landlord
     */
    public function getLandlordStats($landlordId)
    {
        $assignments = TenantAssignment::where('landlord_id', $landlordId);

        return [
            'total_assignments' => $assignments->count(),
            'active_assignments' => $assignments->where('status', 'active')->count(),
            'pending_assignments' => $assignments->where('status', 'pending')->count(),
            'pending_approval' => $assignments->where('status', 'pending_approval')->count(),
            'total_revenue' => $assignments->where('status', 'active')->sum('rent_amount'),
        ];
    }
} 