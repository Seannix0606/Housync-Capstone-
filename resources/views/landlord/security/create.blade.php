@extends('layouts.landlord-app')

@section('title', 'Assign RFID Card')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Assign RFID Card</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('landlord.security', ['apartment_id' => $apartmentId]) }}">Security</a>
                    </li>
                    <li class="breadcrumb-item active">Assign Card</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('landlord.security', ['apartment_id' => $apartmentId]) }}" 
           class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Security
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading">Please fix the following errors:</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-id-card"></i> Card Assignment Form
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('landlord.security.store-card') }}">
                        @csrf
                        
                        <!-- Card UID -->
                        <div class="mb-3">
                            <label for="card_uid" class="form-label required">Card UID</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control @error('card_uid') is-invalid @enderror" 
                                       id="card_uid" 
                                       name="card_uid" 
                                       value="{{ old('card_uid') }}"
                                       placeholder="Automatically scanning for RFID cards..."
                                       style="font-family: monospace;"
                                       readonly
                                       required>
                                <button type="button" class="btn btn-primary" id="btn-web-scan">
                                    <i class="fas fa-credit-card me-1"></i> Scan New Card
                                </button>
                                <div class="input-group-text" id="scan-indicator" style="display:none;">
                                    <i class="fas fa-radio text-primary" id="scan-icon"></i>
                                </div>
                                
                                <!-- Dropdown appears after pressing Scan New Card -->
                                <select class="form-select d-none ms-2" id="card_uid_select" aria-label="Select scanned Card UID"></select>
                            </div>
                            <div class="form-text" id="card-uid-help">
                                Tap any RFID card on the reader now. The Card UID will be automatically detected and filled in.
                            </div>
                            @error('card_uid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Apartment Selection -->
                        <div class="mb-3">
                            <label for="apartment_id" class="form-label required">Apartment</label>
                            <select class="form-select @error('apartment_id') is-invalid @enderror" 
                                    id="apartment_id" 
                                    name="apartment_id" 
                                    required>
                                <option value="">Select an apartment</option>
                                @foreach($apartments as $apartment)
                                    <option value="{{ $apartment->id }}" 
                                            {{ (old('apartment_id') ?: $apartmentId) == $apartment->id ? 'selected' : '' }}>
                                        {{ $apartment->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('apartment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tenant Assignment -->
                        <div class="mb-3">
                            <label for="tenant_assignment_id" class="form-label required">Assign to Tenant</label>
                            <select class="form-select @error('tenant_assignment_id') is-invalid @enderror" 
                                    id="tenant_assignment_id" 
                                    name="tenant_assignment_id" 
                                    required>
                                <option value="">Select a tenant</option>
                                @foreach($tenantAssignments as $assignment)
                                    <option value="{{ $assignment->id }}" 
                                            data-apartment="{{ $assignment->unit->apartment_id }}"
                                            {{ old('tenant_assignment_id') == $assignment->id ? 'selected' : '' }}>
                                        {{ $assignment->tenant->name }} - Unit {{ $assignment->unit->unit_number }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Only active tenants are shown. Make sure the tenant is assigned to a unit first.
                            </div>
                            @error('tenant_assignment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Card Name (Optional) -->
                        <div class="mb-3">
                            <label for="card_name" class="form-label">Card Name (Optional)</label>
                            <input type="text" 
                                   class="form-control @error('card_name') is-invalid @enderror" 
                                   id="card_name" 
                                   name="card_name" 
                                   value="{{ old('card_name') }}"
                                   placeholder="e.g., Main Access Card, Backup Card">
                            <div class="form-text">
                                Give this card a descriptive name to help identify it later.
                            </div>
                            @error('card_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Expiration Date (Optional) -->
                        <div class="mb-3">
                            <label for="expires_at" class="form-label">Expiration Date (Optional)</label>
                            <input type="date" 
                                   class="form-control @error('expires_at') is-invalid @enderror" 
                                   id="expires_at" 
                                   name="expires_at" 
                                   value="{{ old('expires_at') }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            <div class="form-text">
                                Leave blank if the card should never expire. Card will automatically be deactivated after this date.
                            </div>
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3"
                                      placeholder="Add any additional notes about this card assignment...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('landlord.security', ['apartment_id' => $apartmentId]) }}" 
                               class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Assign Card
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Help Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-question-circle"></i> How to Assign RFID Cards
                    </h6>
                </div>
                <div class="card-body">
                    <div class="step-list">
                        <div class="step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <strong>Tap RFID Card:</strong> Simply tap any RFID card on the ESP32 reader. The Card UID will be automatically detected and filled in.
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <strong>Fill Details:</strong> Select the apartment and tenant, then add any optional details like card name or expiration date.
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <strong>Assign Card:</strong> Click "Assign Card" to complete the assignment. The card will be immediately active for access.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Tips -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-shield-alt"></i> Security Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Set expiration dates for temporary access
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Use descriptive names for easy identification
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Regularly review and audit card assignments
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            Deactivate cards immediately when tenants move out
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Direct scan status display -->
<div id="scan-status-container" class="mt-3" style="display: none;">
    <div class="alert alert-info" id="scan-status">
        <i class="fas fa-spinner fa-spin me-2"></i>
        <span id="scan-status-text">Preparing to scan...</span>
    </div>
</div>
@endsection

@section('styles')
<style>
    .required::after {
        content: " *";
        color: #dc3545;
    }
    
    .step-list {
        list-style: none;
        padding: 0;
    }
    
    .step {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    
    .step:last-child {
        margin-bottom: 0;
    }
    
    .step-number {
        background: #007bff;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: bold;
        margin-right: 0.75rem;
        flex-shrink: 0;
    }
    
    .step-content {
        font-size: 0.875rem;
        line-height: 1.4;
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    /* RFID Scan Modal Styles */
    .scan-icon-container {
        width: 80px;
        height: 80px;
        margin: 0 auto;
        border-radius: 50%;
        background: linear-gradient(135deg, #007bff, #0056b3);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    }
    
    .scan-icon {
        font-size: 2rem;
        color: white;
        transition: transform 0.3s ease;
    }
    
    .scan-icon.scanning {
        animation: pulse-scan 1.5s infinite;
    }
    
    @keyframes pulse-scan {
        0% { transform: scale(1) rotate(0deg); }
        50% { transform: scale(1.1) rotate(180deg); }
        100% { transform: scale(1) rotate(360deg); }
    }
    
    .card-uid-display {
        font-family: 'Courier New', monospace;
        font-size: 1.2rem;
        font-weight: bold;
        color: #155724;
        background: rgba(212, 237, 218, 0.5);
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        display: inline-block;
        margin-top: 0.5rem;
    }
    
    #scanCardModal .modal-content {
        border-radius: 1rem;
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
    }
    
    #scanCardModal .modal-header {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 1rem 1rem 0 0;
        border-bottom: 1px solid #dee2e6;
    }
    
    .progress {
        height: 8px;
        background-color: #e9ecef;
        border-radius: 0.5rem;
    }
    
    .progress-bar {
        background: linear-gradient(90deg, #007bff, #0056b3);
        border-radius: 0.5rem;
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const apartmentSelect = document.getElementById('apartment_id');
    const tenantSelect = document.getElementById('tenant_assignment_id');
    const tenantOptions = Array.from(tenantSelect.options);
    
    // Tenant filtering functionality
    function filterTenants() {
        const selectedApartment = apartmentSelect.value;
        
        // Clear current options except the first one
        tenantSelect.innerHTML = '<option value="">Select a tenant</option>';
        
        // Add back filtered options
        tenantOptions.forEach(option => {
            if (option.value && (!selectedApartment || option.dataset.apartment === selectedApartment)) {
                tenantSelect.appendChild(option.cloneNode(true));
            }
        });
    }
    
    apartmentSelect.addEventListener('change', filterTenants);
    
    // Initial filter
    filterTenants();
    
    // Automatic Card UID Scanner - Direct ESP32 Connection
    const cardUidInput = document.getElementById('card_uid');
    const scanStatusContainer = document.getElementById('scan-status-container');
    const scanStatus = document.getElementById('scan-status');
    const scanIcon = document.getElementById('scan-icon');
    const cardUidHelp = document.getElementById('card-uid-help');
    const cardUidSelect = document.getElementById('card_uid_select');
    
    let isScanning = false;
    let scanInterval = null;
    
    // Wire up the explicit Scan button
    const btnWebScan = document.getElementById('btn-web-scan');
    btnWebScan?.addEventListener('click', () => {
        startWebScanFlow();
    });
    
    // Start automatic scanning when page loads (still available as passive fallback)
    startAutomaticScanning();
    
    // Also check immediately for any recently scanned cards
    setTimeout(() => {
        checkForNewCard();
    }, 500);
    
    function startAutomaticScanning() {
        if (scanInterval) return; // Already scanning
        
        updateScanState('scanning', 'Listening for RFID cards...');
        
        // Check for new cards every 1 second (faster real-time updates)
        scanInterval = setInterval(() => {
            checkForNewCard();
        }, 1000);
        
        // Also check immediately
        checkForNewCard();
    }
    
    function stopAutomaticScanning() {
        if (scanInterval) {
            clearInterval(scanInterval);
            scanInterval = null;
        }
        updateScanState('idle', 'Card detected and assigned!');
    }
    
    // Web-triggered scan request (creates a scan request file and polls for status)
    function startWebScanFlow() {
        if (isScanning) return;
        isScanning = true;
        updateScanState('scanning', 'Starting web scan... Please tap a new card.');
        scanIcon.classList.add('scanning');
        // Prepare dropdown UI
        cardUidSelect.classList.add('d-none');
        cardUidSelect.innerHTML = '';
        // Hide text input so dropdown becomes primary UI; keep value in hidden input for submission
        cardUidInput.classList.add('d-none');
        
        // 1) Immediately load the most recent UID from latest_card.json
        fetch('/api/rfid/latest-uid', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(d => {
            if (d && d.success && d.card_uid) {
                addUidOption(d.card_uid, true);
                updateScanState('info', `Loaded recent Card UID: ${d.card_uid}`);
            }
        })
        .catch(() => {/* ignore if none yet */});

        // 2) Start a lightweight polling of latest-uid as a fallback in parallel (up to 20s)
        const latestPollStartedAt = Date.now();
        let latestPollTimer = setInterval(() => {
            if (Date.now() - latestPollStartedAt > 20000) { // 20s
                clearInterval(latestPollTimer);
                return;
            }
            fetch('/api/rfid/latest-uid')
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(d => {
                    if (d && d.success && d.card_uid) {
                        // If dropdown is empty or value differs, update and select
                        if (!cardUidSelect.value || cardUidSelect.value !== d.card_uid) {
                            addUidOption(d.card_uid, true);
                            updateScanState('success', `✅ Card detected: ${d.card_uid}`);
                            clearInterval(latestPollTimer);
                            isScanning = false;
                            stopAutomaticScanning();
                        }
                    }
                })
                .catch(() => {});
        }, 1000);

        fetch('/api/rfid/scan/request', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.scan_id) {
                throw new Error(data.error || 'Failed to start scan');
            }
            const scanId = data.scan_id;
            const timeoutMs = (data.timeout || 15) * 1000;
            const startedAt = Date.now();
            updateScanState('scanning', 'Listening for a new card...');
            
            const poll = () => {
                fetch(`/api/rfid/scan/status/${scanId}`)
                    .then(r => r.json())
                    .then(s => {
                        if (s.success && s.status === 'completed' && s.card_uid) {
                            // Add scanned UID to dropdown (deduped) and select it
                            addUidOption(s.card_uid, true);
                            updateScanState('success', `✅ Card detected: ${s.card_uid}`);
                            cardUidInput.classList.add('border-success');
                            isScanning = false;
                            stopAutomaticScanning();
                            // Stop fallback polling
                            try { clearInterval(latestPollTimer); } catch {}
                            return;
                        }
                        if (s.status === 'timeout' || Date.now() - startedAt > timeoutMs) {
                            updateScanState('error', 'Scan timed out. Try again.');
                            isScanning = false;
                            return;
                        }
                        setTimeout(poll, 1000);
                    })
                    .catch(() => {
                        updateScanState('error', 'Scan failed. Try again.');
                        isScanning = false;
                    });
            };
            poll();
        })
        .catch(err => {
            console.error('Web scan error:', err);
            updateScanState('error', err.message || 'Failed to start scan');
            isScanning = false;
        });
    }

    // Helper: add UID to dropdown (dedupe) and optionally select
    function addUidOption(uid, selectIt = false) {
        if (!uid) return;
        let exists = false;
        Array.from(cardUidSelect.options).forEach(opt => { if (opt.value === uid) exists = true; });
        if (!exists) {
            const opt = document.createElement('option');
            opt.value = uid;
            opt.textContent = uid;
            cardUidSelect.appendChild(opt);
        }
        cardUidSelect.classList.remove('d-none');
        if (selectIt) {
            cardUidSelect.value = uid;
            // Mirror selection to the hidden text input so form submits same field name
            cardUidInput.value = uid;
        }
    }

    // Keep hidden input in sync when user changes dropdown manually
    cardUidSelect.addEventListener('change', () => {
        cardUidInput.value = cardUidSelect.value || '';
    });
    
    function checkForNewCard() {
        if (isScanning) return;
        isScanning = true;
        
        // Check for the latest card from ESP32Reader.php
        fetch('/api/rfid/latest-uid', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.card_uid && !cardUidInput.value) {
                // Success! New card detected
                cardUidInput.value = data.card_uid;
                cardUidInput.classList.add('border-success');
                
                let message = `✅ Card detected: ${data.card_uid}`;
                if (data.age_seconds !== undefined && data.age_seconds < 600) {
                    updateScanState('success', message);
                    cardUidHelp.innerHTML = `Card UID detected: <strong>${data.card_uid}</strong>. You can now complete the assignment.`;
                    
                    // Stop scanning after successful detection
                    stopAutomaticScanning();
                    
                    // Remove success styling after a while
                    setTimeout(() => {
                        cardUidInput.classList.remove('border-success');
                    }, 5000);
                }
            } else if (data.error && data.error.includes('No card has been scanned')) {
                // No cards scanned yet, keep scanning
                updateScanState('scanning', 'Waiting for RFID card... ESP32Reader.php is running.');
            } else if (data.error && data.error.includes('too old')) {
                // Card is too old, keep scanning
                updateScanState('scanning', 'Listening for new RFID cards...');
            }
            
            isScanning = false;
        })
        .catch(error => {
            console.error('ESP32Reader error:', error);
            updateScanState('error', 'ESP32Reader.php not running. Please start: php ESP32Reader.php --port=COM3');
            isScanning = false;
        });
    }
    
    function tryFallbackGenerator() {
        showScanStatus('info', 'Using test Card UID generator...');
        
        fetch('/api/rfid/generate-uid', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.card_uid) {
                cardUidInput.value = data.card_uid;
                cardUidInput.classList.add('border-success');
                
                let message = `Test Card UID: ${data.card_uid}`;
                if (data.test_mode) {
                    message += ' (Generated for testing)';
                }
                showScanStatus('info', message);
                
                setTimeout(() => {
                    hideScanStatus();
                    cardUidInput.classList.remove('border-success');
                }, 4000);
            } else {
                showManualUIDInput();
            }
        })
        .catch(error => {
            console.error('Fallback generator error:', error);
            showManualUIDInput();
        });
    }
    
    function generateTestUID() {
        // Generate a random 8-character hex UID for testing
        const chars = '0123456789ABCDEF';
        let uid = '';
        for (let i = 0; i < 8; i++) {
            uid += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return uid;
    }
    
    function showManualUIDInput() {
        // If automatic scanning fails, allow manual input
        cardUidInput.readOnly = false;
        cardUidInput.placeholder = 'Enter Card UID manually (e.g., A1B2C3D4)';
        cardUidInput.focus();
        
        showScanStatus('warning', 'Automatic scan failed. Please enter Card UID manually or check ESP32 connection.');
        
        // Add input validation
        cardUidInput.addEventListener('input', function() {
            let value = this.value.toUpperCase().replace(/[^0-9A-F]/g, '');
            if (value.length > 8) value = value.substring(0, 8);
            this.value = value;
            
            if (value.length === 8) {
                this.classList.add('border-success');
                showScanStatus('success', `Card UID entered: ${value}`);
            } else {
                this.classList.remove('border-success');
            }
        });
    }
    
    
    function showScanStatus(type, message) {
        scanStatusContainer.style.display = 'block';
        scanStatus.className = `alert alert-${type}`;
        
        let icon = 'fas fa-info-circle';
        if (type === 'success') icon = 'fas fa-check-circle';
        if (type === 'danger') icon = 'fas fa-exclamation-triangle';
        if (type === 'info') icon = 'fas fa-spinner fa-spin';
        
        scanStatus.innerHTML = `<i class="${icon} me-2"></i><span id="scan-status-text">${message}</span>`;
    }
    
    function hideScanStatus() {
        scanStatusContainer.style.display = 'none';
    }
    
    function updateScanState(state, message) {
        if (state === 'scanning') {
            scanIcon.className = 'fas fa-satellite-dish text-primary';
            scanIcon.style.animation = 'pulse-scan 1.5s infinite';
            cardUidHelp.innerHTML = `<i class="fas fa-radio text-primary me-1"></i> ${message}`;
        } else if (state === 'success') {
            scanIcon.className = 'fas fa-check-circle text-success';
            scanIcon.style.animation = 'none';
            cardUidHelp.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i> ${message}`;
        } else if (state === 'error') {
            scanIcon.className = 'fas fa-exclamation-triangle text-warning';
            scanIcon.style.animation = 'none';
            cardUidHelp.innerHTML = `<i class="fas fa-exclamation-triangle text-warning me-1"></i> ${message}`;
        } else if (state === 'idle') {
            scanIcon.className = 'fas fa-id-card text-success';
            scanIcon.style.animation = 'none';
            cardUidHelp.innerHTML = `<i class="fas fa-check text-success me-1"></i> ${message}`;
        }
    }
    
    // Allow manual input if automatic scanning fails
    function enableManualInput() {
        cardUidInput.readOnly = false;
        cardUidInput.placeholder = 'Enter Card UID manually (e.g., A1B2C3D4)';
        cardUidInput.focus();
        updateScanState('error', 'Automatic scan failed. Please enter Card UID manually.');
        
        // Add input validation
        cardUidInput.addEventListener('input', function() {
            let value = this.value.toUpperCase().replace(/[^0-9A-F]/g, '');
            if (value.length > 8) value = value.substring(0, 8);
            this.value = value;
            
            if (value.length === 8) {
                this.classList.add('border-success');
                updateScanState('success', `Card UID entered: ${value}`);
                stopAutomaticScanning();
            } else {
                this.classList.remove('border-success');
            }
        });
    }
    
    // Stop scanning if user starts typing manually
    cardUidInput.addEventListener('focus', function() {
        if (this.readOnly === false) {
            stopAutomaticScanning();
        }
    });
    
    // Add a manual override button (hidden by default)
    setTimeout(() => {
        if (!cardUidInput.value && cardUidInput.readOnly) {
            // After 30 seconds, offer manual input option
            setTimeout(() => {
                if (!cardUidInput.value) {
                    cardUidHelp.innerHTML += ' <a href="#" onclick="enableManualInput(); return false;" class="text-primary">Enter manually</a>';
                }
            }, 30000);
        }
    }, 1000);
});
</script>
@endsection
