<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserAvatarResource;
use App\Http\Resources\UserResource;
use App\Models\UserAvatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SelfProfileController extends Controller
{
    public function index(){
        $user = auth()->user()->load('mainAvatar', 'avatars');
        $userRes = new UserResource($user);
        $chatID = $user->chats()
            ->where('is_self', true)
            ->whereHas('users', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->first()?->id;
        return Inertia::render('profile/self/Index', ['user' => $userRes->resolve(), 'chatID' => $chatID]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:11000',
        ]);
        $file = $request->file('avatar');
        $user = auth()->user();
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('users/avatars/' . $user->id, $filename, 'public');

        $user->avatars()->where('is_main', 1)->update(['is_main' => 0]);
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

    public function setAsMain(Request $request){
        $id = $request->input('id');
        $user = auth()->user();

        UserAvatar::forUser($user)->where('is_main', 1)->update(['is_main' => 0]);
        $avatar = UserAvatar::where('id', $id)->first();
        $avatar->update(['is_main' => 1]);
        $avatars = $user->avatars()->get();
        return response()->json([
            "success" => true,
            "avatars" => UserAvatarResource::collection($avatars),
        ]);
    }

    public function deleteAvatar(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:user_avatars,id',
        ]);

        $user = auth()->user();

        $avatar = UserAvatar::forUser($user)->where('id', $request->input('id'))->first();

        if (!$avatar) {
            return response()->json([
                "success" => false,
                "message" => "Avatar not found",
            ], 404);
        }

        Storage::disk('public')->delete($avatar->path);

        $avatar->delete();

        $nextAvatar = $user->avatars()->first();
        if ($nextAvatar) {
            $nextAvatar->update(['is_main' => 1]);
        }

        $avatars = $user->avatars()->get();

        return response()->json([
            "success" => true,
            "avatars" => UserAvatarResource::collection($avatars),
        ]);
    }

    public function updateUsername(Request $request)
    {
        try {
            $data = $request->validate([
                'username' => 'required|string|alpha_num|max:255|unique:users,username,' . auth()->id(),
            ]);

            $user = auth()->user();
            $user->update(['username' => $data['username']]);

            return response()->json([
                "success" => true,
                "message" => "Username updated",
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->errors()['username'][0] ?? 'Validation error',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Unexpected error",
            ], 500);
        }
    }
}
