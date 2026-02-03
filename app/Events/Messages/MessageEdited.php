<?php

namespace App\Events\Messages;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageEdited implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Message $message)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('chat.' . $this->message->chat_id),
        ];

        foreach ($this->message->chat->users as $user) {
            $channels[] = new PrivateChannel('user.' . $user->id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'message.edited';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => new MessageResource($this->message),
            'sidebar' => [
                'chatId' => $this->message->chat_id,
                'senderId' => $this->message->chat->lastMessage->id === $this->message->id ? $this->message->user_id : null,
                'lastMessage' => $this->message->chat->lastMessage->id === $this->message->id ? (new MessageResource($this->message))->resolve() : null,
            ],
        ];
    }
}
