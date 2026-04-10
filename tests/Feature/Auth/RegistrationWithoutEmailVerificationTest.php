<?php

namespace Tests\Feature\Auth;

use App\Services\SupabaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class RegistrationWithoutEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_email_verification_routes_are_not_registered(): void
    {
        $this->assertFalse(Route::has('verification.notice'));
        $this->assertFalse(Route::has('verification.verify'));
        $this->assertFalse(Route::has('verification.send'));
    }

    public function test_email_verify_url_returns_not_found(): void
    {
        $this->get('/email/verify')->assertNotFound();
    }

    public function test_tenant_registration_redirects_to_tenant_dashboard(): void
    {
        $response = $this->post(route('register.post'), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'tenant-register-test@example.com',
            'password' => 'password12',
        ]);

        $response->assertRedirect(route('tenant.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_landlord_registration_redirects_to_pending_not_email_verify(): void
    {
        $supabase = Mockery::mock(SupabaseService::class);
        $supabase->shouldReceive('uploadFile')
            ->times(4)
            ->andReturn(['success' => true, 'url' => 'https://example.test/fake-doc.pdf']);
        $this->instance(SupabaseService::class, $supabase);

        $response = $this->from(route('landlord.register'))->post(route('landlord.register.store'), [
            'name' => 'Test Landlord LLC',
            'email' => 'landlord-register-test@example.com',
            'password' => 'password12',
            'password_confirmation' => 'password12',
            'phone' => '09123456789',
            'address' => '123 Test Street, City',
            'business_info' => 'Residential property rentals for testing.',
            'doc_barangay_clearance' => UploadedFile::fake()->create('barangay.pdf', 100, 'application/pdf'),
            'doc_mayors_permit' => UploadedFile::fake()->create('mayor.pdf', 100, 'application/pdf'),
            'doc_fire_safety_certificate' => UploadedFile::fake()->create('fire.pdf', 100, 'application/pdf'),
            'doc_tax_registration' => UploadedFile::fake()->create('tax.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('landlord.pending'));
        $response->assertSessionHas('success');
        $response->assertSessionMissing('errors');
        $this->assertDatabaseHas('users', ['email' => 'landlord-register-test@example.com', 'role' => 'landlord']);
    }
}
