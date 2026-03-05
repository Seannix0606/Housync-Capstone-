<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Private channel for individual conversations
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    return $conversation && $conversation->isParticipant($user->id);
});

// Private channel for user's conversation updates
Broadcast::channel('user.{userId}.conversations', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});



