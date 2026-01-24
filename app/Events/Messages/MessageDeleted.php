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
        public int      $deletedMessageId,
        public int      $originalUserId,
        public int      $chatId,
        public ?Message $message,
        public bool     $isMessageUnread,
        public          $chatUsers,
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
        $channels = [
            new PrivateChannel('chat.' . $this->chatId),
        ];

        foreach ($this->chatUsers as $user) {
            $channels[] = new PrivateChannel('user.' . $user->id);
        }


        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }

    public function broadcastWith(): array
    {

        return [
            'messageId' => $this->deletedMessageId,
            'sidebar' => [
                'chatId' => $this->chatId,
                'lastMessage' => $this->resolveMessage($this->message),
                'unreadCountDecrement' => $this->isMessageUnread ? 1 : 0,
            ],
        ];
    }

    private function resolveMessage($message){
        if(!$message){
            return null;
        }
        $resource = new MessageResource($message);
        $resource->chatUsers = $this->chatUsers;
        return $resource->resolve();
    }
}
