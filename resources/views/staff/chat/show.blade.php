@extends('layouts.staff-app')

@section('title', 'Chat')

@push('styles')
<style>
    .chat-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        height: calc(100vh - 200px);
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
    }
    
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
    
    .chat-input-area {
        padding: 16px 24px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 12px;
    }
    
    .chat-input {
        flex: 1;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.95rem;
        resize: none;
    }
    
    .chat-input:focus {
        outline: none;
        border-color: #f97316;
    }
    
    .send-btn {
        padding: 14px 24px;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="chat-card">
    <div class="chat-header">
        <a href="{{ route('staff.chat') }}" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 10px; background: #f1f5f9; color: #64748b; text-decoration: none;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem;">
            {{ $otherParticipants->first() ? strtoupper(substr($otherParticipants->first()->name, 0, 1)) : '?' }}
        </div>
        <div>
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
                {{ $otherParticipants->first()?->name ?? 'Unknown' }}
            </h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0;">
                @if($conversation->unit)
                    {{ $conversation->unit->apartment->name }} - Unit {{ $conversation->unit->unit_number }}
                @else
                    Direct message
                @endif
            </p>
        </div>
    </div>
    
    <div class="chat-messages" id="chatMessages">
        @foreach($conversation->messages as $message)
            @php $isMine = $message->sender_id === auth()->id(); @endphp
            <div class="message {{ $isMine ? 'sent' : 'received' }}">
                <div class="message-avatar">{{ $message->sender_avatar }}</div>
                <div class="message-content">
                    <div>{!! nl2br(e($message->content)) !!}</div>
                    <div style="font-size: 0.75rem; color: {{ $isMine ? 'rgba(255,255,255,0.8)' : '#94a3b8' }}; margin-top: 6px;">
                        {{ $message->formatted_time }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <form class="chat-input-area" id="messageForm">
        @csrf
        <textarea class="chat-input" id="messageInput" name="content" placeholder="Type your message..." rows="1" required></textarea>
        <button type="submit" class="send-btn" id="sendBtn">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
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
    
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            messageForm.dispatchEvent(new Event('submit'));
        }
    });
    
    messageForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const content = messageInput.value.trim();
        if (!content) return;
        
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('content', content);
        
        try {
            const response = await fetch('{{ route("staff.chat.send", $conversation->id) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });
            
            const data = await response.json();
            
            if (data.success) {
                const messageHtml = `
                    <div class="message sent">
                        <div class="message-avatar">${data.message.sender_avatar}</div>
                        <div class="message-content">
                            <div>${data.message.content.replace(/\n/g, '<br>')}</div>
                            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.8); margin-top: 6px;">
                                ${data.message.formatted_time}
                            </div>
                        </div>
                    </div>
                `;
                messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
                messageInput.value = '';
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                lastMessageId = data.message.id;
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });
    
    // Poll for new messages
    setInterval(async () => {
        try {
            const response = await fetch(`{{ route("staff.chat.fetch", $conversation->id) }}?last_message_id=${lastMessageId}`);
            const data = await response.json();
            
            if (data.success && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    if (msg.sender_id !== currentUserId) {
                        const messageHtml = `
                            <div class="message received">
                                <div class="message-avatar">${msg.sender_avatar}</div>
                                <div class="message-content">
                                    <div>${msg.content.replace(/\n/g, '<br>')}</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 6px;">
                                        ${msg.formatted_time}
                                    </div>
                                </div>
                            </div>
                        `;
                        messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
                    }
                    lastMessageId = Math.max(lastMessageId, msg.id);
                });
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }, 3000);
</script>
@endpush


