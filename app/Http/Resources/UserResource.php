<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'main_avatar' => $this->mainAvatar
                ? (new UserAvatarResource($this->mainAvatar))->toArray($request)
                : null,
            'avatars' => $this->avatars
                ? UserAvatarResource::collection($this->avatars)->toArray($request)
                : [],
        ];
        //        return [
//            'id' => $this->id,
//            'username' => $this->username,
//            'email' => $this->email,
//            'main_avatar' => [
//                'id' => $this->mainAvatar->id,
//                'path' => "/storage/".$this->mainAvatar->path,
//            ],
//            'avatars' => $this->avatars->map(function($a) {
//                return [
//                    'id' => $a->id,
//                    'path' => "/storage/".$a->path,
//                ];
//            }),
//        ];
    }
}
