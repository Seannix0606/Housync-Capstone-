<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RFID ESP32 Configuration
    |--------------------------------------------------------------------------
    |
    | Supports two connection modes:
    |
    |   wifi   — ESP32 connects to WiFi and POSTs card UIDs directly to the
    |             Laravel API (HousyncRFID.ino firmware).  No bridge script
    |             needs to run on a PC.  This is the recommended mode.
    |
    |   serial — ESP32 is connected via USB/serial.  ESP32Reader.php runs on
    |             the same machine as a bridge, reads serial data, and
    |             forwards card UIDs to the API.
    |
    */

    'mode' => env('RFID_MODE', 'wifi'), // 'wifi' or 'serial'

    // ── Serial bridge settings (only used when mode = serial) ────────────────

    'com_port'  => env('RFID_COM_PORT',  'COM3'),
    'baud_rate' => env('RFID_BAUD_RATE', 115200),

    // Serial commands sent to the ESP32
    'commands' => [
        'ping'         => 'PING',
        'scan_request' => 'SCAN_REQUEST',
        'scan_stop'    => 'SCAN_STOP',
        'status'       => 'STATUS',
    ],

    // Expected response strings from the ESP32
    'responses' => [
        'pong'           => 'PONG',
        'scan_active'    => 'SCAN_REQUEST_ACTIVE',
        'scan_completed' => 'SCAN_COMPLETED',
        'scan_timeout'   => 'SCAN_TIMEOUT',
    ],

    // ── Shared settings (both modes) ─────────────────────────────────────────

    // How long (seconds) the backend waits for a web-triggered card tap
    'scan_timeout' => env('RFID_SCAN_TIMEOUT', 15),

    // Delay (ms) between browser polls when waiting for a scan result
    'read_delay' => env('RFID_READ_DELAY', 2000),

    // ── WiFi mode settings (only used when mode = wifi) ──────────────────────

    // How often (ms) the ESP32 polls /api/rfid/scan/pending for web requests
    'wifi_poll_interval' => env('RFID_WIFI_POLL_INTERVAL', 2000),

    // Debounce window (ms) — same card ignored within this interval on the device
    'wifi_debounce_ms' => env('RFID_WIFI_DEBOUNCE_MS', 3000),

];
