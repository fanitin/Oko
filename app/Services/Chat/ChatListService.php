<?php

namespace App\Services\Chat;

use App\Http\Resources\MessageResource;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

class ChatListService
{
    public function getForSidebar()
    {
        $userId = Auth::id();

        $chats = Chat::query()
            ->select('chats.*')
            ->join('chat_users', 'chat_users.chat_id', '=', 'chats.id')
            ->leftJoin('messages', 'messages.chat_id', '=', 'chats.id')
            ->where('chat_users.user_id', $userId)
            ->groupBy('chats.id', 'chat_users.is_pinned')
            ->orderByDesc('chat_users.is_pinned')
            ->orderByRaw('MAX(messages.created_at) DESC')
            ->with([
                'lastMessage.chat.users',
                'chatUsers.user.mainAvatar',
                'chatAvatars',
                'messages:id,chat_id',
            ])
            ->get();

        return $chats->map(fn ($chat) => $this->mapChat($chat, $userId))
            ->values();
    }

    protected function mapChat(Chat $chat, int $userId): array
    {
        return [
            'id' => $chat->id,
            'settings' => $this->resolveSettings($chat, $userId),
            'type' => $this->resolveType($chat),
            'name' => $this->resolveName($chat, $userId),
            'avatar' => $this->resolveAvatar($chat, $userId),
            'lastMessage' => $this->resolveMessage($chat->lastMessage),
            'unreadCount' => $this->resolveUnreadCount($chat, $userId),
            'friendUserId' => $this->resolveFriendUserId($chat, $userId),
        ];
    }

    protected function resolveSettings(Chat $chat, int $userId): array
    {
        $chatUser = $chat->chatUsers->firstWhere('user_id', $userId);

        return [
            'isPinned' => $chatUser && $chatUser->is_pinned,
            'isMuted' => $chatUser && $chatUser->is_muted,
        ];
    }

    protected function resolveFriendUserId(Chat $chat, int $userId): ?int
    {
        if ($this->resolveType($chat) !== 'private') {
            return null;
        }

        $friendChatUser = $chat->chatUsers->firstWhere('user_id', '!=', $userId);
        return $friendChatUser ? $friendChatUser->user_id : null;
    }

    protected function resolveMessage($message){
        if(!$message){
            return null;
        }

        $resource = new MessageResource($message);
        $resource->chatUsers = $message->chat->users;
        return $resource->resolve();
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
            ->where('id', '>', $chatUser->last_read_message_id ?? 0)
            ->where('user_id', '!=', $userId)
            ->count();
    }
}
