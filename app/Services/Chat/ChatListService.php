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

            // Определяем имя и аватар
            if ($chat->is_group) {
                $name = $chat->title ?? 'Group Chat';
                $mainAvatar = $chat->chatAvatars->firstWhere('is_main', 1);
                $avatar = $mainAvatar ? asset('storage/' . $mainAvatar->path) : null;
            } elseif ($chat->is_self) {
                $user = $chat->chatUsers->firstWhere('user_id', $userId)->user;
                $name = $user->username;
                $avatar = $user->mainAvatar ? asset('storage/' . $user->mainAvatar->path) : null;
            } else {
                $otherUser = $chat->chatUsers->firstWhere('user_id', '!=', $userId)?->user;
                $name = $otherUser->username ?? null;
                $avatar = $otherUser && $otherUser->mainAvatar
                    ? asset('storage/' . $otherUser->mainAvatar->path)
                    : null;
            }

            return [
                'id' => $chat->id,
                'name' => $name,
                'avatar' => $avatar,
                'lastMessage' => $chat->lastMessage?->body,
                'lastMessageAt' => $chat->lastMessage?->created_at,
                'unreadCount' => $unreadCount,
                'participants' => $chat->chatUsers->pluck('user_id')->toArray(),
            ];
        });
    }
}
