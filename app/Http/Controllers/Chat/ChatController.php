<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserAvatarResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ChatController extends Controller
{
    public $chatService;
    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index(User $user){
        $chat = $this->chatService->getOrCreatePrivateChat($user);
        $user = new UserResource($user->load('mainAvatar', 'avatars'));

        return Inertia::render('chat/Index', [
            'chatWith' => $user->resolve(),
            'chat' => $chat,
        ]);
    }
}
