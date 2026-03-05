<?php

if (!function_exists('image_url')) {
    /**
     * Get the full URL for an image/document path.
     * Handles both Supabase URLs and local storage paths.
     *
     * @param string|null $path
     * @return string|null
     */
    function image_url($path)
    {
        if (empty($path)) {
            return null;
        }

        // If already a full URL (Supabase or external), return as is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // For local storage paths, use asset helper
        return asset('storage/' . $path);
    }
}

if (!function_exists('document_url')) {
    /**
     * Alias for image_url - used for documents
     *
     * @param string|null $path
     * @return string|null
     */
    function document_url($path)
    {
        return image_url($path);
    }
}

