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
        $superAdmin->superAdminProfile()->updateOrCreate(['user_id' => $superAdmin->id], ['name' => 'Super Admin', 'status' => 'active']);

        $pendingLandlord = User::factory()->create(['role' => 'landlord']);
        $pendingLandlord->landlordProfile()->updateOrCreate(['user_id' => $pendingLandlord->id], ['name' => 'Pending Landlord', 'status' => 'pending']);

        $response = $this->actingAs($superAdmin)->get('/super-admin/pending-landlords');

        $response->assertStatus(200);
        $response->assertSee('Pending Landlord');
        $response->assertViewHas('pendingLandlords');
    }

    public function test_super_admin_can_approve_landlord()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $superAdmin->superAdminProfile()->updateOrCreate(['user_id' => $superAdmin->id], ['name' => 'Super Admin', 'status' => 'active']);

        $landlord = User::factory()->create(['role' => 'landlord']);
        $landlord->landlordProfile()->updateOrCreate(['user_id' => $landlord->id], ['name' => 'Pending Landlord', 'status' => 'pending']);

        $this->assertEquals('pending', $landlord->fresh()->landlordProfile->status);

        $response = $this->actingAs($superAdmin)->post("/super-admin/approve-landlord/{$landlord->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Landlord approved successfully.');

        $this->assertDatabaseHas('landlord_profiles', [
            'user_id' => $landlord->id,
            'status' => 'approved',
            'approved_by' => $superAdmin->id,
        ]);
    }

    public function test_super_admin_can_reject_landlord()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $superAdmin->superAdminProfile()->updateOrCreate(['user_id' => $superAdmin->id], ['name' => 'Super Admin', 'status' => 'active']);

        $landlord = User::factory()->create(['role' => 'landlord']);
        $landlord->landlordProfile()->updateOrCreate(['user_id' => $landlord->id], ['name' => 'Pending Landlord', 'status' => 'pending']);

        $this->assertEquals('pending', $landlord->fresh()->landlordProfile->status);

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
        $landlord->landlordProfile()->updateOrCreate(['user_id' => $landlord->id], ['name' => 'Landlord User', 'status' => 'active']);

        $response = $this->actingAs($landlord)->get('/super-admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_super_admin_dashboard()
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $tenant->tenantProfile()->updateOrCreate(['user_id' => $tenant->id], ['name' => 'Tenant User', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/super-admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_super_admin_routes()
    {
        $response = $this->get('/super-admin/dashboard');
        $response->assertRedirect('/login');

        $response = $this->post('/super-admin/approve-landlord/1');
        $response->assertRedirect('/login');
    }

    public function test_approve_nonexistent_landlord_returns_404()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $superAdmin->superAdminProfile()->updateOrCreate(['user_id' => $superAdmin->id], ['name' => 'Super Admin', 'status' => 'active']);

        $response = $this->actingAs($superAdmin)->post('/super-admin/approve-landlord/999999');

        $response->assertStatus(404);
    }

    public function test_approve_already_approved_landlord_behaviour()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $superAdmin->superAdminProfile()->updateOrCreate(['user_id' => $superAdmin->id], ['name' => 'Super Admin', 'status' => 'active']);

        $landlord = User::factory()->create(['role' => 'landlord']);
        $landlord->landlordProfile()->updateOrCreate(['user_id' => $landlord->id], ['name' => 'Approved Landlord', 'status' => 'approved', 'approved_by' => $superAdmin->id]);

        $response = $this->actingAs($superAdmin)->post("/super-admin/approve-landlord/{$landlord->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error', 'This landlord is already approved.');

        $this->assertDatabaseHas('landlord_profiles', [
            'user_id' => $landlord->id,
            'status' => 'approved',
        ]);
    }
}
