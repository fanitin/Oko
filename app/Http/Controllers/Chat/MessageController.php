<?php

namespace App\Http\Controllers\Chat;

use App\Events\Messages\MessageDeleted;
use App\Events\Messages\MessageEdited;
use App\Events\Messages\MessageMarkAsRead;
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

        $message->load('user.mainAvatar', 'media', 'replyTo.user', 'chat.users');

        broadcast(new MessageSent($message))->toOthers();

        return new MessageResource($message);
    }

    public function delete(Request $request, Chat $chat, Message $message)
    {
        try {
            $this->authorize('delete', $message);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $isLastMessage = $chat->lastMessage?->id === $message->id;
        $newLastMessage = $chat?->lastMessage;

        if ($isLastMessage) {
            $newLastMessage = $chat->messages()
                ->where('id', '<', $message->id)
                ->latest()
                ->first();
        }

        $deletedMessageId = $message->id;
        $originalUserId = $message->user_id;

        $isMessageUnread = $chat->chatUsers()
            ->where('user_id', '!=', $message->user_id)
            ->where('last_read_message_id', '<', $message->id)
            ->exists();

        $chatUsers = $chat->users;

        $message->delete();

        broadcast(new MessageDeleted($deletedMessageId, $originalUserId, $chat->id, $newLastMessage, $isMessageUnread, $chatUsers));

        return response()->json(['message' => 'Message deleted successfully'], 200);
    }

    public function markAsRead(Chat $chat)
    {
        $user = auth()->user();

        $chat->chatUsers()
            ->where('user_id', $user->id)
            ->update(['last_read_message_id' => $chat->lastMessage()?->first()->id ?? null]);

        $chatUsers = $chat->users;

        broadcast(new MessageMarkAsRead($chat->id, $user->id, $chatUsers))->toOthers();
        return response()->noContent();
    }

    public function update(Chat $chat, Message $message, Request $request)
    {
        try {
            $this->authorize('update', $message);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $message->body = $validated['body'];
        $message->edited_at = now();
        $message->save();

        $message->load('user.mainAvatar', 'media', 'replyTo.user', 'chat.users');

        broadcast(new MessageEdited($message))->toOthers();

        return new MessageResource($message);
    }
}
