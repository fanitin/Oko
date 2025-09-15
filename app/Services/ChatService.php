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

        $chat = Chat::where('is_group', false)
            ->whereHas('users', fn($q) => $q->where('user_id', $you->id))
            ->whereHas('users', fn($q) => $q->where('user_id', $otherUser->id))
            ->first();

        if($chat){
            return $chat;
        }

        try {
            DB::beginTransaction();

            $chat = Chat::create(['is_group' => false]);
            ChatUser::create([
                'chat_id' => $chat->id,
                'user_id' => $otherUser->id,
            ]);
            ChatUser::create([
                'chat_id' => $chat->id,
                'user_id' => $you->id,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return $chat;
    }
}
