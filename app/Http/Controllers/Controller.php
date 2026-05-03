<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Strip non-digit characters from a phone string for consistent storage.
     */
    protected static function normalizePhone(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
    }
}
