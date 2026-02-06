<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    public function toArray($request): array
    {
        $authId = auth()->id();
        $type = $this->resolveType();

        return [
            'id' => $this->id,
            'type' => $type, // self | private | group
            'settings' => [
                'isMuted' => $this->is_muted,
                'isPinned' => $this->is_pinned,
            ],

            'header' => [
                'title'  => $this->resolveTitle($type, $authId),
                'avatar' => $this->resolveAvatar($type, $authId),
                'link'   => $this->resolveHeaderLink($type, $authId),
                'friendUserId' => $this->resolveFriendUserId($type, $authId),
            ],

            'participants' => $this->users->map(fn ($user) => [
                'id' => $user->id,
                'username' => $user->username,
            ])->values(),
        ];
    }

    protected function resolveFriendUserId(string $type, int $authId): ?int
    {
        if ($type !== 'private') {
            return null;
        }

        return $this->users->firstWhere('id', '!=', $authId)?->id;
    }

    protected function resolveType(): string
    {
        if ($this->is_self) return 'self';
        if ($this->is_group) return 'group';
        return 'private';
    }

    protected function resolveTitle(string $type, int $authId): string
    {
        return match ($type) {
            'self' => 'Saved messages',
            'group' => $this->title ?? 'Group chat',
            'private' => optional(
                $this->users->firstWhere('id', '!=', $authId)
            )?->username ?? 'Unknown',
        };
    }

    protected function resolveAvatar(string $type, int $authId): ?string
    {
        if ($type === 'group') {
            $path = $this->chatAvatars
                ->firstWhere('is_main', 1)?->path;

            return $path ? asset('storage/' . $path) : null;
        }

        $user = $this->users->firstWhere('id', '!=', $authId)
            ?? $this->users->firstWhere('id', $authId);

        return $user?->mainAvatar
            ? asset('storage/' . $user->mainAvatar->path)
            : null;
    }

    protected function resolveHeaderLink(string $type, int $authId): array
    {
        return match ($type) {
            'self' => [
                'route' => 'profile.index',
                'params' => [],
            ],

            'private' => [
                'route' => 'otherProfile.index',
                'params' => [
                    $this->users->firstWhere('id', '!=', $authId)?->id,
                ],
            ],

            'group' => [
                'route' => 'chat.settings',
                'params' => [$this->id],
            ],
        };
    }
}
