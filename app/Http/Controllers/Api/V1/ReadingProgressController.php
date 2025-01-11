<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReadingProgress\UpdateReadingProgressRequest;
use App\Models\Book;
use App\Models\ReadingProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ReadingProgressController extends Controller
{
    /**
     * Get reading progress for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $progress = ReadingProgress::query()
            ->with('book')
            ->where('user_id', Auth::id())
            ->when(
                $request->get('status'),
                fn ($query) => $query->where('status', $request->status)
            )
            ->latest('updated_at')
            ->paginate(15);

        return response()->json($progress);
    }

    /**
     * Get reading progress for a specific book.
     */
    public function show(Book $book): JsonResponse
    {
        $progress = ReadingProgress::query()
            ->where('user_id', Auth::id())
            ->where('book_id', $book->id)
            ->firstOrFail();

        return response()->json($progress);
    }

    /**
     * Start or update reading progress for a book.
     */
    public function update(UpdateReadingProgressRequest $request, Book $book): JsonResponse
    {
        $progress = ReadingProgress::query()
            ->firstOrCreate(
                [
                    'user_id' => Auth::id(),
                    'book_id' => $book->id,
                ],
                [
                    'status' => 'not_started',
                    'current_page' => 0,
                    'reading_time_minutes' => 0,
                ]
            );

        $progress->update($request->validated());

        return response()->json($progress);
    }

    /**
     * Get reading statistics for the authenticated user.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = [
            'total_books' => $user->readingProgress()->count(),
            'completed_books' => $user->readingProgress()->where('status', 'completed')->count(),
            'in_progress_books' => $user->readingProgress()->where('status', 'in_progress')->count(),
            'total_pages_read' => $user->readingProgress()->sum('current_page'),
            'total_reading_time' => $user->readingProgress()->sum('reading_time_minutes'),
        ];

        return response()->json($stats);
    }

    /**
     * Calculate the current reading streak in days.
     */
    private function calculateReadingStreak(int $userId): int
    {
        $progress = ReadingProgress::where('user_id', $userId)
            ->where('updated_at', '>=', now()->subDays(30))
            ->orderBy('updated_at', 'desc')
            ->get()
            ->groupBy(fn ($item) => $item->updated_at->format('Y-m-d'));

        $streak = 0;
        $date = now();

        while ($progress->has($date->format('Y-m-d'))) {
            $streak++;
            $date = $date->subDay();
        }

        return $streak;
    }
} 