<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $activities = $request->user()->activityFeed();

        return response()->json($activities);
    }

    public function userActivities(Request $request): JsonResponse
    {
        $activities = $request->user()
            ->activities()
            ->with(['subject'])
            ->latest()
            ->paginate();

        return response()->json($activities);
    }
} 