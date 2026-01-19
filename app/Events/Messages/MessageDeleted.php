<?php

namespace App\Events\Messages;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $deletedMessageId,
        public int $originalUserId,
        public int $chatId,
        public ?Message $message,
        public bool $isMessageUnread,
    )
    {

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->chatId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }

    public function broadcastWith(): array
    {

        return [
            'messageId' => $this->deletedMessageId,
            'originalUserId' => $this->originalUserId,
            'sidebar' => [
                'chatId' => $this->message->chat_id,
                'lastMessage' => $this->message?->body,
                'unreadCountDecrement' => $this->isMessageUnread ? 1 : 0,
            ],
        ];
    }
}
