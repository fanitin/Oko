<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MessageCollection extends ResourceCollection
{
    public $chatUsers;

    public function toArray($request)
    {
        return $this->collection->map(function ($message) use ($request) {
            $resource = new MessageResource($message);
            $resource->chatUsers = $this->chatUsers;
            return $resource->toArray($request);
        })->all();
    }

    public function with($request)
    {
        return [
            'chatUsers' => $this->chatUsers,
        ];
    }
}
