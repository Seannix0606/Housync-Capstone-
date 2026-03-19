<?php

namespace App\Contracts;

interface StorageServiceInterface
{
    /**
     * Upload a file to the given storage bucket and path.
     */
    public function uploadFile(string $bucket, string $path, mixed $file): array;

    /**
     * Get a public URL for the given bucket and path.
     */
    public function getPublicUrl(string $bucket, string $path): string;

    /**
     * Delete a file from the given storage bucket and path.
     */
    public function deleteFile(string $bucket, string $path): bool;

    /**
     * List files within a bucket, optionally scoped to a prefix/path.
     */
    public function listFiles(string $bucket, string $path = ''): ?array;
}

