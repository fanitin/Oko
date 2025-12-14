<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Chat;
use App\Models\User;
use App\Services\Chat\ChatService;
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
        $userId = auth()->id();

        if ($chat) {
            $participantsIds = $chat->chatUsers->pluck('user_id')->toArray();
        } else {
            $participantsIds = request()->get('participants', [$userId]);
        }

        $chat = $this->chatService->getOrCreateById($chat?->id, $participantsIds);

        return Inertia::render('chat/Index', [
            'chat' => $chat,
            'chatWith' => $chat->chatWith,
        ]);
    }
}
