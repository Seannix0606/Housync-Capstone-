<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RFID ESP32 Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for ESP32 RFID card reader communication
    |
    */

    // Serial port configuration
    'com_port' => env('RFID_COM_PORT', 'COM3'),
    'baud_rate' => env('RFID_BAUD_RATE', 115200),
    
    // Scan timeout settings
    'scan_timeout' => env('RFID_SCAN_TIMEOUT', 15), // seconds
    'read_delay' => env('RFID_READ_DELAY', 2000), // milliseconds
    
    // ESP32 commands
    'commands' => [
        'ping' => 'PING',
        'scan_request' => 'SCAN_REQUEST',
        'scan_stop' => 'SCAN_STOP',
        'status' => 'STATUS',
    ],
    
    // Response patterns
    'responses' => [
        'pong' => 'PONG',
        'scan_active' => 'SCAN_REQUEST_ACTIVE',
        'scan_completed' => 'SCAN_COMPLETED',
        'scan_timeout' => 'SCAN_TIMEOUT',
    ],

];
