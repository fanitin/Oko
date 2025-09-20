<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\ChatUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function getOrCreatePrivateChat(User $otherUser){
        $you = Auth::user();
        $isSelfChat = $otherUser->id === $you->id;

        $chat = Chat::where('is_group', false)
            ->when($isSelfChat, fn($q) => $q->where('is_self', true))
            ->whereHas('users', fn($q) => $q->where('user_id', $you->id))
            ->when(!$isSelfChat, fn($q) => $q->whereHas('users', fn($q2) => $q2->where('user_id', $otherUser->id)))
            ->first();

        if($chat){
            return $chat;
        }

        try {
            DB::beginTransaction();

            $chat = Chat::create([
                'is_group' => false,
                'is_self' => $isSelfChat,
            ]);

            ChatUser::create([
                'chat_id' => $chat->id,
                'user_id' => $you->id,
            ]);
            if (!$isSelfChat) {
                ChatUser::create([
                    'chat_id' => $chat->id,
                    'user_id' => $otherUser->id,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return $chat;
    }
}
