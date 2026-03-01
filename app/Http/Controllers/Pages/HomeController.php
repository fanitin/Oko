<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Services\Chat\ChatListService;
use App\Services\User\UserSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private UserSearchService $userSearchService,
        private ChatListService $chatListService
    ) {}

    public function index(): Response
    {
        $recentChats = $this->chatListService->getForHome()->take(20);

        return Inertia::render('pages/Index', [
            'recentChats' => $recentChats,
        ]);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $users = $this->userSearchService->search($query, Auth::id());

        return response()->json(['users' => $users]);
    }
}
