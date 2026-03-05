<?php

namespace App\Http\Controllers;

use App\Models\RfidCard;
use App\Models\AccessLog;
use App\Models\TenantAssignment;
use App\Models\TenantRfidAssignment;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RfidController extends Controller
{
    /**
     * Display RFID management dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $apartmentId = $request->get('apartment_id');
        
        // Get landlord's properties
        $apartments = $user->properties;
        
        // If no specific apartment selected, use the first one
        if (!$apartmentId && $apartments->count() > 0) {
            $apartmentId = $apartments->first()->id;
        }
        
        // Get RFID cards for the selected apartment
        $cards = RfidCard::with(['activeTenantAssignment.tenantAssignment.tenant', 'apartment'])
                         ->forLandlord($user->id);
        
        if ($apartmentId) {
            $cards = $cards->forApartment($apartmentId);
        }
        
        $cards = $cards->orderBy('created_at', 'desc')->paginate(10);
        
        // Get recent access logs
        $recentLogs = AccessLog::with(['rfidCard', 'tenantAssignment.tenant', 'apartment'])
                              ->when($apartmentId, function($query) use ($apartmentId) {
                                  return $query->where('apartment_id', $apartmentId);
                              })
                              ->orderBy('access_time', 'desc')
                              ->limit(10)
                              ->get();
        
        // Get access statistics
        $stats = AccessLog::getAccessStats($apartmentId, 30);
        
        return view('landlord.security.index', compact(
            'cards', 'apartments', 'apartmentId', 'recentLogs', 'stats'
        ));
    }
    
    /**
     * Show form to assign a new RFID card
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $apartmentId = $request->get('apartment_id');
        
        // Get landlord's properties
        $apartments = $user->properties;
        
        // Get active tenant assignments for the apartment
        $tenantAssignments = TenantAssignment::with(['tenant', 'unit'])
                                           ->where('landlord_id', $user->id)
                                           ->when($apartmentId, function($query) use ($apartmentId) {
                                               return $query->whereHas('unit', function($q) use ($apartmentId) {
                                                   $q->where('apartment_id', $apartmentId);
                                               });
                                           })
                                           ->active()
                                           ->get();
        
        return view('landlord.security.create', compact(
            'apartments', 'apartmentId', 'tenantAssignments'
        ));
    }
    
    /**
     * Store a new RFID card assignment
     */
    public function store(Request $request)
    {
        $request->validate([
            'card_uid' => 'required|string|max:255|unique:rfid_cards',
            'tenant_assignment_id' => 'required|exists:tenant_assignments,id',
            'apartment_id' => 'required|exists:properties,id',
            'card_name' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $user = Auth::user();
        
        // Verify the tenant assignment belongs to this landlord
        $tenantAssignment = TenantAssignment::where('id', $request->tenant_assignment_id)
                                          ->where('landlord_id', $user->id)
                                          ->first();
        
        if (!$tenantAssignment) {
            return back()->withErrors(['tenant_assignment_id' => 'Invalid tenant assignment.']);
        }
        
        // Verify the property belongs to this landlord
        $apartment = $user->properties()->find($request->apartment_id);
        if (!$apartment) {
            return back()->withErrors(['apartment_id' => 'Invalid apartment.']);
        }
        
        try {
            DB::beginTransaction();
            
            // Create the RFID card
            $rfidCard = RfidCard::create([
                'card_uid' => strtoupper($request->card_uid),
                'landlord_id' => $user->id,
                'apartment_id' => $request->apartment_id,
                'card_name' => $request->card_name,
                'status' => 'active',
                'issued_at' => now(),
                'expires_at' => $request->expires_at,
                'notes' => $request->notes,
            ]);
            
            // Create the tenant assignment
            TenantRfidAssignment::create([
                'rfid_card_id' => $rfidCard->id,
                'tenant_assignment_id' => $request->tenant_assignment_id,
                'assigned_at' => now(),
                'expires_at' => $request->expires_at,
                'status' => 'active',
                'notes' => 'Initial assignment',
            ]);
            
            DB::commit();
            
            return redirect()->route('landlord.security', ['apartment_id' => $request->apartment_id])
                           ->with('success', 'RFID card assigned successfully!');
                           
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to assign RFID card: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show RFID card details
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $card = RfidCard::with(['activeTenantAssignment.tenantAssignment.tenant', 'apartment'])
                       ->where('landlord_id', $user->id)
                       ->findOrFail($id);
        
        // Get access logs for this card
        $accessLogs = AccessLog::with(['apartment'])
                             ->where('rfid_card_id', $id)
                             ->orderBy('access_time', 'desc')
                             ->paginate(15);
        
        return view('landlord.security.show', compact('card', 'accessLogs'));
    }
    
    /**
     * Show access logs
     */
    public function accessLogs(Request $request)
    {
        $user = Auth::user();
        $apartmentId = $request->get('apartment_id');
        $cardUid = $request->get('card_uid');
        $result = $request->get('result');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        // Get landlord's properties
        $apartments = $user->properties;
        
        // Build query
        $query = AccessLog::with(['rfidCard', 'tenantAssignment.tenant', 'apartment'])
                         ->whereIn('apartment_id', $apartments->pluck('id'));
        
        // Apply filters
        if ($apartmentId) {
            $query->where('apartment_id', $apartmentId);
        }
        
        if ($cardUid) {
            $query->where('card_uid', 'like', "%{$cardUid}%");
        }
        
        if ($result) {
            $query->where('access_result', $result);
        }
        
        if ($dateFrom) {
            $query->whereDate('access_time', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->whereDate('access_time', '<=', $dateTo);
        }
        
        $logs = $query->orderBy('access_time', 'desc')->paginate(20);
        
        // Get denied access reasons for stats
        $deniedReasons = AccessLog::getDeniedAccessReasons($apartmentId);
        
        return view('landlord.security.access-logs', compact(
            'logs', 'apartments', 'apartmentId', 'cardUid', 'result', 
            'dateFrom', 'dateTo', 'deniedReasons'
        ));
    }
    
    /**
     * Deactivate/reactivate RFID card
     */
    public function toggleStatus($id)
    {
        $user = Auth::user();
        
        $card = RfidCard::where('landlord_id', $user->id)->findOrFail($id);
        
        $newStatus = $card->status === 'active' ? 'inactive' : 'active';
        $card->update(['status' => $newStatus]);
        
        $action = $newStatus === 'active' ? 'activated' : 'deactivated';
        
        return back()->with('success', "RFID card {$action} successfully!");
    }
    
    /**
     * Show form to reassign an existing RFID card to a new tenant
     */
    public function reassignForm($id)
    {
        $user = Auth::user();
        
        $card = RfidCard::with(['activeTenantAssignment.tenantAssignment.tenant', 'apartment'])
                       ->where('landlord_id', $user->id)
                       ->findOrFail($id);
        
        // Get active tenant assignments for the same apartment (excluding current tenant if any)
        $tenantAssignments = TenantAssignment::with(['tenant', 'unit'])
                                           ->where('landlord_id', $user->id)
                                           ->whereHas('unit', function($q) use ($card) {
                                               $q->where('apartment_id', $card->apartment_id);
                                           })
                                           ->when($card->activeTenantAssignment, function($query) use ($card) {
                                               // Exclude the currently assigned tenant
                                               return $query->where('id', '!=', $card->activeTenantAssignment->tenant_assignment_id);
                                           })
                                           ->active()
                                           ->get();
        
        return view('landlord.security.reassign', compact('card', 'tenantAssignments'));
    }
    
    /**
     * Process the reassignment of an RFID card to a new tenant
     */
    public function reassign(Request $request, $id)
    {
        $request->validate([
            'tenant_assignment_id' => 'required|exists:tenant_assignments,id',
            'expires_at' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $user = Auth::user();
        
        $card = RfidCard::where('landlord_id', $user->id)->findOrFail($id);
        
        // Verify the tenant assignment belongs to this landlord and apartment
        $tenantAssignment = TenantAssignment::where('id', $request->tenant_assignment_id)
                                          ->where('landlord_id', $user->id)
                                          ->whereHas('unit', function($q) use ($card) {
                                              $q->where('apartment_id', $card->apartment_id);
                                          })
                                          ->first();
        
        if (!$tenantAssignment) {
            return back()->withErrors(['tenant_assignment_id' => 'Invalid tenant assignment.']);
        }
        
        try {
            DB::beginTransaction();
            
            // Deactivate any existing active assignments for this card
            TenantRfidAssignment::where('rfid_card_id', $card->id)
                              ->where('status', 'active')
                              ->update([
                                  'status' => 'inactive',
                                  'notes' => 'Reassigned to another tenant'
                              ]);
            
            // Create new assignment
            TenantRfidAssignment::create([
                'rfid_card_id' => $card->id,
                'tenant_assignment_id' => $request->tenant_assignment_id,
                'assigned_at' => now(),
                'expires_at' => $request->expires_at,
                'status' => 'active',
                'notes' => $request->notes ?: 'Card reassigned',
            ]);
            
            // Update card status and expiry
            $card->update([
                'status' => 'active',
                'expires_at' => $request->expires_at,
            ]);
            
            DB::commit();
            
            return redirect()->route('landlord.security', ['apartment_id' => $card->apartment_id])
                           ->with('success', 'RFID card reassigned successfully to ' . $tenantAssignment->tenant->name . '!');
                           
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to reassign RFID card: ' . $e->getMessage()]);
        }
    }
    
    /**
     * API endpoint for ESP32 to verify card access
     */
    public function verifyAccess(Request $request)
    {
        $cardUid = strtoupper($request->input('card_uid'));
        
        if (!$cardUid) {
            return response()->json(['error' => 'Card UID required'], 400);
        }
        
        $rfidCard = RfidCard::with(['activeTenantAssignment.tenantAssignment.tenant'])->where('card_uid', $cardUid)->first();
        
        $result = [
            'card_uid' => $cardUid,
            'access_granted' => false,
            'tenant_name' => null,
            'denial_reason' => null,
            'timestamp' => now()->toISOString()
        ];
        
        if (!$rfidCard) {
            $result['denial_reason'] = 'card_not_found';
        } elseif ($rfidCard->canGrantAccess()) {
            $result['access_granted'] = true;
            $result['tenant_name'] = $rfidCard->activeTenantAssignment->tenantAssignment->tenant->name;
        } else {
            $result['denial_reason'] = $rfidCard->getAccessDenialReason();
        }
        
        // Log the access attempt
        AccessLog::create([
            'card_uid' => $cardUid,
            'rfid_card_id' => $rfidCard?->id,
            'tenant_assignment_id' => $rfidCard?->activeTenantAssignment?->tenant_assignment_id,
            'apartment_id' => $rfidCard?->apartment_id,
            'access_result' => $result['access_granted'] ? 'granted' : 'denied',
            'denial_reason' => $result['denial_reason'],
            'access_time' => now(),
            'reader_location' => $request->input('reader_location', 'main_entrance'),
            'raw_data' => $request->all()
        ]);
        
        return response()->json($result);
    }
    
    /**
     * API endpoint to trigger card scanning and return UID directly
     */
    public function scanCard(Request $request)
    {
        $timeout = $request->input('timeout', 15); // Default 15 seconds timeout
        
        try {
            // Create a unique scan request
            $scanId = 'temp_scan_' . uniqid();
            $tempFile = storage_path('app/' . $scanId . '.json');
            
            // Store the scan request
            $scanRequest = [
                'scan_id' => $scanId,
                'requested_at' => now()->toISOString(),
                'timeout' => $timeout,
                'status' => 'waiting',
                'card_uid' => null,
                'error' => null,
                'direct_mode' => true // Flag for direct Card UID extraction
            ];
            
            file_put_contents($tempFile, json_encode($scanRequest));
            
            return response()->json([
                'success' => true,
                'scan_id' => $scanId,
                'message' => 'Ready to scan. Please tap your RFID card now.',
                'timeout' => $timeout,
                'direct_mode' => true
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to initiate scan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API endpoint for receiving RFID data from ESP32Reader.php
     */
    public function scanCardDirect(Request $request)
    {
        try {
            $cardUID = strtoupper($request->input('cardUID'));
            $timestamp = $request->input('timestamp');
            $readerLocation = $request->input('reader_location', 'main_entrance');
            $deviceId = $request->input('device_id', 'esp32_reader');
            $scanType = $request->input('scan_type', 'access_attempt'); // 'access_attempt' or 'card_registration'
            
            if (!$cardUID) {
                return response()->json([
                    'success' => false,
                    'error' => 'Card UID is required'
                ], 400);
            }
            
            // Determine entry state (IN/OUT) for single-scanner toggle
            // Rule: If last scan for this card was IN, next is OUT; otherwise IN
            $lastLog = \App\Models\AccessLog::where('card_uid', $cardUID)
                ->orderBy('access_time', 'desc')
                ->first();
            $lastEntryState = null;
            if ($lastLog) {
                // Try to read from explicit field first, then from raw_data JSON
                $lastEntryState = $lastLog->entry_state ?? null;
                if (!$lastEntryState && is_array($lastLog->raw_data ?? null)) {
                    $lastEntryState = $lastLog->raw_data['entry_state'] ?? null;
                }
            }
            $entryState = ($lastEntryState === 'in') ? 'out' : 'in';

            // Process the card
            $rfidCard = RfidCard::with(['activeTenantAssignment.tenantAssignment.tenant'])->where('card_uid', $cardUID)->first();
            
            $result = [
                'success' => true,
                'card_uid' => $cardUID,
                'message' => 'Card UID received successfully!',
                'timestamp' => $timestamp ?: now()->toISOString(),
                'scan_type' => $scanType,
                'entry_state' => $entryState
            ];
            
            if (!$rfidCard) {
                // New card - not in database yet
                $result['card_status'] = 'new_card';
                $result['message'] = 'New card detected - ready for assignment';
                 
                // Only log as access attempt if it's actually an access attempt
                if ($scanType === 'access_attempt') {
                    $result['access_granted'] = false;
                    $result['denial_reason'] = 'card_not_found';
                    
                    // Log the access attempt
                    AccessLog::create([
                        'card_uid' => $cardUID,
                        'rfid_card_id' => null,
                        'tenant_assignment_id' => null,
                        'apartment_id' => null,
                        'access_result' => 'denied',
                        'denial_reason' => 'card_not_found',
                        'access_time' => now(),
                        'reader_location' => $readerLocation,
                        'raw_data' => array_merge($request->all(), [
                            'entry_state' => $entryState,
                            'device_id' => $deviceId
                        ])
                    ]);
                }
            } else {
                // Existing card - check access
                $result['card_status'] = 'registered_card';
                
                if ($rfidCard->canGrantAccess()) {
                    $result['access_granted'] = true;
                    $result['tenant_name'] = $rfidCard->activeTenantAssignment->tenantAssignment->tenant->name;
                    $result['message'] = 'Access granted (' . strtoupper($entryState) . ')';
                } else {
                    $result['access_granted'] = false;
                    $result['denial_reason'] = $rfidCard->getAccessDenialReason();
                    $result['message'] = 'Access denied: ' . $result['denial_reason'];
                    
                    // Ensure we have a denial reason for deactivated cards
                    if (!$result['denial_reason'] && $rfidCard->status !== 'active') {
                        $result['denial_reason'] = 'card_inactive';
                    }
                }
                
                // Always log for registered cards (both access attempts and card registration)
                AccessLog::create([
                    'card_uid' => $cardUID,
                    'rfid_card_id' => $rfidCard->id,
                    'tenant_assignment_id' => $rfidCard->activeTenantAssignment?->tenant_assignment_id,
                    'apartment_id' => $rfidCard->apartment_id,
                    'access_result' => $result['access_granted'] ? 'granted' : 'denied',
                    'denial_reason' => $result['denial_reason'] ?? null,
                    'access_time' => now(),
                    'reader_location' => $readerLocation,
                    'raw_data' => array_merge($request->all(), [
                        'entry_state' => $entryState,
                        'device_id' => $deviceId,
                        'scan_type' => $scanType
                    ])
                ]);
            }
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test endpoint for ESP32Reader.php connection
     */
    public function testConnection(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Laravel API is responding',
            'timestamp' => now()->toISOString(),
            'endpoint' => '/api/rfid/scan/direct'
        ]);
    }

    /**
     * Get the latest Card UID from access_logs database
     */
    public function getLatestCardUID(Request $request)
    {
        try {
            // Get the most recent access log entry
            $latestLog = AccessLog::orderBy('access_time', 'desc')->first();
            
            if (!$latestLog) {
                return response()->json([
                    'success' => false,
                    'error' => 'No card has been scanned yet. Please tap a card on the ESP32 reader first.',
                    'instructions' => 'Make sure ESP32Reader.php is running and tap an RFID card.'
                ], 404);
            }
            
            // Check if the card data is recent (within last 10 minutes for better UX)
            $age = now()->diffInSeconds($latestLog->access_time);
            
            // Allow cards scanned within the last 10 minutes (600 seconds)
            if ($age > 600) {
                return response()->json([
                    'success' => false,
                    'error' => 'Last scanned card is too old. Please tap a new card on the ESP32 reader.',
                    'last_scan' => $latestLog->access_time->toISOString(),
                    'age_seconds' => $age
                ], 410);
            }
            
            return response()->json([
                'success' => true,
                'card_uid' => $latestLog->card_uid,
                'message' => 'Latest Card UID retrieved successfully',
                'scanned_at' => $latestLog->access_time->toISOString(),
                'age_seconds' => $age
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get latest Card UID: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return recent access logs (JSON) for dynamic UI refresh
     */
    public function recentLogsJson(Request $request)
    {
        $apartmentId = $request->get('apartment_id');
        $limit = (int)($request->get('limit', 10));
        $limit = max(1, min(50, $limit));

        $logs = \App\Models\AccessLog::with(['rfidCard', 'tenantAssignment.tenant', 'apartment'])
            ->when($apartmentId, fn($q) => $q->where('apartment_id', $apartmentId))
            ->orderBy('access_time', 'desc')
            ->limit($limit)
            ->get();

        $data = $logs->map(function($log){
            return [
                'id' => $log->id,
                'access_time' => $log->access_time?->toIso8601String(),
                'access_time_human' => $log->access_time?->format('M j, g:i A'),
                'card_uid' => $log->card_uid,
                'tenant_name' => $log->tenant_name,
                'result_badge_class' => $log->display_badge_class ?? $log->result_badge_class,
                'result_text' => $log->display_result ?? ucfirst($log->access_result),
                'denial_reason' => $log->denial_reason_display,
            ];
        });

        return response()->json([
            'success' => true,
            'logs' => $data,
        ]);
    }

    /**
     * Simple Card UID generator for testing
     */
    public function generateCardUID(Request $request)
    {
        try {
            // Generate a random 8-character hex UID for testing
            $cardUID = strtoupper(substr(md5(uniqid()), 0, 8));
            
            return response()->json([
                'success' => true,
                'card_uid' => $cardUID,
                'message' => 'Card UID generated successfully (TEST MODE)',
                'timestamp' => now()->toISOString(),
                'test_mode' => true
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate Card UID: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Card UID directly from ESP32Reader.php
     * This method communicates with ESP32Reader.php to get a Card UID on demand
     */
    public function getCardUIDFromESP32Reader(Request $request)
    {
        try {
            $timeout = $request->input('timeout', 15);
            $comPort = $request->input('com_port', 'COM3');
            
            // Create a scan request file that ESP32Reader.php will monitor
            $scanId = 'web_scan_' . uniqid();
            $requestFile = storage_path('app/scan_requests/' . $scanId . '.json');
            
            // Ensure directory exists
            $requestDir = dirname($requestFile);
            if (!is_dir($requestDir)) {
                mkdir($requestDir, 0755, true);
            }
            
            // Create scan request
            $scanRequest = [
                'scan_id' => $scanId,
                'type' => 'web_request',
                'com_port' => $comPort,
                'timeout' => $timeout,
                'requested_at' => now()->toISOString(),
                'status' => 'pending',
                'card_uid' => null,
                'error' => null
            ];
            
            file_put_contents($requestFile, json_encode($scanRequest, JSON_PRETTY_PRINT));
            
            return response()->json([
                'success' => true,
                'scan_id' => $scanId,
                'message' => 'Scan request created. ESP32Reader.php will process it.',
                'timeout' => $timeout
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create scan request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check scan request status from ESP32Reader.php
     */
    public function checkScanRequestStatus($scanId)
    {
        try {
            $requestFile = storage_path('app/scan_requests/' . $scanId . '.json');
            
            if (!file_exists($requestFile)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Scan request not found or expired'
                ], 404);
            }
            
            $scanData = json_decode(file_get_contents($requestFile), true);
            
            if (!$scanData) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid scan data'
                ], 500);
            }
            
            // Check if timed out
            $requestedAt = \Carbon\Carbon::parse($scanData['requested_at']);
            $timeout = $scanData['timeout'];
            
            if ($requestedAt->addSeconds($timeout)->isPast()) {
                // Clean up expired file
                unlink($requestFile);
                return response()->json([
                    'success' => false,
                    'status' => 'timeout',
                    'error' => 'Scan request timed out'
                ]);
            }
            
            // Calculate remaining time
            $remaining = max(0, $requestedAt->addSeconds($timeout)->diffInSeconds(now()));
            
            return response()->json([
                'success' => true,
                'status' => $scanData['status'],
                'card_uid' => $scanData['card_uid'],
                'error' => $scanData['error'],
                'remaining_time' => $remaining
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Status check failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request immediate Card UID scan from ESP32Reader.php
     */
    public function requestCardScan(Request $request)
    {
        try {
            // Create a scan request file that ESP32Reader.php will detect
            $scanId = 'scan_request_' . uniqid();
            $tempFile = storage_path('app/' . $scanId . '.json');
            
            $scanRequest = [
                'scan_id' => $scanId,
                'requested_at' => now()->toISOString(),
                'timeout' => 15,
                'status' => 'waiting',
                'card_uid' => null,
                'error' => null,
                'request_type' => 'web_interface'
            ];
            
            file_put_contents($tempFile, json_encode($scanRequest));
            
            return response()->json([
                'success' => true,
                'scan_id' => $scanId,
                'message' => 'Scan request created. Please tap your RFID card now.',
                'timeout' => 15
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create scan request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    
    /**
     * Check the status of a card scan request
     */
    public function scanStatus($scanId)
    {
        $tempFile = storage_path('app/' . $scanId . '.json');
        
        if (!file_exists($tempFile)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid scan ID or scan expired'
            ], 404);
        }
        
        $scanData = json_decode(file_get_contents($tempFile), true);
        $requestedAt = \Carbon\Carbon::parse($scanData['requested_at']);
        
        // Check if scan has timed out
        if ($requestedAt->addSeconds($scanData['timeout'])->isPast()) {
            unlink($tempFile); // Clean up
            return response()->json([
                'success' => false,
                'status' => 'timeout',
                'error' => 'Scan request timed out'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'status' => $scanData['status'],
            'card_uid' => $scanData['card_uid'],
            'error' => $scanData['error'],
            'remaining_time' => max(0, $requestedAt->addSeconds($scanData['timeout'])->diffInSeconds(now()))
        ]);
    }
    
    /**
     * Test endpoint to verify deactivated card behavior
     */
    public function testDeactivatedCard(Request $request)
    {
        $cardUid = strtoupper($request->input('card_uid'));
        
        if (!$cardUid) {
            return response()->json(['error' => 'Card UID required'], 400);
        }
        
        $rfidCard = RfidCard::with(['activeTenantAssignment.tenantAssignment.tenant'])->where('card_uid', $cardUid)->first();
        
        if (!$rfidCard) {
            return response()->json(['error' => 'Card not found'], 404);
        }
        
        $debug = [
            'card_uid' => $rfidCard->card_uid,
            'card_status' => $rfidCard->status,
            'card_isActive' => $rfidCard->isActive(),
            'card_isExpired' => $rfidCard->isExpired(),
            'card_isCompromised' => $rfidCard->isCompromised(),
            'activeTenantAssignment_exists' => $rfidCard->activeTenantAssignment ? true : false,
            'canGrantAccess' => $rfidCard->canGrantAccess(),
            'accessDenialReason' => $rfidCard->getAccessDenialReason(),
        ];
        
        if ($rfidCard->activeTenantAssignment) {
            $debug['activeTenantAssignment'] = [
                'id' => $rfidCard->activeTenantAssignment->id,
                'status' => $rfidCard->activeTenantAssignment->status,
                'tenant_assignment_id' => $rfidCard->activeTenantAssignment->tenant_assignment_id,
                'canGrantAccess' => $rfidCard->activeTenantAssignment->canGrantAccess(),
            ];
        }
        
        // Test what would be logged
        $wouldLog = [
            'access_result' => $rfidCard->canGrantAccess() ? 'granted' : 'denied',
            'denial_reason' => $rfidCard->canGrantAccess() ? null : $rfidCard->getAccessDenialReason(),
        ];
        
        return response()->json([
            'debug' => $debug,
            'would_log' => $wouldLog,
            'message' => 'Debug information for card: ' . $cardUid
        ]);
    }

    /**
     * Update scan status (called by ESP32 bridge)
     */
    public function updateScanStatus(Request $request)
    {
        $scanId = $request->input('scan_id');
        $cardUid = $request->input('card_uid');
        $error = $request->input('error');
        
        $tempFile = storage_path('app/' . $scanId . '.json');
        
        if (!file_exists($tempFile)) {
            return response()->json(['error' => 'Invalid scan ID'], 404);
        }
        
        $scanData = json_decode(file_get_contents($tempFile), true);
        
        if ($cardUid) {
            $scanData['status'] = 'completed';
            $scanData['card_uid'] = strtoupper($cardUid);
        } else if ($error) {
            $scanData['status'] = 'error';
            $scanData['error'] = $error;
        }
        
        $scanData['completed_at'] = now()->toISOString();
        
        file_put_contents($tempFile, json_encode($scanData));
        
        // Clean up file after 60 seconds
        dispatch(function() use ($tempFile) {
            sleep(60);
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        })->delay(now()->addMinutes(1));
        
        return response()->json(['success' => true]);
    }
}
