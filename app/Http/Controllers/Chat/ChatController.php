<?php

namespace App\Http\Controllers\Chat;

use App\Events\Chat\ChatDeleted;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ChatController extends Controller
{
    public $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function show(?Chat $chat, Request $request)
    {
        if($chat?->id && !$chat->users->contains(auth()->id())) {
            return redirect()->route('home');
        }

        if(!$chat?->id) {
            $authId = auth()->id();
            $userId = $request->query('user');

            if ($chat?->id) {
                $participantsIds = $chat->users->pluck('id')->toArray();
            } elseif ($userId && $userId != $authId) {
                $participantsIds = [$authId, (int)$userId];
            } else {
                $participantsIds = [$authId];
            }

            $chat = $this->chatService->getOrCreateById(
                $chat?->id,
                $participantsIds
            );

            if (!$request->route('chat') || $request->route('chat')?->id !== $chat->id) {
                return redirect()->route('chat.show', ['chat' => $chat->id]);
            }
        }

        if($request->query('user')) {
            return redirect()->route('chat.show', ['chat' => $chat->id]);
        }

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

    public function delete(Chat $chat)
    {
        $user = auth()->user();

        if (!$chat->chatUsers()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a participant of this chat',
            ], 403);
        }

        $chatID = $chat->id;
        $chatUsers = $chat->users;

        DB::transaction(function () use ($chat) {
            $chat->delete();
        });

        broadcast(new ChatDeleted($chatID, $chatUsers))->toOthers();


        return response()->json([
            'status' => 'success',
        ]);
    }
}
