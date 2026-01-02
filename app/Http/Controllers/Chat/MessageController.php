<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageCollection;
use App\Http\Resources\MessageResource;
use App\Models\Chat;

class MessageController extends Controller
{
    public function index(Chat $chat)
    {
        $messages = $chat->messages()
            ->with(['user.mainAvatar', 'media', 'replyTo.user'])
            ->latest()
            ->take(50)
            ->get()
            ->reverse();

        $chatUsers = $chat->users()->get(); // все участники чата

        $collection = new MessageCollection($messages);
        $collection->chatUsers = $chatUsers;

        return $collection;
    }


}
