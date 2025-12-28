<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Chat;

class MessageController extends Controller
{
    public function index(Chat $chat)
    {
        $messages = $chat->messages()
            ->with([
                'user.mainAvatar',
                'media',
                'replyTo.user',
            ])
            ->latest()
            ->paginate(30);

        return MessageResource::collection($messages);
    }


}
