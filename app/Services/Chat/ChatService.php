<?php

namespace App\Services\Chat;

use App\Events\Chat\ChatCreated;
use App\Http\Resources\UserResource;
use App\Models\Chat;
use App\Models\ChatUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatService
{
    protected ChatListService $chatListService;

    public function __construct(ChatListService $chatListService)
    {
        $this->chatListService = $chatListService;
    }
    public function getOrCreateById(?int $chatId, array $participantsIds): Chat
    {
        $authId = Auth::id();

        if ($chatId) {
            return Chat::with([
                'users.mainAvatar',
                'chatAvatars',
            ])->findOrFail($chatId);
        }

        $isSelf = count($participantsIds) === 1 && in_array($authId, $participantsIds);
        $isGroup = count($participantsIds) > 2;

        return DB::transaction(function () use ($participantsIds, $isSelf, $isGroup) {
            $chat = Chat::create([
                'is_self' => $isSelf,
                'is_group' => $isGroup,
                'title' => null,
            ]);

            $chat->users()->sync($participantsIds);

            $chat->load([
                'users.mainAvatar',
                'chatAvatars',
                'messages',
                'lastMessage.chat.users',
                'chatUsers.user.mainAvatar',
            ]);

            $chatUsers = $chat->users;

            foreach ($chatUsers as $user) {
                $formattedChat = $this->chatListService->mapChatForUser($chat, $user->id);
                broadcast(new ChatCreated($formattedChat, $user->id));
            }

            return $chat;
        });
    }
}
