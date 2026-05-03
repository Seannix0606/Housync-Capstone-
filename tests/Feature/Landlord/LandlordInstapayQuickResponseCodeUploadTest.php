<?php

namespace Tests\Feature\Landlord;

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use App\Services\Billing\PaymentVerificationImageStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class LandlordInstapayQuickResponseCodeUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(RoleMiddleware::class);
    }

    private function fakePngUpload(): UploadedFile
    {
        $onePixelPng = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/w8AAgMBgN9vW6sAAAAASUVORK5CYII=',
            true
        );

        return UploadedFile::fake()->createWithContent('landlord-qr.png', (string) $onePixelPng);
    }

    public function test_landlord_can_upload_instapay_quick_response_code_image(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $expectedStoredPath = 'landlord-instapay-quick-response-codes/test-qr-image.jpg';

        $storageServiceMock = Mockery::mock(PaymentVerificationImageStorageService::class);
        $storageServiceMock->shouldReceive('deletePrivateDiskObjectIfPresent')
            ->once()
            ->with(null);
        $storageServiceMock->shouldReceive('persistInstapayCodeFromLandlord')
            ->once()
            ->andReturn($expectedStoredPath);

        $this->app->instance(PaymentVerificationImageStorageService::class, $storageServiceMock);

        $response = $this->actingAs($landlord)->post(
            route('landlord.payments.instapay-code.update'),
            [
                'instapay_quick_response_code_image' => $this->fakePngUpload(),
            ]
        );

        $response->assertRedirect(route('landlord.payments'));
        $response->assertSessionHas('success');

        $landlord->refresh();
        $this->assertSame($expectedStoredPath, $landlord->landlord_instapay_quick_response_code_image_path);
    }

    public function test_instapay_quick_response_code_upload_failure_returns_user_friendly_error(): void
    {
        $landlord = User::factory()->create([
            'role' => 'landlord',
            'landlord_instapay_quick_response_code_image_path' => 'landlord-instapay-quick-response-codes/old.jpg',
        ]);

        $storageServiceMock = Mockery::mock(PaymentVerificationImageStorageService::class);
        $storageServiceMock->shouldReceive('deletePrivateDiskObjectIfPresent')
            ->once()
            ->with('landlord-instapay-quick-response-codes/old.jpg');
        $storageServiceMock->shouldReceive('persistInstapayCodeFromLandlord')
            ->once()
            ->andThrow(new RuntimeException('Simulated storage failure'));

        $this->app->instance(PaymentVerificationImageStorageService::class, $storageServiceMock);

        $response = $this->actingAs($landlord)->post(
            route('landlord.payments.instapay-code.update'),
            [
                'instapay_quick_response_code_image' => $this->fakePngUpload(),
            ]
        );

        $response->assertRedirect(route('landlord.payments'));
        $response->assertSessionHas('error', 'Failed to save InstaPay quick response code. Please try again.');

        $landlord->refresh();
        $this->assertSame(
            'landlord-instapay-quick-response-codes/old.jpg',
            $landlord->landlord_instapay_quick_response_code_image_path
        );
    }
}
