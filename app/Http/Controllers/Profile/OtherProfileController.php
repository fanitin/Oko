<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserAvatarResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserAvatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class OtherProfileController extends Controller
{
    public function index(User $user)
    {
        $user = new UserResource($user->load('mainAvatar', 'avatars'));
        $chatID = $user->chats()
            ->whereHas('users', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->first()?->id;
        return Inertia::render('profile/other/Index', ['user' => $user->resolve(), 'chatID' => $chatID]);
    }
}
