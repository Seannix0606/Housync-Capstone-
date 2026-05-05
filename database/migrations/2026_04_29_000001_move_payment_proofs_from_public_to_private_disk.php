<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Payment proofs were previously stored on the public disk (storage/app/public/payment-proofs).
 * They now live on the private disk (storage/app/private/payment-proofs); DB paths stay payment-proofs/...
 */
return new class extends Migration
{
    public function up(): void
    {
        $fromDir = storage_path('app/public/payment-proofs');
        $toDir = storage_path('app/private/payment-proofs');

        if (! is_dir($fromDir)) {
            return;
        }

        if (! is_dir($toDir)) {
            mkdir($toDir, 0755, true);
        }

        foreach (glob($fromDir.'/*') ?: [] as $filePath) {
            if (! is_file($filePath)) {
                continue;
            }
            $basename = basename($filePath);
            $target = $toDir.DIRECTORY_SEPARATOR.$basename;
            if (file_exists($target)) {
                Log::warning("Migration skip: collision for {$basename}");

                continue;
            }
            if (! @rename($filePath, $target)) {
                Log::error("Migration failed: could not move {$basename}");
            }
        }
    }

    public function down(): void
    {
        $fromDir = storage_path('app/private/payment-proofs');
        $toDir = storage_path('app/public/payment-proofs');

        if (! is_dir($fromDir)) {
            return;
        }

        if (! is_dir($toDir)) {
            mkdir($toDir, 0755, true);
        }

        foreach (glob($fromDir.'/*') ?: [] as $filePath) {
            if (! is_file($filePath)) {
                continue;
            }
            $basename = basename($filePath);
            $target = $toDir.DIRECTORY_SEPARATOR.$basename;
            if (file_exists($target)) {
                continue;
            }
            rename($filePath, $target);
        }
    }
};
