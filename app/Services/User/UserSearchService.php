<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class UserSearchService
{
    public function search(string $query, int $excludeUserId, int $limit = 10): Collection
    {
        if (strlen($query) < 2) {
            return collect([]);
        }

        return User::where('id', '!=', $excludeUserId)
            ->where(function ($q) use ($query) {
                if (str_starts_with($query, '@')) {
                    $username = ltrim($query, '@');
                    $q->where('username', 'like', '%' . $username . '%');
                } else {
                    $q->where('email', 'like', '%' . $query . '%')
                        ->orWhere('username', 'like', '%' . $query . '%');
                }
            })
            ->with('mainAvatar')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->username,
                    'email' => $user->email,
                    'avatar' => $user->mainAvatar ? asset('storage/' . $user->mainAvatar?->path) : null,
                    'chatId' => $user->chats()
                        ->whereHas('users', function ($query) {
                            $query->where('user_id', Auth::id());
                        })
                        ->first()?->id,
                ];
            });
    }
}
