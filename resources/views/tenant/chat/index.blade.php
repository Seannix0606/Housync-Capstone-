@extends('layouts.tenant-app')

@section('title', 'Messages')

@push('styles')
<style>
    .chat-page {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .chat-header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    
    .chat-header-section h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .unread-badge {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        font-size: 0.8rem;
        padding: 4px 12px;
        border-radius: 999px;
        font-weight: 600;
    }
    
    .chat-actions {
        display: flex;
        gap: 12px;
    }
    
    .action-btn {
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        text-decoration: none;
    }
    
    .action-btn.primary {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        border: none;
    }
    
    .action-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
    }
    
    .action-btn.secondary {
        background: #fff;
        color: #1e293b;
        border: 2px solid #e2e8f0;
    }
    
    .action-btn.secondary:hover {
        border-color: #f97316;
        color: #f97316;
    }
    
    .conversation-list {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    
    .conversation-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    
    .conversation-item:last-child {
        border-bottom: none;
    }
    
    .conversation-item:hover {
        background: #f8fafc;
    }
    
    .conversation-item.unread {
        background: #fff7ed;
    }
    
    .conversation-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
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
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .conversation-preview {
        font-size: 0.9rem;
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
        font-size: 0.8rem;
        color: #94a3b8;
        margin-bottom: 6px;
    }
    
    .unread-count {
        background: #f97316;
        color: #fff;
        font-size: 0.75rem;
        padding: 3px 8px;
        border-radius: 999px;
        font-weight: 600;
    }
    
    .priority-badge {
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .priority-urgent { background: #fee2e2; color: #dc2626; }
    .priority-high { background: #fef3c7; color: #d97706; }
    .priority-normal { background: #dbeafe; color: #2563eb; }
    .priority-low { background: #d1fae5; color: #059669; }
    
    .status-badge {
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 600;
    }
    
    .status-active { background: #d1fae5; color: #059669; }
    .status-resolved { background: #dbeafe; color: #2563eb; }
    
    .empty-state {
        padding: 60px 40px;
        text-align: center;
    }
    
    .empty-state-icon {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f97316, #ea580c);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
    }
    
    .empty-state-icon i {
        font-size: 40px;
        color: #fff;
    }
    
    .empty-state h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }
    
    .empty-state p {
        color: #64748b;
        margin-bottom: 24px;
    }
    
    /* Modal */
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
        max-height: 90vh;
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
    }
    
    .modal-body {
        padding: 24px;
        overflow-y: auto;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: border-color 0.2s;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #f97316;
    }
    
    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }
</style>
@endpush

@section('content')
<div class="chat-page">
    <div class="chat-header-section">
        <h1>
            <i class="fas fa-comments" style="color: #f97316;"></i>
            Messages
            @if($totalUnread > 0)
                <span class="unread-badge">{{ $totalUnread }} new</span>
            @endif
        </h1>
        <div class="chat-actions">
            <button class="action-btn secondary" onclick="openTicketModal()">
                <i class="fas fa-wrench"></i> Report Issue
            </button>
            <form action="{{ route('tenant.chat.start-with-landlord') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="action-btn primary">
                    <i class="fas fa-comment-dots"></i> Message Landlord
                </button>
            </form>
        </div>
    </div>
    
    <div class="conversation-list">
        @forelse($conversations as $conversation)
            @php
                $other = $conversation->getOtherParticipant(auth()->id());
                $unread = $conversation->getUnreadCountFor(auth()->id());
            @endphp
            <a href="{{ route('tenant.chat.show', $conversation->id) }}" 
               class="conversation-item {{ $unread > 0 ? 'unread' : '' }}">
                <div class="conversation-avatar {{ $conversation->type === 'maintenance_ticket' ? 'ticket' : '' }}">
                    @if($conversation->type === 'maintenance_ticket')
                        <i class="fas fa-wrench"></i>
                    @else
                        {{ $other ? strtoupper(substr($other->name, 0, 1)) : 'L' }}
                    @endif
                </div>
                <div class="conversation-info">
                    <div class="conversation-name">
                        @if($conversation->type === 'maintenance_ticket')
                            {{ $conversation->subject ?? 'Maintenance Request' }}
                            <span class="priority-badge priority-{{ $conversation->priority }}">{{ $conversation->priority }}</span>
                            <span class="status-badge status-{{ $conversation->status }}">{{ $conversation->status }}</span>
                        @else
                            {{ $other?->name ?? 'Landlord' }}
                        @endif
                    </div>
                    <div class="conversation-preview">
                        @if($conversation->latestMessage)
                            {{ Str::limit($conversation->latestMessage->content, 50) }}
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
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>No messages yet</h3>
                <p>Start a conversation with your landlord or report an issue.</p>
                <form action="{{ route('tenant.chat.start-with-landlord') }}" method="POST">
                    @csrf
                    <button type="submit" class="action-btn primary">
                        <i class="fas fa-comment-dots"></i> Message Landlord
                    </button>
                </form>
            </div>
        @endforelse
    </div>
</div>

<!-- Maintenance Ticket Modal -->
<div class="modal-overlay" id="ticketModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-wrench" style="color: #8b5cf6; margin-right: 10px;"></i>Report an Issue</h3>
            <button class="modal-close" onclick="closeTicketModal()">&times;</button>
        </div>
        <form action="{{ route('tenant.chat.create-ticket') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="subject">Issue Title</label>
                    <input type="text" id="subject" name="subject" required placeholder="e.g., Leaking faucet in bathroom">
                </div>
                
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority" required>
                        <option value="low">Low - Can wait a few days</option>
                        <option value="normal" selected>Normal - Should be fixed soon</option>
                        <option value="high">High - Needs attention today</option>
                        <option value="urgent">Urgent - Emergency situation</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required placeholder="Please describe the issue in detail..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="attachments">Attach Photos (optional)</label>
                    <input type="file" id="attachments" name="attachments[]" multiple accept="image/*,.pdf">
                    <small style="color: #64748b; display: block; margin-top: 6px;">Upload photos or documents to help explain the issue</small>
                </div>
            </div>
            <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="action-btn secondary" onclick="closeTicketModal()">Cancel</button>
                <button type="submit" class="action-btn primary">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openTicketModal() {
        document.getElementById('ticketModal').classList.add('show');
    }
    
    function closeTicketModal() {
        document.getElementById('ticketModal').classList.remove('show');
    }
    
    document.getElementById('ticketModal').addEventListener('click', function(e) {
        if (e.target === this) closeTicketModal();
    });
</script>
@endpush



