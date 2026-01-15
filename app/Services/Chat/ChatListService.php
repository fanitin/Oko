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
            'messages:id,chat_id',
        ])
            ->whereHas('chatUsers', fn ($q) => $q->where('user_id', $userId))
            ->get()
            ->sortByDesc(fn ($chat) => $chat->lastMessage?->created_at);

        return $chats->map(fn ($chat) => $this->mapChat($chat, $userId))
            ->values();
    }

    protected function mapChat(Chat $chat, int $userId): array
    {
        return [
            'id' => $chat->id,
            'type' => $this->resolveType($chat),
            'name' => $this->resolveName($chat, $userId),
            'avatar' => $this->resolveAvatar($chat, $userId),
            'lastMessage' => $chat->lastOtherUserMessage($userId)->first()?->body,
            'unreadCount' => $this->resolveUnreadCount($chat, $userId),
        ];
    }

    protected function resolveType(Chat $chat): string
    {
        if ($chat->is_self) {
            return 'self';
        }

        if ($chat->is_group) {
            return 'group';
        }

        return 'private';
    }

    protected function resolveName(Chat $chat, int $userId): string
    {
        return match ($this->resolveType($chat)) {
            'self' => 'Saved messages',

            'group' => $chat->title ?? 'Group chat',

            'private' => optional(
                $chat->chatUsers->firstWhere('user_id', '!=', $userId)?->user
            )?->username ?? 'Unknown',
        };
    }

    protected function resolveAvatar(Chat $chat, int $userId): ?string
    {
        $path = match ($this->resolveType($chat)) {
            'group' => $chat->chatAvatars
                ->firstWhere('is_main', true)?->path,

            'self' => $chat->chatUsers
                ->firstWhere('user_id', $userId)?->user
                ?->mainAvatar?->path,

            'private' => $chat->chatUsers
                ->firstWhere('user_id', '!=', $userId)?->user
                ?->mainAvatar?->path,
        };

        return $path ? asset('storage/' . $path) : null;
    }

    protected function resolveUnreadCount(Chat $chat, int $userId): int
    {
        $chatUser = $chat->chatUsers->firstWhere('user_id', $userId);

        if (! $chatUser) {
            return 0;
        }

        return $chat->messages()
            ->where('id', '>', $chatUser->pivot->last_read_message_id ?? 0)
            ->where('user_id', '!=', $userId)
            ->count();
    }
}
