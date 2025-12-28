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

            'media' => $this->media->map(fn ($media) => [
                'id' => $media->id,
                'type' => $media->media_type,
                'path' => asset('storage/' . $media->path),
                'meta' => $media->meta,
            ]),

            'created_at' => $this->created_at,
        ];
    }
}
