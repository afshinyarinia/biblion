<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $followers = $request->user()
            ->followers()
            ->select(['users.id', 'users.name', 'users.email'])
            ->withCount(['followers', 'following'])
            ->orderBy('followers.created_at', 'desc')
            ->get();

        return response()->json($followers);
    }

    public function following(Request $request): JsonResponse
    {
        $following = $request->user()
            ->following()
            ->select(['users.id', 'users.name', 'users.email'])
            ->withCount(['followers', 'following'])
            ->orderBy('followers.created_at', 'desc')
            ->get();

        return response()->json($following);
    }

    public function store(Request $request, User $user): JsonResponse
    {
        // Cannot follow yourself
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'You cannot follow yourself'], 422);
        }

        // Check if already following
        if ($request->user()->isFollowing($user)) {
            return response()->json(['message' => 'You are already following this user'], 422);
        }

        $request->user()->following()->attach($user);

        return response()->json([
            'message' => 'Successfully followed user',
            'user' => $user->load(['followers', 'following']),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        // Cannot unfollow yourself
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'You cannot unfollow yourself'], 422);
        }

        // Check if not following
        if (!$request->user()->isFollowing($user)) {
            return response()->json(['message' => 'You are not following this user'], 422);
        }

        $request->user()->following()->detach($user);

        return response()->json([
            'message' => 'Successfully unfollowed user',
            'user' => $user->load(['followers', 'following']),
        ]);
    }
} 