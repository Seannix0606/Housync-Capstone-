<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use App\Models\TenantAssignment;
use App\Events\MessageSent;
use App\Events\ConversationUpdated;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Display chat inbox for landlord
     */
    public function landlordIndex()
    {
        $user = Auth::user();
        
        $conversations = Conversation::forUser($user->id)
            ->with(['latestMessage', 'users', 'apartment', 'unit'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);
        
        $totalUnread = ConversationParticipant::where('user_id', $user->id)
            ->sum('unread_count');
        
        return view('landlord.chat.index', compact('conversations', 'totalUnread'));
    }

    /**
     * Display chat inbox for tenant
     */
    public function tenantIndex()
    {
        $user = Auth::user();
        
        $conversations = Conversation::forUser($user->id)
            ->with(['latestMessage', 'users', 'apartment', 'unit'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);
        
        $totalUnread = ConversationParticipant::where('user_id', $user->id)
            ->sum('unread_count');
        
        return view('tenant.chat.index', compact('conversations', 'totalUnread'));
    }

    /**
     * Display chat inbox for staff
     */
    public function staffIndex()
    {
        $user = Auth::user();
        
        $conversations = Conversation::forUser($user->id)
            ->with(['latestMessage', 'users', 'apartment', 'unit'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);
        
        $totalUnread = ConversationParticipant::where('user_id', $user->id)
            ->sum('unread_count');
        
        return view('staff.chat.index', compact('conversations', 'totalUnread'));
    }

    /**
     * Show a specific conversation
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $conversation = Conversation::with(['messages.sender', 'messages.attachments', 'users', 'apartment', 'unit'])
            ->forUser($user->id)
            ->findOrFail($id);
        
        // Mark conversation as read
        $conversation->markAsReadFor($user->id);
        
        // Get other participants for display
        $otherParticipants = $conversation->users->filter(fn($u) => $u->id !== $user->id);
        
        // Determine view based on user role
        $viewPrefix = match($user->role) {
            'landlord' => 'landlord',
            'tenant' => 'tenant',
            'staff' => 'staff',
            default => 'landlord',
        };
        
        return view("{$viewPrefix}.chat.show", compact('conversation', 'otherParticipants'));
    }

    /**
     * Start a new conversation with a tenant (for landlords)
     */
    public function startWithTenant(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:users,id',
            'apartment_id' => 'nullable|exists:apartments,id',
            'message' => 'nullable|string|max:5000',
        ]);

        $landlord = Auth::user();
        $tenantId = $request->tenant_id;

        // Verify tenant belongs to landlord
        $validTenant = TenantAssignment::where('landlord_id', $landlord->id)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$validTenant) {
            return back()->with('error', 'You can only message your own tenants.');
        }

        $conversation = Conversation::getOrCreateDirect(
            $landlord->id, 
            $tenantId, 
            $request->apartment_id
        );

        // Send initial message if provided
        if ($request->filled('message')) {
            $this->createMessage($conversation, $landlord->id, $request->message);
        }

        return redirect()->route('landlord.chat.show', $conversation->id);
    }

    /**
     * Start a new conversation with landlord (for tenants)
     */
    public function startWithLandlord(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:5000',
        ]);

        $tenant = Auth::user();

        // Get tenant's active assignment
        $assignment = TenantAssignment::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with('unit.apartment')
            ->first();

        if (!$assignment) {
            return back()->with('error', 'You need an active lease to contact your landlord.');
        }

        $conversation = Conversation::getOrCreateDirect(
            $tenant->id,
            $assignment->landlord_id,
            $assignment->unit->apartment_id
        );

        // Send initial message if provided
        if ($request->filled('message')) {
            $this->createMessage($conversation, $tenant->id, $request->message);
        }

        return redirect()->route('tenant.chat.show', $conversation->id);
    }

    /**
     * Create a maintenance ticket
     */
    public function createTicket(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'priority' => 'required|in:low,normal,high,urgent',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx',
        ]);

        $tenant = Auth::user();

        // Get tenant's active assignment
        $assignment = TenantAssignment::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with('unit.apartment')
            ->first();

        if (!$assignment) {
            return back()->with('error', 'You need an active lease to submit a maintenance request.');
        }

        try {
            DB::beginTransaction();

            $conversation = Conversation::createMaintenanceTicket(
                $request->subject,
                $assignment->unit_id,
                $tenant->id,
                $assignment->landlord_id,
                $request->priority
            );

            // Create the initial message with description
            $message = $this->createMessage(
                $conversation, 
                $tenant->id, 
                $request->description,
                'text',
                $request->file('attachments')
            );

            DB::commit();

            return redirect()->route('tenant.chat.show', $conversation->id)
                ->with('success', 'Maintenance ticket created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create maintenance ticket', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenant->id,
            ]);
            return back()->with('error', 'Failed to create ticket. Please try again.');
        }
    }

    /**
     * Send a message in a conversation
     */
    public function sendMessage(Request $request, $conversationId)
    {
        try {
            $request->validate([
                'content' => 'required_without:attachments|string|max:5000',
                'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all()),
            ], 422);
        }

        $user = Auth::user();
        
        try {
            $conversation = Conversation::forUser($user->id)->findOrFail($conversationId);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found or you do not have access.',
            ], 404);
        }

        try {
            $message = $this->createMessage(
                $conversation,
                $user->id,
                $request->input('content', ''),
                'text',
                $request->file('attachments')
            );

            // Always return JSON for this endpoint (used by AJAX)
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'type' => $message->type,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender_name,
                    'sender_avatar' => $message->sender_avatar,
                    'formatted_time' => $message->formatted_time,
                    'created_at' => $message->created_at->toISOString(),
                    'attachments' => $message->attachments->map(fn($a) => [
                        'id' => $a->id,
                        'file_name' => $a->file_name,
                        'file_url' => $a->file_url,
                        'file_type' => $a->file_type,
                        'formatted_size' => $a->formatted_size,
                        'is_image' => $a->is_image,
                    ]),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'conversation_id' => $conversationId,
                'user_id' => $user->id,
                'content' => $request->input('content'),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                $errorMessage = config('app.debug') 
                    ? 'Failed to send message: ' . $e->getMessage()
                    : 'Failed to send message. Please try again.';
                    
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                ], 500);
            }

            return back()->with('error', 'Failed to send message. Please try again.');
        }
    }

    /**
     * Get messages for a conversation (AJAX/polling endpoint)
     */
    public function getMessages(Request $request, $conversationId)
    {
        $user = Auth::user();
        $lastMessageId = $request->input('last_message_id', 0);
        
        $conversation = Conversation::forUser($user->id)->findOrFail($conversationId);

        $messages = $conversation->messages()
            ->with(['sender', 'attachments'])
            ->where('id', '>', $lastMessageId)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read
        $conversation->markAsReadFor($user->id);

        return response()->json([
            'success' => true,
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'content' => $m->content,
                'type' => $m->type,
                'sender_id' => $m->sender_id,
                'sender_name' => $m->sender_name,
                'sender_avatar' => $m->sender_avatar,
                'formatted_time' => $m->formatted_time,
                'created_at' => $m->created_at->toISOString(),
                'is_mine' => $m->sender_id === $user->id,
                'attachments' => $m->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'file_name' => $a->file_name,
                    'file_url' => $a->file_url,
                    'file_type' => $a->file_type,
                    'formatted_size' => $a->formatted_size,
                    'is_image' => $a->is_image,
                ]),
            ]),
        ]);
    }

    /**
     * Get conversation list (AJAX endpoint for real-time updates)
     */
    public function getConversations(Request $request)
    {
        $user = Auth::user();
        
        $conversations = Conversation::forUser($user->id)
            ->with(['latestMessage', 'users'])
            ->orderBy('last_message_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'conversations' => $conversations->map(function($c) use ($user) {
                $other = $c->getOtherParticipant($user->id);
                return [
                    'id' => $c->id,
                    'type' => $c->type,
                    'subject' => $c->subject,
                    'status' => $c->status,
                    'priority' => $c->priority,
                    'unread_count' => $c->getUnreadCountFor($user->id),
                    'other_participant' => $other ? [
                        'id' => $other->id,
                        'name' => $other->name,
                        'avatar' => strtoupper(substr($other->name ?? 'U', 0, 1)),
                    ] : null,
                    'last_message' => $c->latestMessage ? [
                        'content' => $c->latestMessage->content,
                        'sender_name' => $c->latestMessage->sender_name,
                        'formatted_time' => $c->latestMessage->formatted_time,
                    ] : null,
                ];
            }),
            'total_unread' => ConversationParticipant::where('user_id', $user->id)->sum('unread_count'),
        ]);
    }

    /**
     * Get unread count for current user
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        
        $count = ConversationParticipant::where('user_id', $user->id)
            ->sum('unread_count');

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }

    /**
     * Mark a conversation as read
     */
    public function markAsRead($conversationId)
    {
        $user = Auth::user();
        
        $conversation = Conversation::forUser($user->id)->findOrFail($conversationId);
        $conversation->markAsReadFor($user->id);

        return response()->json(['success' => true]);
    }

    /**
     * Update ticket status (for landlords/staff)
     */
    public function updateTicketStatus(Request $request, $conversationId)
    {
        $request->validate([
            'status' => 'required|in:active,resolved,archived',
        ]);

        $user = Auth::user();
        
        $conversation = Conversation::forUser($user->id)
            ->where('type', 'maintenance_ticket')
            ->findOrFail($conversationId);

        $oldStatus = $conversation->status;
        $conversation->update(['status' => $request->status]);

        // Add system message about status change
        Message::createSystemMessage(
            $conversation->id,
            "Ticket status changed from {$oldStatus} to {$request->status}",
            ['action' => 'status_change', 'old_status' => $oldStatus, 'new_status' => $request->status]
        );

        return response()->json([
            'success' => true,
            'message' => 'Ticket status updated.',
        ]);
    }

    /**
     * Get list of tenants for landlord to start conversation
     */
    public function getTenantsList()
    {
        $user = Auth::user();
        
        $tenants = TenantAssignment::where('landlord_id', $user->id)
            ->where('status', 'active')
            ->with(['tenant', 'unit.apartment'])
            ->get()
            ->map(fn($a) => [
                'id' => $a->tenant->id,
                'name' => $a->tenant->name,
                'email' => $a->tenant->email,
                'unit' => $a->unit->unit_number,
                'property' => $a->unit->apartment->name,
            ]);

        return response()->json([
            'success' => true,
            'tenants' => $tenants,
        ]);
    }

    /**
     * Create a message with optional attachments
     */
    private function createMessage(Conversation $conversation, int $senderId, string $content, string $type = 'text', $attachments = null): Message
    {
        DB::beginTransaction();
        
        try {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $senderId,
                'content' => $content,
                'type' => $type,
            ]);

            // Handle attachments
            if ($attachments && is_array($attachments)) {
                foreach ($attachments as $file) {
                    $this->uploadAttachment($message, $file);
                }
            } elseif ($attachments) {
                $this->uploadAttachment($message, $attachments);
            }

            // Update conversation
            $conversation->update(['last_message_at' => now()]);
            
            // Increment unread count for other participants
            $conversation->incrementUnreadFor($senderId);

            DB::commit();

            // Skip real-time broadcasting for local development
            // The chat still works via polling - messages are saved and fetched periodically
            // To enable real-time: composer require pusher/pusher-php-server
            // Then set BROADCAST_DRIVER=pusher in .env with valid Pusher keys

            return $message->load('attachments');

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Upload attachment to storage
     */
    private function uploadAttachment(Message $message, $file): MessageAttachment
    {
        $useSupabase = config('app.env') !== 'local' || config('services.supabase.key');
        
        $fileName = 'chat-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = 'chat-attachments/' . $fileName;
        
        $fileType = $this->determineFileType($file->getMimeType());
        
        if ($useSupabase) {
            $supabase = new SupabaseService();
            $uploadResult = $supabase->uploadFile('house-sync', $path, $file->getRealPath());
            
            if (!$uploadResult['success']) {
                throw new \Exception('Failed to upload attachment: ' . ($uploadResult['message'] ?? 'Unknown error'));
            }
            
            $filePath = $uploadResult['url'];
        } else {
            $filePath = $file->storeAs('chat-attachments', $fileName, 'public');
        }

        return MessageAttachment::create([
            'message_id' => $message->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $fileType,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    /**
     * Determine file type from mime type
     */
    private function determineFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif ($mimeType === 'application/pdf') {
            return 'document';
        } elseif (str_contains($mimeType, 'word') || str_contains($mimeType, 'document')) {
            return 'document';
        } else {
            return 'file';
        }
    }
}


