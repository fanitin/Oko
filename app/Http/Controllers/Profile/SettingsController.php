<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserAvatarResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function uploadAvatar(Request $request)
    {
        $data = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:11000',
        ]);
        $file = $request->file('avatar');
        $user = auth()->user();
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('users/avatars/' . $user->id, $filename, 'public');

        $lastMain = $user->avatars()->where('is_main', 1);
        $lastMain->update(['is_main' => 0]);
        $user->avatars()->create([
            'path' => $path,
            'is_main' => 1,
        ]);

        $avatars = $user->avatars()->get();

        return response()->json([
            'success' => true,
            'avatars' => UserAvatarResource::collection($avatars),
        ]);
    }
}
