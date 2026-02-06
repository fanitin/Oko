<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Http\Resources\UserResource;
use App\Models\Chat;
use App\Models\User;
use App\Services\Chat\ChatService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ChatController extends Controller
{
    public $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function show(?Chat $chat)
    {
        $authId = auth()->id();

        $participantsIds = $chat
            ? $chat->users->pluck('id')->toArray()
            : [$authId];

        $chat = $this->chatService->getOrCreateById(
            $chat?->id,
            $participantsIds
        );

        return Inertia::render('chat/Index', [
            'chat' => (new ChatResource(
                $chat->load([
                    'users.mainAvatar',
                    'chatAvatars',
                ])
            ))->resolve(),
        ]);
    }

    public function togglePin(Chat $chat)
    {
        try {
            $user = auth()->user();
            $userChat = $chat->chatUsers()->where('user_id', $user->id)->firstOrFail();
            DB::beginTransaction();

            $userChat->update([
                'is_pinned' => !$userChat->is_pinned,
            ]);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
            ]);
        }

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function toggleMute(Chat $chat)
    {
        try {
            $user = auth()->user();
            $userChat = $chat->chatUsers()->where('user_id', $user->id)->firstOrFail();
            DB::beginTransaction();

            $userChat->update([
                'is_muted' => !$userChat->is_muted,
            ]);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
            ]);
        }

        return response()->json([
            'status' => 'success',
        ]);
    }
}
