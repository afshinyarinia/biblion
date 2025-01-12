<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReadingGoal\StoreReadingGoalRequest;
use App\Http\Requests\Api\V1\ReadingGoal\UpdateReadingGoalRequest;
use App\Models\ReadingGoal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadingGoalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $goals = $request->user()
            ->readingGoals()
            ->orderBy('year', 'desc')
            ->get();

        return response()->json($goals);
    }

    public function store(StoreReadingGoalRequest $request): JsonResponse
    {
        $goal = $request->user()->readingGoals()->create($request->validated());

        return response()->json($goal, 201);
    }

    public function show(ReadingGoal $readingGoal): JsonResponse
    {
        if ($readingGoal->user_id !== request()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($readingGoal);
    }

    public function update(UpdateReadingGoalRequest $request, ReadingGoal $readingGoal): JsonResponse
    {
        $readingGoal->update($request->validated());

        // Check if the goal has been completed
        if (!$readingGoal->is_completed) {
            $booksCompleted = $readingGoal->books_read >= $readingGoal->target_books;
            $pagesCompleted = $readingGoal->pages_read >= $readingGoal->target_pages;

            if ($booksCompleted && $pagesCompleted) {
                $readingGoal->update(['is_completed' => true]);
            }
        }

        return response()->json($readingGoal);
    }

    public function destroy(ReadingGoal $readingGoal): JsonResponse
    {
        if ($readingGoal->user_id !== request()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $readingGoal->delete();

        return response()->json(null, 204);
    }

    public function current(Request $request): JsonResponse
    {
        $currentYear = date('Y');
        
        $goal = $request->user()
            ->readingGoals()
            ->where('year', $currentYear)
            ->first();

        if (!$goal) {
            return response()->json(['message' => 'No reading goal set for the current year'], 404);
        }

        return response()->json($goal);
    }
} 