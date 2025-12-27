<?php

namespace App\Services\Chat;

use App\Http\Resources\UserResource;
use App\Models\Chat;
use App\Models\ChatUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function getOrCreateById(?int $chatId, array $participantsIds): Chat
    {
        $authId = Auth::id();

        if ($chatId) {
            return Chat::with([
                'users.mainAvatar',
                'chatAvatars',
                'messages',
            ])->findOrFail($chatId);
        }

        if (count($participantsIds) === 2) {
            $chat = Chat::query()
                ->where('is_group', false)
                ->whereHas('users', fn($q) => $q->whereIn('users.id', $participantsIds)
                )
                ->withCount('users')
                ->having('users_count', 2)
                ->with([
                    'users.mainAvatar',
                    'chatAvatars',
                    'messages',
                ])
                ->first();

            if ($chat) {
                return $chat;
            }
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

            return $chat->load([
                'users.mainAvatar',
                'chatAvatars',
                'messages',
            ]);
        });
    }
}
