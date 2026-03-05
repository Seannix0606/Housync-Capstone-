<?php

/**
 * Enhanced ESP32 RFID Reader for PHP
 * Command-line script to read RFID data from ESP32 via serial port
 * Now supports web scan requests from Laravel application
 * 
 * Usage: php ESP32Reader.php --port=COM7 --url=https://housync.up.railway.app
 */

class ESP32Reader
{
    private $port;
    private $baudrate;
    private $laravelUrl;
    private $apiEndpoint;
    private $handle;
    private $running = false;
    private $lastScanRequestCheck = 0;
    // De-duplication for rapid repeated lines from serial
    private $lastProcessedUid = null;
    private $lastProcessedAt = 0; // unix timestamp seconds
    private $dedupeWindowSeconds = 10; // suppress repeats within 10 seconds - only allow one scan per card per 10s

    public function __construct ($port = 'COM3', $baudrate = 115200, $laravelUrl = 'https://housync.up.railway.app')
    {
        $this->port = $port;
        $this->baudrate = $baudrate;
        $this->laravelUrl = rtrim($laravelUrl, '/');
        $this->apiEndpoint = $this->laravelUrl . '/api/rfid-scan';
        
        echo "Enhanced ESP32 RFID Reader Initialized\n";
        echo "Port: {$this->port}\n";
        echo "Baudrate: {$this->baudrate}\n";
        echo "Laravel URL: {$this->laravelUrl}\n";
        echo "API Endpoint: {$this->apiEndpoint}\n";
        echo "Web Scan Requests: Enabled\n";
        echo str_repeat('-', 50) . "\n";
    }

    /**
     * Connect to ESP32 serial port
     */
    public function connect()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows - Configure serial port
            echo "Configuring Windows serial port {$this->port}...\n";
            $command = "mode {$this->port}: BAUD={$this->baudrate} PARITY=N DATA=8 STOP=1";
            echo "Running: $command\n";
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                echo "Port configuration output:\n";
                foreach ($output as $line) {
                    echo "  $line\n";
                }
                throw new Exception("Failed to configure port {$this->port}. Return code: $returnVar");
            }

            // Try to open the port
            echo "Opening serial port {$this->port}...\n";
            $this->handle = fopen($this->port, 'r+b');
        } else {
            // Linux/macOS
            echo "Opening serial port {$this->port} (Unix)...\n";
            $this->handle = fopen($this->port, 'r+b');
        }

        if (!$this->handle) {
            throw new Exception("Cannot open serial port {$this->port}. Check if ESP32 is connected and port is correct.");
        }

        // Set non-blocking mode
        stream_set_blocking($this->handle, false);
        
        echo "Connected to ESP32 on {$this->port} at {$this->baudrate} baud\n";
        
        // Send a test ping to ESP32
        fwrite($this->handle, "PING\n");
        fflush($this->handle);
        echo "Sent PING to ESP32...\n";
        return true;
    }

    /**
     * Read data from ESP32
     */
    public function readData()
    {
        if (!$this->handle) {
            return false;
        }

        $data = fgets($this->handle);
        if ($data !== false && !empty(trim($data))) {
            return trim($data);
        }
        
        return false;
    }

    /**
     * Send RFID data to Laravel API
     */
    public function sendToLaravel($cardUID, $timestamp = null)
    {
        if (!$timestamp) {
            $timestamp = date('Y-m-d H:i:s');
        }

        echo "Preparing to send: cardUID=$cardUID, timestamp=$timestamp\n";
        
        $postData = json_encode([
            'cardUID' => $cardUID,
            'timestamp' => $timestamp
        ]);
        
        echo "JSON payload: $postData\n";

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($postData),
                    'User-Agent: ESP32Reader/1.0'
                ],
                'content' => $postData,
                'timeout' => 30
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false
            ]
        ]);

        echo "Sending POST to: {$this->apiEndpoint}\n";
        $response = @file_get_contents($this->apiEndpoint, false, $context);
        
        if ($response === false) {
            echo "Failed to connect to Laravel API at {$this->apiEndpoint}\n";
            $error = error_get_last();
            if ($error) {
                echo "Error: {$error['message']}\n";
            }
            return false;
        }
        
        echo "Got response from Laravel: $response\n";

        $responseData = json_decode($response, true);
        
        if ($responseData && isset($responseData['success']) && $responseData['success']) {
            echo "Data sent successfully to Laravel\n";
            if (isset($responseData['card_uid'])) {
                echo "Card: {$responseData['card_uid']}\n";
                
                // Show appropriate status based on card status
                if (isset($responseData['card_status'])) {
                    if ($responseData['card_status'] === 'new_card') {
                        echo "Status: New card detected (ready for assignment)\n";
                    } elseif ($responseData['card_status'] === 'registered_card') {
                        $entryState = isset($responseData['entry_state']) ? strtoupper($responseData['entry_state']) : null; // IN/OUT
                        if (isset($responseData['access_granted']) && $responseData['access_granted']) {
                            // Show IN/OUT instead of GRANTED
                            if ($entryState === 'IN' || $entryState === 'OUT') {
                                echo "Entry: {$entryState}\n";
                            } else {
                                echo "Access: GRANTED\n"; // fallback
                            }
                            if (isset($responseData['tenant_name'])) {
                                echo "Tenant: {$responseData['tenant_name']}\n";
                            }
                        } else {
                            // Denied case: include intended entry state if available
                            if ($entryState === 'IN' || $entryState === 'OUT') {
                                echo "Entry: {$entryState} (DENIED)\n";
                            } else {
                                echo "Access: DENIED\n";
                            }
                            if (isset($responseData['denial_reason'])) {
                                echo "   Reason: {$responseData['denial_reason']}\n";
                            }
                        }
                    }
                } else {
                    // Fallback for older response format
                    if (isset($responseData['access_granted'])) {
                        if ($responseData['access_granted']) {
                            echo "Access: GRANTED\n";
                            if (isset($responseData['tenant_name'])) {
                                echo "Tenant: {$responseData['tenant_name']}\n";
                            }
                        } else {
                            if (isset($responseData['denial_reason']) && $responseData['denial_reason'] !== 'card_not_found') {
                                echo "Access: DENIED\n";
                                echo "Reason: {$responseData['denial_reason']}\n";
                            } else {
                                echo "Status: New card detected\n";
                            }
                        }
                    }
                }
                
                // Show message if available
                if (isset($responseData['message'])) {
                    echo "Message: {$responseData['message']}\n";
                }
            }
            return true;
        } else {
            echo "Laravel API returned error: " . ($responseData['message'] ?? 'Unknown error') . "\n";
            return false;
            //--
        }
    }

    /**
     * Check for web scan requests from Laravel
     */
    public function checkWebScanRequests()
    {
        $scanRequestDir = dirname(__FILE__) . '/storage/app/scan_requests';
        
        // Create directory if it doesn't exist
        if (!is_dir($scanRequestDir)) {
            mkdir($scanRequestDir, 0755, true);
        }
        
        $requestFiles = glob($scanRequestDir . '/web_scan_*.json');
        
        foreach ($requestFiles as $requestFile) {
            if (!file_exists($requestFile)) {
                continue;
            }
            
            $requestData = json_decode(file_get_contents($requestFile), true);
            
            if (!$requestData || $requestData['status'] !== 'pending') {
                continue;
            }
            
            // Check if request has timed out
            $requestedAt = strtotime($requestData['requested_at']);
            $timeout = $requestData['timeout'];
            
            if (time() - $requestedAt > $timeout) {
                // Mark as timed out
                $requestData['status'] = 'timeout';
                $requestData['error'] = 'Request timed out';
                file_put_contents($requestFile, json_encode($requestData, JSON_PRETTY_PRINT));
                echo "Web scan request timed out: {$requestData['scan_id']}\n";
                continue;
            }
            
            echo "Processing web scan request: {$requestData['scan_id']}\n";
            echo "Waiting for RFID card tap...\n";
            
            // Send scan request to ESP32
            if ($this->handle) {
                fwrite($this->handle, "SCAN_REQUEST\n");
                fflush($this->handle);
            }
            
            // Mark as processing
            $requestData['status'] = 'processing';
            file_put_contents($requestFile, json_encode($requestData, JSON_PRETTY_PRINT));
        }
    }
    
    /**
     * Store the latest card UID for web interface access
     */
    public function storeLatestCardUID($cardUID)
    {
        $latestCardFile = dirname(__FILE__) . '/storage/app/latest_card.json';
        $latestCardDir = dirname($latestCardFile);
        
        // Create directory if it doesn't exist
        if (!is_dir($latestCardDir)) {
            mkdir($latestCardDir, 0755, true);
        }
        
        $latestCardData = [
            'card_uid' => $cardUID,
            'scanned_at' => date('c'),
            'timestamp' => time()
        ];
        
        $jsonResult = file_put_contents($latestCardFile, json_encode($latestCardData, JSON_PRETTY_PRINT));
        if ($jsonResult !== false) {
            echo "Latest card UID stored: $cardUID (file: $latestCardFile)\n";
        } else {
            echo "Failed to store latest card UID\n";
        }
    }

    /**
     * Fulfill web scan request with detected card UID
     */
    public function fulfillWebScanRequest($cardUID)
    {
        $scanRequestDir = dirname(__FILE__) . '/storage/app/scan_requests';
        $requestFiles = glob($scanRequestDir . '/web_scan_*.json');
        
        foreach ($requestFiles as $requestFile) {
            if (!file_exists($requestFile)) {
                continue;
            }
            
            $requestData = json_decode(file_get_contents($requestFile), true);
            
            if (!$requestData || $requestData['status'] !== 'processing') {
                continue;
            }
            
            // Fulfill the request
            $requestData['status'] = 'completed';
            $requestData['card_uid'] = $cardUID;
            $requestData['completed_at'] = date('c');
            
            file_put_contents($requestFile, json_encode($requestData, JSON_PRETTY_PRINT));
            
            echo "Web scan request fulfilled: {$requestData['scan_id']} with card: $cardUID\n";
            
            // Only fulfill the first matching request
            break;
        }
    }

    /**
     * Process incoming RFID data
     */
    public function processRfidData($data)
    {
        echo "\n[PROCESSING] Raw data received: '$data' (length: " . strlen($data) . ")\n";
        
        // Handle different data formats from ESP32
        $originalData = $data;
        
        // Case 1: Pure JSON data (starts with {)
        if (substr(trim($data), 0, 1) === '{') {
            echo "Found pure JSON data\n";
            // Data is already JSON, use as-is
        }
        // Case 2: Prefixed JSON (📤 Sent to bridge: {...})
        else if (strpos($data, 'Sent to bridge:') !== false) {
            $jsonStart = strpos($data, '{');
            if ($jsonStart !== false) {
                $data = substr($data, $jsonStart);
                echo "Extracted JSON from prefixed message\n";
            }
        }
        // Case 3: Card detected message (Card detected: 036E8DE4)
        else if (preg_match('/Card detected:\s*([A-F0-9]+)/i', $data, $matches)) {
            // Create JSON from the extracted UID
            $extractedUID = $matches[1];
            $data = json_encode([
                'cardUID' => $extractedUID,
                'timestamp' => (string)time(),
                'reader_location' => 'main_entrance',
                'device_id' => 'esp32_serial'
            ]);
            echo "Extracted UID from debug message: $extractedUID\n";
        }
        // Case 4: Filter out other messages
        else {
            $ignoredMessages = [
                'v:', 'mode:', 'load:', 'entry', 'Firmware Version:', 'MFRC522', 
                'RFID Reader initialized', 'Ready to scan cards', 'ESP32 RFID Serial Bridge Ready',
                'Mode: Serial Bridge', 'SCAN_REQUEST_ACTIVE', 'Please tap your RFID card',
                'SCAN_COMPLETED', 'PONG', '========================================', '---'
            ];
            
            foreach ($ignoredMessages as $ignored) {
                if (strpos($data, $ignored) !== false) {
                    echo "Ignoring: " . substr($originalData, 0, 50) . "...\n";
                    return false;
                }
            }
        }

        $cardUID = null;
        $timestamp = null;

        // Try to parse as JSON first
        $jsonData = json_decode($data, true);
        if ($jsonData && isset($jsonData['cardUID'])) {
            $cardUID = $jsonData['cardUID'];
            $timestamp = $jsonData['timestamp'] ?? null;
            echo "Parsed JSON data: cardUID = $cardUID, timestamp = $timestamp\n";
        } else if (preg_match('/^([A-F0-9]+)(:(\d+))?$/i', $data, $matches)) {
            // Try simple format: CARD_UID:TIMESTAMP or just CARD_UID
            $cardUID = $matches[1];
            $timestamp = isset($matches[3]) ? date('Y-m-d H:i:s', $matches[3] / 1000) : null;
            echo "Parsed simple format: cardUID = $cardUID\n";
        } else {
            echo "Failed to parse data:\n";
            echo "  - Raw data: '$data'\n";
            echo "  - Length: " . strlen($data) . "\n";
            echo "  - Starts with '{': " . (substr($data, 0, 1) === '{' ? 'yes' : 'no') . "\n";
            echo "  - JSON decode error: " . json_last_error_msg() . "\n";
            echo "  - Contains cardUID: " . (strpos($data, 'cardUID') !== false ? 'yes' : 'no') . "\n";
            return false;
        }

        if ($cardUID) {
            // Dedupe: suppress rapid duplicate submissions of the same UID
            $now = time();
            if ($this->lastProcessedUid === $cardUID && ($now - $this->lastProcessedAt) < $this->dedupeWindowSeconds) {
                echo "Duplicate UID within {$this->dedupeWindowSeconds}s window, ignoring: $cardUID (last scan was " . ($now - $this->lastProcessedAt) . "s ago)\n";
                return false;
            }
            $this->lastProcessedUid = $cardUID;
            $this->lastProcessedAt = $now;
            echo "Processing RFID card: $cardUID\n";
            
            // Store the latest card UID for web interface access
            echo "Storing latest card UID...\n";
            $this->storeLatestCardUID($cardUID);
            
            // Check if this fulfills any web scan requests
            echo "Checking web scan requests...\n";
            $this->fulfillWebScanRequest($cardUID);
            
            // Send to Laravel for activity logging (including new cards)
            echo "Sending to Laravel API...\n";
            $result = $this->sendToLaravel($cardUID, $timestamp);
            
            if ($result) {
                echo "Successfully processed card: $cardUID\n";
            } else {
                echo "Failed to send card to Laravel\n";
            }
            
            return $result;
        }
        
        echo "No card UID found in data: '$data'\n";
        return false;
    }

    /**
     * Test Laravel connection
     */
    public function testLaravelConnection()
    {
        echo "Testing Laravel connection...\n";
        
        $testUrl = $this->laravelUrl . '/api/system-info';
        
        // Create SSL context for HTTPS connections
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: ESP32Reader/1.0'
                ],
                'timeout' => 30
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false
            ]
        ]);
        
        $response = @file_get_contents($testUrl, false, $context);
        
        if ($response === false) {
            echo "Cannot connect to Laravel at {$this->laravelUrl}\n";
            $error = error_get_last();
            if ($error) {
                echo "Error: {$error['message']}\n";
            }
            echo "Make sure your Railway app is running and accessible\n";
            return false;
        }
        
        $data = json_decode($response, true);
        if ($data) {
            echo "Laravel connection successful\n";
            echo "PHP Version: " . ($data['php_version'] ?? 'Unknown') . "\n";
            echo "Laravel Version: " . ($data['laravel_version'] ?? 'Unknown') . "\n";
            echo "Database Connected: " . ($data['database_connected'] ? 'Yes' : 'No') . "\n";
            return true;
        }
        
        echo "Laravel responded but with unexpected data\n";
        return false;
    }

    /**
     * Main reading loop with web scan request support
     */
    public function run()
    {
        $this->running = true;
        
        echo "Starting Enhanced RFID reader...\n";
        echo "Tap RFID cards on the reader\n";
        echo "Monitoring for web scan requests\n";
        echo "Press Ctrl+C to stop\n";
        echo str_repeat('-', 50) . "\n";

        // Test Laravel connection first (but continue anyway for debugging)
        if (!$this->testLaravelConnection()) {
            echo "Laravel connection failed, but continuing for debugging...\n";
        }

        // Connect to ESP32
        try {
            $this->connect();
        } catch (Exception $e) {
            echo "Connection failed: " . $e->getMessage() . "\n";
            return false;
        }

        $lastDataTime = 0;
        $emptyReads = 0;
        
        while ($this->running) {
            $data = $this->readData();
            
            if ($data !== false) {
                $emptyReads = 0;
                $lastDataTime = time();
                
                echo "[MAIN LOOP] Got data from serial: '$data'\n";
                
                // Process the RFID data
                $this->processRfidData($data);
                
            } else {
                $emptyReads++;
                
                // Show status every 10 seconds of no data
                if ($emptyReads % 1000 === 0) {
                    echo "Waiting for RFID data... (" . date('H:i:s') . ")\n";
                }
            }
            
            // Check for web scan requests every 500ms
            if (time() - $this->lastScanRequestCheck >= 0.5) {
                $this->checkWebScanRequests();
                $this->lastScanRequestCheck = time();
            }
            
            // Small delay to prevent excessive CPU usage
            usleep(10000); // 10ms
            
            // Check if we should continue
            if (connection_aborted()) {
                break;
            }
        }
        
        $this->close();
        echo "\nEnhanced RFID reader stopped\n";
    }

    /**
     * Stop the reader
     */
    public function stop()
    {
        $this->running = false;
    }

    /**
     * Close serial connection
     */
    public function close()
    {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle = null;
            echo "Serial connection closed\n";
        }
    }

    /**
     * Get available COM ports (Windows)
     */
    public static function getAvailablePorts()
    {
        $ports = [];
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = 'powershell -Command "[System.IO.Ports.SerialPort]::GetPortNames()"';
            exec($command, $output, $returnVar);
            
            if ($returnVar === 0) {
                $ports = array_filter($output, function($port) {
                    return !empty(trim($port));
                });
            }
        }
        
        if (empty($ports)) {
            // Fallback to common ports
            $ports = ['COM1', 'COM3', 'COM7', 'COM8', 'COM9', 'COM10', 'COM11'];
        }
        
        return $ports;
    }
}

// Signal handling for graceful shutdown
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGINT, function($signal) {
        global $reader;
        if ($reader) {
            $reader->stop();
        }
        exit(0);
    });
}

// Command line execution
if (php_sapi_name() === 'cli') {
    // Parse command line arguments
    $options = [
        'port' => 'COM3',
        'url' => 'https://housync.up.railway.app',
        'help' => false
    ];
    
    $shortopts = "h";
    $longopts = ["port:", "url:", "help"];
    $parsed = getopt($shortopts, $longopts);
    
    if (isset($parsed['h']) || isset($parsed['help'])) {
        echo "Enhanced ESP32 RFID Reader for Laravel\n";
        echo "Usage: php ESP32Reader.php [options]\n\n";
        echo "Options:\n";
        echo "  --port=COMx    Serial port (default: COM3)\n";
        echo "  --url=URL      Laravel base URL (default: https://housync.up.railway.app)\n";
        echo "  --help, -h     Show this help message\n\n";
        echo "Features:\n";
        echo "   RFID data reading and Laravel API integration\n";
        echo "   Web scan request support for direct Card UID retrieval\n";
        echo "   Activity logging to Laravel database\n";
        echo "   Real-time card processing\n\n";
        echo "Available COM ports:\n";
        $ports = ESP32Reader::getAvailablePorts();
        foreach ($ports as $port) {
            echo "  - $port\n";
        }
        echo "\nExample:\n";
        echo "php ESP32Reader.php --port=COM3 --url=https://housync.up.railway.app\n";
        exit(0);
    }
    
    if (isset($parsed['port'])) {
        $options['port'] = $parsed['port'];
    }
    
    if (isset($parsed['url'])) {
        $options['url'] = $parsed['url'];
    }
    
    // Create and run the reader
    $reader = new ESP32Reader($options['port'], 115200, $options['url']);
    
    // Handle process control signals
    if (function_exists('pcntl_async_signals')) {
        pcntl_async_signals(true);
    }
    
    $reader->run();
    
} else {
    echo "This script must be run from command line\n";
}
?>