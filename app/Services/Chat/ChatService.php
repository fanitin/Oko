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
        $youId = Auth::id();

        if ($chatId) {
            $chat = Chat::with(['chatUsers.user.mainAvatar', 'messages'])->findOrFail($chatId);
        } else {
            if (count($participantsIds) === 2) {
                $chat = Chat::where('is_group', false)
                    ->whereHas('chatUsers', fn($q) => $q->whereIn('user_id', $participantsIds))
                    ->withCount('chatUsers')
                    ->get()
                    ->first(fn($c) => $c->chat_users_count === 2);

                if ($chat) {
                    return $chat->load(['chatUsers.user.mainAvatar', 'messages']);
                }
            }

            $isSelf = count($participantsIds) === 1 && in_array($youId, $participantsIds);
            $isGroup = count($participantsIds) > 2;

            DB::beginTransaction();
            try {
                $chat = Chat::create([
                    'is_group' => $isGroup,
                    'is_self' => $isSelf,
                    'title' => $isGroup ? null : null,
                ]);

                $insertData = array_map(fn($id) => ['chat_id' => $chat->id, 'user_id' => $id], $participantsIds);
                ChatUser::insert($insertData);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            $chat->load(['chatUsers.user.mainAvatar', 'messages']);
        }

        $chatWith = null;
        if ($chat->is_group) {
            // placeholder for group chat
            $chatWith = [
                'id' => $chat->id,
                'username' => $chat->title ?? 'Group Chat',
                'main_avatar' => null,
                'avatars' => [],
            ];
        } elseif ($chat->is_self) {
            $user = Auth::user()->load('mainAvatar', 'avatars');
            $chatWith = (new UserResource($user))->resolve();
        } else {
            $otherUser = $chat->chatUsers
                ->where('user_id', '!=', $youId)
                ->first()?->user;

            $chatWith = $otherUser
                ? (new UserResource($otherUser->load('mainAvatar', 'avatars')))->resolve()
                : null;
        }

        $chat->chatWith = $chatWith;

        return $chat;
    }
}
