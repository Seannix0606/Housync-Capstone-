@extends('layouts.tenant-app')

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

    .secondary-chat-btn {
        width: 100%;
        margin-top: 10px;
        padding: 10px 16px;
        background: #fff;
        color: #1e293b;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: border-color 0.2s, color 0.2s;
    }

    .secondary-chat-btn:hover {
        border-color: #8b5cf6;
        color: #7c3aed;
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
        max-width: 340px;
        margin-bottom: 20px;
    }

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

    .contact-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .contact-item:hover {
        border-color: #f97316;
        background: #fff7ed;
    }

    .contact-item .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        flex-shrink: 0;
    }

    .contact-item .avatar.landlord {
        background: linear-gradient(135deg, #f97316, #ea580c);
    }

    .contact-item .avatar.staff {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
    }

    .contact-item .avatar.tenant {
        background: linear-gradient(135deg, #22c55e, #15803d);
    }

    .contact-item .info h4 {
        font-weight: 600;
        color: #1e293b;
        margin: 0;
        font-size: 0.95rem;
    }

    .contact-item .info p {
        font-size: 0.85rem;
        color: #64748b;
        margin: 4px 0 0 0;
    }

    .ticket-modal .form-group {
        margin-bottom: 20px;
    }

    .ticket-modal .form-group label {
        display: block;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .ticket-modal .form-group input,
    .ticket-modal .form-group textarea,
    .ticket-modal .form-group select {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
    }

    .ticket-modal .form-group input:focus,
    .ticket-modal .form-group textarea:focus,
    .ticket-modal .form-group select:focus {
        outline: none;
        border-color: #f97316;
    }

    .ticket-actions {
        padding: 16px 24px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .ticket-actions .btn-cancel {
        padding: 10px 18px;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        background: #fff;
        font-weight: 600;
        cursor: pointer;
    }

    .ticket-actions .btn-submit {
        padding: 10px 18px;
        border-radius: 10px;
        border: none;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        font-weight: 600;
        cursor: pointer;
    }

    .list-pagination {
        padding: 12px;
        border-top: 1px solid #e2e8f0;
        background: #fff;
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
            <button type="button" class="new-chat-btn" onclick="openNewChatModal()">
                <i class="fas fa-plus"></i> New conversation
            </button>
            <button type="button" class="secondary-chat-btn" onclick="openTicketModal()">
                <i class="fas fa-wrench"></i> Report issue
            </button>
        </div>

        <div class="conversation-list" id="conversationList">
            @forelse($conversations as $conversation)
                @php
                    $other = $conversation->getOtherParticipant(auth()->id());
                    $unread = $conversation->getUnreadCountFor(auth()->id());
                @endphp
                <a href="{{ route('tenant.chat.show', $conversation->id) }}"
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
        @if($conversations->hasPages())
            <div class="list-pagination">
                {{ $conversations->links() }}
            </div>
        @endif
    </div>

    <div class="chat-empty">
        <div class="chat-empty-icon">
            <i class="fas fa-comments"></i>
        </div>
        <h3>Select a conversation</h3>
        <p>Choose someone you have messaged before, or start a new conversation with your landlord, neighbors at your property, or building staff.</p>
        <button type="button" class="new-chat-btn" onclick="openNewChatModal()">
            <i class="fas fa-plus"></i> New conversation
        </button>
    </div>
</div>

<div class="modal-overlay" id="newChatModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>New conversation</h3>
            <button type="button" class="modal-close" onclick="closeNewChatModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="color: #64748b; margin-bottom: 16px;">Message your landlord, other tenants at your property, or staff assigned to your building:</p>
            <div class="contact-list" id="contactList">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-spinner fa-spin"></i> Loading contacts…
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay ticket-modal" id="ticketModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-wrench" style="color: #8b5cf6; margin-right: 10px;"></i>Report an issue</h3>
            <button type="button" class="modal-close" onclick="closeTicketModal()">&times;</button>
        </div>
        <form action="{{ route('tenant.chat.create-ticket') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="subject">Issue title</label>
                    <input type="text" id="subject" name="subject" required placeholder="e.g., Leaking faucet in bathroom">
                </div>
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority" required>
                        <option value="low">Low — can wait a few days</option>
                        <option value="normal" selected>Normal — should be fixed soon</option>
                        <option value="high">High — needs attention today</option>
                        <option value="urgent">Urgent — emergency</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required placeholder="Describe the issue in detail…"></textarea>
                </div>
                <div class="form-group">
                    <label for="attachments">Photos (optional)</label>
                    <input type="file" id="attachments" name="attachments[]" multiple accept="image/*,.pdf">
                    <small style="color: #64748b; display: block; margin-top: 6px;">Photos or PDFs help explain the issue</small>
                </div>
            </div>
            <div class="ticket-actions">
                <button type="button" class="btn-cancel" onclick="closeTicketModal()">Cancel</button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openNewChatModal() {
        document.getElementById('newChatModal').classList.add('show');
        loadContacts();
    }

    function closeNewChatModal() {
        document.getElementById('newChatModal').classList.remove('show');
    }

    function openTicketModal() {
        document.getElementById('ticketModal').classList.add('show');
    }

    function closeTicketModal() {
        document.getElementById('ticketModal').classList.remove('show');
    }

    async function loadContacts() {
        try {
            const response = await fetch('{{ route("tenant.chat.contacts-list") }}');
            const data = await response.json();
            const listEl = document.getElementById('contactList');

            if (!data.success || !data.contacts || data.contacts.length === 0) {
                listEl.innerHTML = `
                    <div style="text-align: center; padding: 20px; color: #64748b;">
                        <i class="fas fa-address-book" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i>
                        <p>No contacts available. You need an active lease to message people at your property.</p>
                    </div>
                `;
                return;
            }

            listEl.innerHTML = data.contacts.map(function (c) {
                const initial = (c.name || 'U').charAt(0).toUpperCase();
                const roleClass = c.role === 'staff' ? 'staff' : (c.role === 'tenant' ? 'tenant' : 'landlord');
                return `
                <form action="{{ route('tenant.chat.start-with-user') }}" method="POST" style="margin:0;">
                    @csrf
                    <input type="hidden" name="user_id" value="${c.id}">
                    <button type="submit" class="contact-item" style="width:100%;background:none;text-align:left;">
                        <div class="avatar ${roleClass}">${initial}</div>
                        <div class="info" style="flex:1;min-width:0;">
                            <h4>${escapeHtml(c.name || 'User')}</h4>
                            <p>${escapeHtml(c.subtitle || '')}</p>
                        </div>
                        <i class="fas fa-chevron-right" style="color:#94a3b8;flex-shrink:0;"></i>
                    </button>
                </form>`;
            }).join('');
        } catch (e) {
            console.error(e);
            document.getElementById('contactList').innerHTML = `
                <div style="text-align: center; padding: 20px; color: #dc2626;">
                    <i class="fas fa-exclamation-circle"></i> Failed to load contacts
                </div>
            `;
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    document.getElementById('newChatModal').addEventListener('click', function (e) {
        if (e.target === this) closeNewChatModal();
    });

    document.getElementById('ticketModal').addEventListener('click', function (e) {
        if (e.target === this) closeTicketModal();
    });

    setInterval(async () => {
        try {
            await fetch('{{ route("tenant.chat.unread-count") }}');
        } catch (e) { /* ignore */ }
    }, 30000);
</script>
@endpush
