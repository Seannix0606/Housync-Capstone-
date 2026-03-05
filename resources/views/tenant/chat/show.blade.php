@extends('layouts.tenant-app')

@section('title', 'Chat')

@push('styles')
<style>
    .chat-page {
        max-width: 900px;
        margin: 0 auto;
        height: calc(100vh - 180px);
        display: flex;
        flex-direction: column;
    }
    
    .chat-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .chat-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 16px;
        background: #fff;
    }
    
    .back-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #f1f5f9;
        color: #64748b;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .back-btn:hover {
        background: #e2e8f0;
        color: #1e293b;
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
    
    .chat-header-info {
        flex: 1;
    }
    
    .chat-header-info h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 4px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .chat-header-info p {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
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
    
    .status-badge {
        font-size: 0.65rem;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
    }
    
    .status-resolved { background: #d1fae5; color: #059669; }
    
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
        max-width: 75%;
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
    
    /* Input Area */
    .chat-input-area {
        padding: 16px 24px;
        border-top: 1px solid #e2e8f0;
        background: #fff;
    }
    
    @if($conversation->status === 'resolved')
    .chat-input-area {
        background: #f8fafc;
        text-align: center;
        color: #64748b;
    }
    @endif
    
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
        padding-right: 50px;
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
</style>
@endpush

@section('content')
<div class="chat-page">
    <div class="chat-card">
        <div class="chat-header">
            <a href="{{ route('tenant.chat') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="chat-header-avatar {{ $conversation->type === 'maintenance_ticket' ? 'ticket' : '' }}">
                @if($conversation->type === 'maintenance_ticket')
                    <i class="fas fa-wrench"></i>
                @else
                    {{ $otherParticipants->first() ? strtoupper(substr($otherParticipants->first()->name, 0, 1)) : 'L' }}
                @endif
            </div>
            <div class="chat-header-info">
                <h3>
                    @if($conversation->type === 'maintenance_ticket')
                        {{ $conversation->subject ?? 'Maintenance Request' }}
                        <span class="priority-badge priority-{{ $conversation->priority }}">{{ $conversation->priority }}</span>
                        @if($conversation->status === 'resolved')
                            <span class="status-badge status-resolved">Resolved</span>
                        @endif
                    @else
                        {{ $otherParticipants->first()?->name ?? 'Landlord' }}
                    @endif
                </h3>
                <p>
                    @if($conversation->type === 'maintenance_ticket' && $conversation->unit)
                        {{ $conversation->unit->apartment->name }} - Unit {{ $conversation->unit->unit_number }}
                    @else
                        Direct message
                    @endif
                </p>
            </div>
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
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        
        <div class="chat-input-area">
            @if($conversation->status === 'resolved')
                <p><i class="fas fa-check-circle" style="color: #10b981;"></i> This ticket has been resolved. If you need further assistance, please create a new request.</p>
            @else
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
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const conversationId = {{ $conversation->id }};
    const currentUserId = {{ auth()->id() }};
    let lastMessageId = {{ $conversation->messages->last()?->id ?? 0 }};
    
    const messagesContainer = document.getElementById('chatMessages');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    
    // Scroll to bottom
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    scrollToBottom();
    
    @if($conversation->status !== 'resolved')
    // Auto-resize textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
    
    // Send message on Enter
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            messageForm.dispatchEvent(new Event('submit'));
        }
    });
    
    // Submit message
    messageForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const content = messageInput.value.trim();
        if (!content) return;
        
        sendBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('content', content);
        
        const fileInput = document.getElementById('fileInput');
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append('attachments[]', fileInput.files[i]);
        }
        
        try {
            const response = await fetch('{{ route("tenant.chat.send", $conversation->id) }}', {
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
    @endif
    
    // Append message
    function appendMessage(msg, isMine) {
        const messageHtml = `
            <div class="message ${isMine ? 'sent' : 'received'}" data-id="${msg.id}">
                <div class="message-avatar">${msg.sender_avatar}</div>
                <div class="message-content">
                    <div class="message-text">${msg.content.replace(/\n/g, '<br>')}</div>
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
        try {
            const response = await fetch(`{{ route("tenant.chat.fetch", $conversation->id) }}?last_message_id=${lastMessageId}`);
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
        
        setTimeout(pollMessages, 3000);
    }
    
    pollMessages();
</script>
@endpush


