<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */


    public $chatUsers;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'avatar' => $this->user->mainAvatar
                    ? asset('storage/' . $this->user->mainAvatar->path)
                    : null,
            ],
            'user_id' => $this->user_id,
            'chat_id' => $this->chat_id,
            'body' => $this->body,
            'edited_at' => $this->edited_at,
            'reply_to' => $this->replyTo
                ? [
                    'id' => $this->replyTo->id,
                    'body' => $this->replyTo->body,
                    'user' => [
                        'id' => $this->replyTo->user->id,
                        'username' => $this->replyTo->user->username,
                    ],
                ]
                : null,
            'status' => $this->resolveStatus(),
            'media' => $this->media->map(fn($media) => [
                'id' => $media->id,
                'type' => $media->media_type,
                'path' => asset('storage/' . $media->path),
                'meta' => $media->meta,
            ]),
            'created_at' => $this->created_at,
        ];
    }

    protected function resolveStatus()
    {
        if (!$this->chatUsers) {
            $this->chatUsers = $this->whenLoaded('chat', function () {
                return $this->resource->chat->users;
            }, collect());
        }

        if ($this->user_id === auth()->id()) {
            $seenByAnyone = $this->chatUsers
                ->where('id', '!=', $this->user_id)
                ->where('pivot.last_read_message_id', '>=', $this->id)
                ->isNotEmpty();

            return $seenByAnyone ? 'seen' : 'delivered';
        }

        return 'delivered';
    }
}
