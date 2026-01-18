<?php

namespace App\Http\Controllers\Chat;

use App\Events\Messages\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageCollection;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, Chat $chat)
    {
        $request->validate([
            'cursor' => 'nullable|integer|exists:messages,id',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $limit = $request->input('limit', 50);
        $cursor = $request->input('cursor');

        $query = $chat->messages()
            ->with(['user.mainAvatar', 'media', 'replyTo.user'])
            ->latest('id');

        if ($cursor) {
            $query->where('id', '<', $cursor);
        }

        $messages = $query->take($limit)
            ->get()
            ->reverse();

        $collection = new MessageCollection($messages);
        $collection->chatUsers = $chat->users()->get();

        return $collection;
    }

    public function context(Request $request, Chat $chat, Message $message)
    {
        try {
            $this->authorize('view', $chat);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'cursor' => 'nullable|integer|exists:messages,id',
        ]);

        $oldestMessage = $request->input('cursor');
        $newestMessage = $message->id - 30 > 0 ? $message->id - 30 : 1;

        $query = $chat->messages()
            ->with(['user.mainAvatar', 'media', 'replyTo.user'])
            ->where('id', '>=', $message->id - 30)
            ->latest('id');

        if ($oldestMessage) {
            $query->where('id', '<', $oldestMessage);
        }

        $messages = $query->get()->reverse();

        $collection = new MessageCollection($messages);
        $collection->chatUsers = $chat->users()->get();
        return $collection;
    }

    public function store(Request $request, Chat $chat)
    {
        $validated = $request->validate([
            'body' => 'nullable|string',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,avi,mov|max:10240',
            'reply_for_message_id' => 'nullable|exists:messages,id',
        ]);

        $message = Message::create([
            'body' => $validated['body'],
            'chat_id' => $chat->id,
            'user_id' => auth()->user()->id,
            'reply_for_message_id' => $validated['reply_for_message_id'] ?? null,
        ]);

        $message->load('user.mainAvatar', 'media', 'replyTo.user');

        broadcast(new MessageSent($message))->toOthers();

        return new MessageResource($message);
    }
}
