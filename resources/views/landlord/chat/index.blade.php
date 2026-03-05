@extends('layouts.landlord-app')

@section('title', 'Messages')

@push('styles')
<style>
    .chat-container {
        display: flex;
        height: calc(100vh - 180px);
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    /* Sidebar - Conversation List */
    .chat-sidebar {
        width: 340px;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        background: #f8fafc;
    }
    
    .chat-sidebar-header {
        padding: 20px;
        border-bottom: 1px solid #e2e8f0;
        background: #fff;
    }
    
    .chat-sidebar-header h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 12px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .unread-badge {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 999px;
        font-weight: 600;
    }
    
    .new-chat-btn {
        width: 100%;
        padding: 10px 16px;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .new-chat-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
    }
    
    .conversation-list {
        flex: 1;
        overflow-y: auto;
        padding: 12px;
    }
    
    .conversation-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 6px;
        position: relative;
    }
    
    .conversation-item:hover {
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    
    .conversation-item.active {
        background: #fff;
        box-shadow: 0 2px 12px rgba(249, 115, 22, 0.15);
        border-left: 3px solid #f97316;
    }
    
    .conversation-item.unread {
        background: #fff7ed;
    }
    
    .conversation-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    
    .conversation-avatar.ticket {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }
    
    .conversation-info {
        flex: 1;
        min-width: 0;
    }
    
    .conversation-name {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 2px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .conversation-preview {
        font-size: 0.85rem;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .conversation-meta {
        text-align: right;
        flex-shrink: 0;
    }
    
    .conversation-time {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-bottom: 4px;
    }
    
    .unread-count {
        background: #f97316;
        color: #fff;
        font-size: 0.7rem;
        padding: 2px 7px;
        border-radius: 999px;
        font-weight: 600;
    }
    
    .priority-badge {
        font-size: 0.65rem;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .priority-urgent { background: #fee2e2; color: #dc2626; }
    .priority-high { background: #fef3c7; color: #d97706; }
    .priority-normal { background: #dbeafe; color: #2563eb; }
    .priority-low { background: #d1fae5; color: #059669; }
    
    /* Empty State */
    .chat-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px;
        text-align: center;
        background: #f8fafc;
    }
    
    .chat-empty-icon {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
    }
    
    .chat-empty-icon i {
        font-size: 48px;
        color: #fff;
    }
    
    .chat-empty h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }
    
    .chat-empty p {
        color: #64748b;
        max-width: 300px;
        margin-bottom: 20px;
    }
    
    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }
    
    .modal-overlay.show {
        opacity: 1;
        visibility: visible;
    }
    
    .modal-content {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 500px;
        max-height: 80vh;
        overflow: hidden;
        transform: scale(0.9);
        transition: transform 0.3s;
    }
    
    .modal-overlay.show .modal-content {
        transform: scale(1);
    }
    
    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .modal-header h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #64748b;
        cursor: pointer;
        padding: 4px;
    }
    
    .modal-body {
        padding: 24px;
        max-height: 60vh;
        overflow-y: auto;
    }
    
    .tenant-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .tenant-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .tenant-item:hover {
        border-color: #f97316;
        background: #fff7ed;
    }
    
    .tenant-item .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    
    .tenant-item .info h4 {
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }
    
    .tenant-item .info p {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
    }

    @media (max-width: 768px) {
        .chat-container {
            height: calc(100vh - 140px);
        }
        .chat-sidebar {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="content-header">
    <h1><i class="fas fa-comments" style="color: #f97316; margin-right: 10px;"></i>Messages</h1>
</div>

<div class="chat-container">
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <h2>
                Conversations
                @if($totalUnread > 0)
                    <span class="unread-badge">{{ $totalUnread }}</span>
                @endif
            </h2>
            <button class="new-chat-btn" onclick="openNewChatModal()">
                <i class="fas fa-plus"></i> New Conversation
            </button>
        </div>
        
        <div class="conversation-list" id="conversationList">
            @forelse($conversations as $conversation)
                @php
                    $other = $conversation->getOtherParticipant(auth()->id());
                    $unread = $conversation->getUnreadCountFor(auth()->id());
                @endphp
                <a href="{{ route('landlord.chat.show', $conversation->id) }}" 
                   class="conversation-item {{ $unread > 0 ? 'unread' : '' }}" 
                   data-id="{{ $conversation->id }}">
                    <div class="conversation-avatar {{ $conversation->type === 'maintenance_ticket' ? 'ticket' : '' }}">
                        @if($conversation->type === 'maintenance_ticket')
                            <i class="fas fa-wrench"></i>
                        @else
                            {{ $other ? strtoupper(substr($other->name, 0, 1)) : '?' }}
                        @endif
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-name">
                            @if($conversation->type === 'maintenance_ticket')
                                {{ $conversation->subject ?? 'Maintenance Request' }}
                                <span class="priority-badge priority-{{ $conversation->priority }}">{{ $conversation->priority }}</span>
                            @else
                                {{ $other?->name ?? 'Unknown' }}
                            @endif
                        </div>
                        <div class="conversation-preview">
                            @if($conversation->latestMessage)
                                {{ Str::limit($conversation->latestMessage->content, 40) }}
                            @else
                                No messages yet
                            @endif
                        </div>
                    </div>
                    <div class="conversation-meta">
                        <div class="conversation-time">
                            {{ $conversation->last_message_at?->diffForHumans(null, true) ?? 'New' }}
                        </div>
                        @if($unread > 0)
                            <span class="unread-count">{{ $unread }}</span>
                        @endif
                    </div>
                </a>
            @empty
                <div style="padding: 40px; text-align: center; color: #64748b;">
                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <p>No conversations yet</p>
                </div>
            @endforelse
        </div>
    </div>
    
    <div class="chat-empty">
        <div class="chat-empty-icon">
            <i class="fas fa-comments"></i>
        </div>
        <h3>Select a Conversation</h3>
        <p>Choose a conversation from the list or start a new one to begin messaging.</p>
        <button class="new-chat-btn" onclick="openNewChatModal()">
            <i class="fas fa-plus"></i> Start New Conversation
        </button>
    </div>
</div>

<!-- New Chat Modal -->
<div class="modal-overlay" id="newChatModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Start New Conversation</h3>
            <button class="modal-close" onclick="closeNewChatModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="color: #64748b; margin-bottom: 16px;">Select a tenant to message:</p>
            <div class="tenant-list" id="tenantList">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-spinner fa-spin"></i> Loading tenants...
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openNewChatModal() {
        document.getElementById('newChatModal').classList.add('show');
        loadTenants();
    }
    
    function closeNewChatModal() {
        document.getElementById('newChatModal').classList.remove('show');
    }
    
    async function loadTenants() {
        try {
            const response = await fetch('{{ route("landlord.chat.tenants-list") }}');
            const data = await response.json();
            
            const tenantList = document.getElementById('tenantList');
            
            if (data.tenants.length === 0) {
                tenantList.innerHTML = `
                    <div style="text-align: center; padding: 20px; color: #64748b;">
                        <i class="fas fa-users" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i>
                        <p>No active tenants found</p>
                    </div>
                `;
                return;
            }
            
            tenantList.innerHTML = data.tenants.map(tenant => `
                <form action="{{ route('landlord.chat.start-with-tenant') }}" method="POST" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="tenant_id" value="${tenant.id}">
                    <button type="submit" class="tenant-item" style="width: 100%; background: none; text-align: left;">
                        <div class="avatar">${tenant.name.charAt(0).toUpperCase()}</div>
                        <div class="info">
                            <h4>${tenant.name}</h4>
                            <p>${tenant.property} - Unit ${tenant.unit}</p>
                        </div>
                        <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                    </button>
                </form>
            `).join('');
        } catch (error) {
            console.error('Error loading tenants:', error);
            document.getElementById('tenantList').innerHTML = `
                <div style="text-align: center; padding: 20px; color: #dc2626;">
                    <i class="fas fa-exclamation-circle"></i> Failed to load tenants
                </div>
            `;
        }
    }
    
    // Close modal on overlay click
    document.getElementById('newChatModal').addEventListener('click', function(e) {
        if (e.target === this) closeNewChatModal();
    });
    
    // Poll for new messages
    setInterval(async () => {
        try {
            const response = await fetch('{{ route("landlord.chat.unread-count") }}');
            const data = await response.json();
            // Update unread badge if needed
        } catch (error) {
            console.error('Error checking unread count:', error);
        }
    }, 30000); // Every 30 seconds
</script>
@endpush



