<?php

namespace App\Services\Chat;

use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

class ChatListService
{
    public function getForSidebar()
    {
        $userId = Auth::id();

        $chats = Chat::with([
            'lastMessage',
            'chatUsers.user.mainAvatar',
            'chatAvatars',
            'messages:id,chat_id'
        ])
            ->whereHas('chatUsers', fn($q) => $q->where('user_id', $userId))
            ->get()
            ->sortByDesc(fn($chat) => $chat->lastMessage?->created_at);

        return $chats->map(function ($chat) use ($userId) {
            $chatUser = $chat->chatUsers->firstWhere('user_id', $userId);

            $unreadCount = $chat->messages()
                ->where('id', '>', $chatUser->pivot->last_read_message_id ?? 0)
                ->count();

            if ($chat->is_group) {
                $name = $chat->title ?? 'Group chat';
                $avatar = $chat->chatAvatars
                    ->firstWhere('is_main', true)?->path;
            } elseif ($chat->is_self) {
                $user = $chatUser->user;
                $name = 'Saved messages';
                $avatar = $user->mainAvatar?->path;
            } else {
                $otherUser = $chat->chatUsers
                    ->firstWhere('user_id', '!=', $userId)?->user;

                $name = $otherUser?->username ?? 'Unknown';
                $avatar = $otherUser?->mainAvatar?->path;
            }

            return [
                'id' => $chat->id,
                'name' => $name,
                'avatar' => $avatar ? asset('storage/' . $avatar) : null,
                'lastMessage' => $chat->lastMessage?->body,
                'unreadCount' => $unreadCount,
            ];
        })->values();
    }
}
