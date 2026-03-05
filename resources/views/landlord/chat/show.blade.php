@extends('layouts.landlord-app')

@section('title', 'Chat')

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
    
    /* Sidebar */
    .chat-sidebar {
        width: 300px;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        background: #f8fafc;
    }
    
    .chat-sidebar-header {
        padding: 16px;
        border-bottom: 1px solid #e2e8f0;
        background: #fff;
    }
    
    .back-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        text-decoration: none;
        font-weight: 500;
        padding: 8px 12px;
        border-radius: 8px;
        transition: all 0.2s;
    }
    
    .back-btn:hover {
        background: #f1f5f9;
        color: #1e293b;
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
        padding: 12px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 4px;
        text-decoration: none;
    }
    
    .conversation-item:hover {
        background: #fff;
    }
    
    .conversation-item.active {
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-left: 3px solid #f97316;
    }
    
    .conversation-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        flex-shrink: 0;
    }
    
    .conversation-info {
        flex: 1;
        min-width: 0;
    }
    
    .conversation-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
    }
    
    .conversation-preview {
        font-size: 0.8rem;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Main Chat Area */
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #fff;
    }
    
    .chat-header {
        padding: 16px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
    }
    
    .chat-header-info {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .chat-header-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
    }
    
    .chat-header-avatar.ticket {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }
    
    .chat-header-details h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 4px 0;
    }
    
    .chat-header-details p {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
    }
    
    .chat-header-actions {
        display: flex;
        gap: 8px;
    }
    
    .header-action-btn {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
    }
    
    .header-action-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    
    .header-action-btn.resolve {
        background: #10b981;
        color: #fff;
        border-color: #10b981;
    }
    
    .header-action-btn.resolve:hover {
        background: #059669;
    }
    
    /* Messages Area */
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    
    .message {
        display: flex;
        gap: 12px;
        max-width: 70%;
        animation: messageSlide 0.3s ease;
    }
    
    @keyframes messageSlide {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .message.sent {
        flex-direction: row-reverse;
        margin-left: auto;
    }
    
    .message-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #64748b, #475569);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.85rem;
        flex-shrink: 0;
    }
    
    .message.sent .message-avatar {
        background: linear-gradient(135deg, #f97316, #ea580c);
    }
    
    .message-content {
        background: #fff;
        padding: 12px 16px;
        border-radius: 16px;
        border-top-left-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .message.sent .message-content {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        border-radius: 16px;
        border-top-right-radius: 4px;
    }
    
    .message-text {
        font-size: 0.95rem;
        line-height: 1.5;
        word-wrap: break-word;
    }
    
    .message-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 6px;
        font-size: 0.75rem;
        color: #94a3b8;
    }
    
    .message.sent .message-meta {
        color: rgba(255,255,255,0.8);
        justify-content: flex-end;
    }
    
    .message-attachments {
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .attachment-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: #f1f5f9;
        border-radius: 8px;
        font-size: 0.85rem;
        color: #1e293b;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .message.sent .attachment-item {
        background: rgba(255,255,255,0.2);
        color: #fff;
    }
    
    .attachment-item:hover {
        background: #e2e8f0;
    }
    
    .attachment-image {
        max-width: 200px;
        border-radius: 8px;
        cursor: pointer;
    }
    
    .message-system {
        text-align: center;
        padding: 8px 16px;
        background: #f1f5f9;
        border-radius: 999px;
        font-size: 0.85rem;
        color: #64748b;
        margin: 8px auto;
    }
    
    /* Input Area */
    .chat-input-area {
        padding: 16px 24px;
        border-top: 1px solid #e2e8f0;
        background: #fff;
    }
    
    .chat-input-form {
        display: flex;
        gap: 12px;
        align-items: flex-end;
    }
    
    .chat-input-wrapper {
        flex: 1;
        position: relative;
    }
    
    .chat-input {
        width: 100%;
        padding: 14px 16px;
        padding-right: 100px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.95rem;
        resize: none;
        min-height: 52px;
        max-height: 120px;
        transition: border-color 0.2s;
        font-family: inherit;
    }
    
    .chat-input:focus {
        outline: none;
        border-color: #f97316;
    }
    
    .input-actions {
        position: absolute;
        right: 8px;
        bottom: 8px;
        display: flex;
        gap: 4px;
    }
    
    .input-action-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        background: #f1f5f9;
        color: #64748b;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    
    .input-action-btn:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    
    .send-btn {
        padding: 14px 24px;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    
    .send-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
    }
    
    .send-btn:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    /* Typing Indicator */
    .typing-indicator {
        display: none;
        padding: 8px 16px;
        font-size: 0.85rem;
        color: #64748b;
        font-style: italic;
    }
    
    /* Date Separator */
    .date-separator {
        text-align: center;
        margin: 16px 0;
        position: relative;
    }
    
    .date-separator span {
        background: #f8fafc;
        padding: 0 16px;
        font-size: 0.8rem;
        color: #94a3b8;
        position: relative;
        z-index: 1;
    }
    
    .date-separator::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #e2e8f0;
    }
    
    /* Responsive */
    @media (max-width: 900px) {
        .chat-sidebar {
            display: none;
        }
    }
    
    @media (max-width: 600px) {
        .chat-container {
            height: calc(100vh - 140px);
        }
        .message {
            max-width: 85%;
        }
        .chat-header-actions {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="chat-container">
    <!-- Main Chat Area -->
    <div class="chat-main">
        <div class="chat-header">
            <div class="chat-header-info">
                <a href="{{ route('landlord.chat') }}" class="back-btn d-md-none">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="chat-header-avatar {{ $conversation->type === 'maintenance_ticket' ? 'ticket' : '' }}">
                    @if($conversation->type === 'maintenance_ticket')
                        <i class="fas fa-wrench"></i>
                    @else
                        {{ $otherParticipants->first() ? strtoupper(substr($otherParticipants->first()->name, 0, 1)) : '?' }}
                    @endif
                </div>
                <div class="chat-header-details">
                    <h3>
                        @if($conversation->type === 'maintenance_ticket')
                            {{ $conversation->subject ?? 'Maintenance Request' }}
                        @else
                            {{ $otherParticipants->first()?->name ?? 'Unknown' }}
                        @endif
                    </h3>
                    <p>
                        @if($conversation->type === 'maintenance_ticket')
                            @if($conversation->unit)
                                {{ $conversation->unit->apartment->name }} - Unit {{ $conversation->unit->unit_number }}
                            @endif
                            <span class="priority-badge priority-{{ $conversation->priority }}" style="margin-left: 8px;">{{ $conversation->priority }}</span>
                        @else
                            @if($conversation->apartment)
                                {{ $conversation->apartment->name }}
                            @else
                                Direct message
                            @endif
                        @endif
                    </p>
                </div>
            </div>
            
            @if($conversation->type === 'maintenance_ticket' && $conversation->status !== 'resolved')
                <div class="chat-header-actions">
                    <button class="header-action-btn resolve" onclick="resolveTicket()">
                        <i class="fas fa-check"></i> Mark Resolved
                    </button>
                </div>
            @endif
        </div>
        
        <div class="chat-messages" id="chatMessages">
            @php $lastDate = null; @endphp
            @foreach($conversation->messages as $message)
                @php
                    $messageDate = $message->created_at->format('Y-m-d');
                    $showDateSeparator = $lastDate !== $messageDate;
                    $lastDate = $messageDate;
                    $isMine = $message->sender_id === auth()->id();
                @endphp
                
                @if($showDateSeparator)
                    <div class="date-separator">
                        <span>{{ $message->created_at->format('F j, Y') }}</span>
                    </div>
                @endif
                
                @if($message->type === 'system')
                    <div class="message-system">
                        <i class="fas fa-info-circle"></i> {{ $message->content }}
                    </div>
                @else
                    <div class="message {{ $isMine ? 'sent' : 'received' }}" data-id="{{ $message->id }}">
                        <div class="message-avatar">{{ $message->sender_avatar }}</div>
                        <div class="message-content">
                            <div class="message-text">{!! nl2br(e($message->content)) !!}</div>
                            
                            @if($message->attachments->count() > 0)
                                <div class="message-attachments">
                                    @foreach($message->attachments as $attachment)
                                        @if($attachment->is_image)
                                            <a href="{{ $attachment->file_url }}" target="_blank">
                                                <img src="{{ $attachment->file_url }}" alt="{{ $attachment->file_name }}" class="attachment-image">
                                            </a>
                                        @else
                                            <a href="{{ $attachment->file_url }}" target="_blank" class="attachment-item">
                                                <i class="fas fa-file"></i>
                                                <span>{{ $attachment->file_name }}</span>
                                                <small>({{ $attachment->formatted_size }})</small>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            
                            <div class="message-meta">
                                <span>{{ $message->formatted_time }}</span>
                                @if($isMine && $message->is_read)
                                    <i class="fas fa-check-double" title="Read"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        
        <div class="typing-indicator" id="typingIndicator">
            <i class="fas fa-ellipsis-h"></i> Someone is typing...
        </div>
        
        <div class="chat-input-area">
            <form class="chat-input-form" id="messageForm" enctype="multipart/form-data">
                @csrf
                <div class="chat-input-wrapper">
                    <textarea 
                        class="chat-input" 
                        id="messageInput"
                        name="content" 
                        placeholder="Type your message..."
                        rows="1"
                        required
                    ></textarea>
                    <div class="input-actions">
                        <label class="input-action-btn" title="Attach file">
                            <i class="fas fa-paperclip"></i>
                            <input type="file" name="attachments[]" multiple hidden id="fileInput" accept="image/*,.pdf,.doc,.docx">
                        </label>
                    </div>
                </div>
                <button type="submit" class="send-btn" id="sendBtn">
                    <i class="fas fa-paper-plane"></i>
                    <span class="d-none d-sm-inline">Send</span>
                </button>
            </form>
            <div id="attachmentPreview" style="display: none; margin-top: 10px; padding: 10px; background: #f8fafc; border-radius: 8px;"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const conversationId = {{ $conversation->id }};
    const currentUserId = {{ auth()->id() }};
    let lastMessageId = {{ $conversation->messages->last()?->id ?? 0 }};
    let isPolling = true;
    
    const messagesContainer = document.getElementById('chatMessages');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    const fileInput = document.getElementById('fileInput');
    const attachmentPreview = document.getElementById('attachmentPreview');
    
    // Scroll to bottom
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    scrollToBottom();
    
    // Auto-resize textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
    
    // Send message on Enter (Shift+Enter for new line)
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            messageForm.dispatchEvent(new Event('submit'));
        }
    });
    
    // File attachment preview
    fileInput.addEventListener('change', function() {
        const files = Array.from(this.files);
        if (files.length > 0) {
            attachmentPreview.style.display = 'block';
            attachmentPreview.innerHTML = files.map(f => `
                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #fff; border-radius: 6px; margin-right: 8px; font-size: 0.85rem;">
                    <i class="fas fa-paperclip"></i> ${f.name}
                    <button type="button" onclick="removeFile('${f.name}')" style="border: none; background: none; color: #dc2626; cursor: pointer;">&times;</button>
                </span>
            `).join('');
        } else {
            attachmentPreview.style.display = 'none';
        }
    });
    
    // Submit message
    messageForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const content = messageInput.value.trim();
        const files = fileInput.files;
        
        if (!content && files.length === 0) return;
        
        sendBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('content', content);
        
        for (let i = 0; i < files.length; i++) {
            formData.append('attachments[]', files[i]);
        }
        
        try {
            const response = await fetch('{{ route("landlord.chat.send", $conversation->id) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server error:', response.status, errorText);
                alert('Server error: ' + (response.status === 500 ? 'Internal server error' : response.statusText));
                return;
            }
            
            const data = await response.json();
            
            if (data.success) {
                appendMessage(data.message, true);
                messageInput.value = '';
                messageInput.style.height = 'auto';
                fileInput.value = '';
                attachmentPreview.style.display = 'none';
                lastMessageId = data.message.id;
                scrollToBottom();
            } else {
                alert(data.error || 'Failed to send message. Please try again.');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            alert('Error: ' + error.message);
        } finally {
            sendBtn.disabled = false;
        }
    });
    
    // Append new message to chat
    function appendMessage(msg, isMine) {
        const messageHtml = `
            <div class="message ${isMine ? 'sent' : 'received'}" data-id="${msg.id}">
                <div class="message-avatar">${msg.sender_avatar}</div>
                <div class="message-content">
                    <div class="message-text">${msg.content.replace(/\n/g, '<br>')}</div>
                    ${msg.attachments && msg.attachments.length > 0 ? `
                        <div class="message-attachments">
                            ${msg.attachments.map(a => a.is_image 
                                ? `<a href="${a.file_url}" target="_blank"><img src="${a.file_url}" alt="${a.file_name}" class="attachment-image"></a>`
                                : `<a href="${a.file_url}" target="_blank" class="attachment-item"><i class="fas fa-file"></i><span>${a.file_name}</span><small>(${a.formatted_size})</small></a>`
                            ).join('')}
                        </div>
                    ` : ''}
                    <div class="message-meta">
                        <span>${msg.formatted_time}</span>
                    </div>
                </div>
            </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
    }
    
    // Poll for new messages
    async function pollMessages() {
        if (!isPolling) return;
        
        try {
            const response = await fetch(`{{ route("landlord.chat.fetch", $conversation->id) }}?last_message_id=${lastMessageId}`);
            const data = await response.json();
            
            if (data.success && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    if (msg.sender_id !== currentUserId) {
                        appendMessage(msg, false);
                    }
                    lastMessageId = Math.max(lastMessageId, msg.id);
                });
                scrollToBottom();
            }
        } catch (error) {
            console.error('Error polling messages:', error);
        }
        
        setTimeout(pollMessages, 3000); // Poll every 3 seconds
    }
    
    // Start polling
    pollMessages();
    
    // Resolve ticket
    async function resolveTicket() {
        if (!confirm('Mark this ticket as resolved?')) return;
        
        try {
            const response = await fetch('{{ route("landlord.chat.ticket-status", $conversation->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ status: 'resolved' }),
            });
            
            const data = await response.json();
            if (data.success) {
                location.reload();
            }
        } catch (error) {
            console.error('Error resolving ticket:', error);
            alert('Failed to update ticket status.');
        }
    }
    
    // Mark as read on focus
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            fetch('{{ route("landlord.chat.mark-read", $conversation->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            });
        }
    });
</script>
@endpush


