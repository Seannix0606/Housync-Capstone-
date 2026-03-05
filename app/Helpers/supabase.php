<?php

use App\Services\SupabaseService;

if (!function_exists('supabase')) {
    function supabase()
    {
        return app(SupabaseService::class);
    }
}

