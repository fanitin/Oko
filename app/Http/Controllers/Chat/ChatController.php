<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
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
}
