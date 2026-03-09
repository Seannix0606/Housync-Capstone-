<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_pending_landlords()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $superAdmin->superAdminProfile()->create(['name' => 'Super Admin', 'status' => 'active']);

        $pendingLandlord = User::factory()->create(['role' => 'landlord']);
        $pendingLandlord->landlordProfile()->create(['name' => 'Pending Landlord', 'status' => 'pending']);

        $response = $this->actingAs($superAdmin)->get('/super-admin/pending-landlords');

        $response->assertStatus(200);
        $response->assertSee('Pending Landlord');
        $response->assertViewHas('pendingLandlords');
    }

    public function test_super_admin_can_approve_landlord()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $superAdmin->superAdminProfile()->create(['name' => 'Super Admin', 'status' => 'active']);

        $landlord = User::factory()->create(['role' => 'landlord']);
        $landlord->landlordProfile()->create(['name' => 'Pending Landlord', 'status' => 'pending']);

        $this->assertEquals('pending', $landlord->fresh()->landlordProfile->status);

        $response = $this->actingAs($superAdmin)->post("/super-admin/approve-landlord/{$landlord->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Landlord approved successfully.');

        $this->assertDatabaseHas('landlord_profiles', [
            'user_id' => $landlord->id,
            'status' => 'approved',
        ]);
    }

    public function test_super_admin_can_reject_landlord()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $superAdmin->superAdminProfile()->create(['name' => 'Super Admin', 'status' => 'active']);

        $landlord = User::factory()->create(['role' => 'landlord']);
        $landlord->landlordProfile()->create(['name' => 'Pending Landlord', 'status' => 'pending']);

        $response = $this->actingAs($superAdmin)->post("/super-admin/reject-landlord/{$landlord->id}", [
            'reason' => 'Invalid documents provided.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Landlord rejected successfully.');

        $this->assertDatabaseHas('landlord_profiles', [
            'user_id' => $landlord->id,
            'status' => 'rejected',
            'rejection_reason' => 'Invalid documents provided.',
        ]);
    }

    public function test_landlord_cannot_access_super_admin_dashboard()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $landlord->landlordProfile()->create(['name' => 'Landlord User', 'status' => 'active']);

        $response = $this->actingAs($landlord)->get('/super-admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_super_admin_dashboard()
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $tenant->tenantProfile()->create(['name' => 'Tenant User', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/super-admin/dashboard');

        $response->assertStatus(403);
    }
}
