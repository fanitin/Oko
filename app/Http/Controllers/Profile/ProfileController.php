<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserAvatarResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index(){
        $user = new UserResource(auth()->user()->load('mainAvatar', 'avatars'));
        return Inertia::render('profile/Index', ['user' => $user->resolve()]);
    }
}
