<?php

namespace App\Services\Billing;

use App\Services\SupabaseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Persists billing-related images (tenant payment proofs, landlord InstaPay codes)
 * to Supabase when configured, otherwise to the private disk — matching app conventions.
 */
class PaymentVerificationImageStorageService
{
    public function __construct(
        protected SupabaseService $supabaseService,
    ) {}

    public function persistPaymentProofFromTenant(
        UploadedFile $proofImage,
        int $billId,
        int $tenantUserId,
    ): string {
        $safeExtension = $this->normalizedImageExtension($proofImage);
        $uniqueFileName = sprintf(
            'bill-%d-tenant-%d-%s.%s',
            $billId,
            $tenantUserId,
            Str::uuid()->toString(),
            $safeExtension,
        );
        $relativePath = 'payment-proofs/'.$uniqueFileName;

        return $this->persistToSupabaseThenPrivateDisk($proofImage, $relativePath);
    }

    public function persistInstapayCodeFromLandlord(
        UploadedFile $codeImage,
        int $landlordUserId,
    ): string {
        $safeExtension = $this->normalizedImageExtension($codeImage);
        $uniqueFileName = sprintf(
            'landlord-%d-%s.%s',
            $landlordUserId,
            Str::uuid()->toString(),
            $safeExtension,
        );
        $relativePath = 'landlord-instapay-quick-response-codes/'.$uniqueFileName;

        return $this->persistToSupabaseThenPrivateDisk($codeImage, $relativePath);
    }

    public function deletePrivateDiskObjectIfPresent(?string $storedPathOrPublicUrl): void
    {
        if ($storedPathOrPublicUrl === null || $storedPathOrPublicUrl === '') {
            return;
        }

        if (str_starts_with($storedPathOrPublicUrl, 'http')) {
            return;
        }

        if (Storage::disk('private')->exists($storedPathOrPublicUrl)) {
            Storage::disk('private')->delete($storedPathOrPublicUrl);
        }
    }

    protected function normalizedImageExtension(UploadedFile $uploadedFile): string
    {
        $extension = strtolower((string) ($uploadedFile->getClientOriginalExtension() ?: $uploadedFile->guessExtension()));
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?? '';

        return $extension === '' ? 'jpg' : $extension;
    }

    protected function persistToSupabaseThenPrivateDisk(
        UploadedFile $uploadedFile,
        string $relativePathIncludingDirectory,
    ): string {
        $bucketName = config('services.supabase.bucket');
        $uploadedFilePath = $uploadedFile->getRealPath();
        if ($uploadedFilePath === false || $uploadedFilePath === '') {
            $uploadedFilePath = $uploadedFile->getPathname();
        }

        $uploadResult = $this->supabaseService->uploadFile(
            $bucketName,
            $relativePathIncludingDirectory,
            $uploadedFilePath,
        );

        if (! empty($uploadResult['success'])) {
            $publicUrl = $uploadResult['url'] ?? null;
            if ($publicUrl) {
                return $publicUrl;
            }
        }

        Log::warning('Billing verification image Supabase upload unavailable or failed; using private disk', [
            'relative_path' => $relativePathIncludingDirectory,
            'message' => $uploadResult['message'] ?? null,
        ]);

        $directory = dirname($relativePathIncludingDirectory);
        $fileName = basename($relativePathIncludingDirectory);

        return $uploadedFile->storeAs($directory, $fileName, 'private');
    }
}
