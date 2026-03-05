@extends('layouts.staff-app')

@section('title', 'Messages')

@section('content')
<div class="content-header">
    <h1><i class="fas fa-comments" style="color: #f97316; margin-right: 10px;"></i>Messages</h1>
</div>

<div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden;">
    @forelse($conversations as $conversation)
        @php
            $other = $conversation->getOtherParticipant(auth()->id());
            $unread = $conversation->getUnreadCountFor(auth()->id());
        @endphp
        <a href="{{ route('staff.chat.show', $conversation->id) }}" 
           style="display: flex; align-items: center; gap: 16px; padding: 20px 24px; border-bottom: 1px solid #f1f5f9; text-decoration: none; transition: background 0.2s; {{ $unread > 0 ? 'background: #fff7ed;' : '' }}"
           onmouseover="this.style.background='#f8fafc'" 
           onmouseout="this.style.background='{{ $unread > 0 ? '#fff7ed' : '#fff' }}'">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem;">
                {{ $other ? strtoupper(substr($other->name, 0, 1)) : '?' }}
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 600; color: #1e293b; margin-bottom: 4px;">
                    {{ $other?->name ?? 'Unknown' }}
                    @if($conversation->type === 'maintenance_ticket')
                        <span style="font-size: 0.7rem; padding: 2px 8px; background: #dbeafe; color: #2563eb; border-radius: 4px; margin-left: 8px;">Maintenance</span>
                    @endif
                </div>
                <div style="font-size: 0.9rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    @if($conversation->latestMessage)
                        {{ Str::limit($conversation->latestMessage->content, 50) }}
                    @else
                        No messages yet
                    @endif
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 6px;">
                    {{ $conversation->last_message_at?->diffForHumans(null, true) ?? 'New' }}
                </div>
                @if($unread > 0)
                    <span style="background: #f97316; color: #fff; font-size: 0.75rem; padding: 3px 8px; border-radius: 999px; font-weight: 600;">{{ $unread }}</span>
                @endif
            </div>
        </a>
    @empty
        <div style="padding: 60px 40px; text-align: center;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #f97316, #ea580c); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-comments" style="font-size: 32px; color: #fff;"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">No messages yet</h3>
            <p style="color: #64748b;">Messages related to your assignments will appear here.</p>
        </div>
    @endforelse
</div>
@endsection



