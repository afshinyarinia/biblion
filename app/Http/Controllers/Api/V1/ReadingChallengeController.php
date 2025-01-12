<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReadingChallenge\StoreReadingChallengeRequest;
use App\Http\Requests\Api\V1\ReadingChallenge\UpdateReadingChallengeRequest;
use App\Models\Activity;
use App\Models\Book;
use App\Models\ReadingChallenge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReadingChallengeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ReadingChallenge::query()
            ->with('creator:id,name')
            ->withCount('participants');

        if (!$request->boolean('show_all')) {
            $query->where(function ($q) {
                $q->where('is_public', true)
                    ->orWhere('created_by', auth()->id());
            });
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        $challenges = $query->latest()->paginate();

        return response()->json($challenges);
    }

    public function store(StoreReadingChallengeRequest $request): JsonResponse
    {
        $challenge = ReadingChallenge::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        Activity::log($request->user(), Activity::TYPE_CREATED_CHALLENGE, $challenge);

        return response()->json($challenge, Response::HTTP_CREATED);
    }

    public function show(ReadingChallenge $readingChallenge): JsonResponse
    {
        if (!$readingChallenge->is_public && $readingChallenge->created_by !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $readingChallenge->load([
            'creator:id,name',
            'participants:id,name',
        ]);

        if (auth()->check()) {
            $readingChallenge->setAttribute('user_progress', $readingChallenge->userProgress(auth()->user()));
            $readingChallenge->setAttribute('is_completed', $readingChallenge->isCompletedByUser(auth()->user()));
        }

        return response()->json($readingChallenge);
    }

    public function update(UpdateReadingChallengeRequest $request, ReadingChallenge $readingChallenge): JsonResponse
    {
        $readingChallenge->update($request->validated());

        return response()->json($readingChallenge);
    }

    public function destroy(ReadingChallenge $readingChallenge): JsonResponse
    {
        // Temporary: Allow challenge creator to delete until admin system is implemented
        if ($readingChallenge->created_by !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        // TODO: Uncomment when admin system is implemented
        // if (!auth()->user()->isAdmin()) {
        //     return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        // }

        if ($readingChallenge->participants()->exists()) {
            return response()->json(
                ['message' => 'Cannot delete challenge with participants'],
                Response::HTTP_CONFLICT
            );
        }

        $readingChallenge->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function join(ReadingChallenge $readingChallenge): JsonResponse
    {
        if (!$readingChallenge->is_public && $readingChallenge->created_by !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        if (!$readingChallenge->is_active) {
            return response()->json(
                ['message' => 'This challenge is not currently active'],
                Response::HTTP_CONFLICT
            );
        }

        if ($readingChallenge->participants()->where('user_id', auth()->id())->exists()) {
            return response()->json(
                ['message' => 'Already participating in this challenge'],
                Response::HTTP_CONFLICT
            );
        }

        $readingChallenge->addParticipant(auth()->user());
        Activity::log(auth()->user(), Activity::TYPE_JOINED_CHALLENGE, $readingChallenge);

        return response()->json(['message' => 'Successfully joined the challenge']);
    }

    public function addBook(Request $request, ReadingChallenge $readingChallenge, Book $book): JsonResponse
    {
        if (!$readingChallenge->participants()->where('user_id', auth()->id())->exists()) {
            return response()->json(
                ['message' => 'Not participating in this challenge'],
                Response::HTTP_FORBIDDEN
            );
        }

        $request->validate([
            'requirement_key' => ['required', 'string', 'exists:reading_challenges,requirements->*'],
        ]);

        if ($readingChallenge->books()->where([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
        ])->exists()) {
            return response()->json(
                ['message' => 'Book already used in this challenge'],
                Response::HTTP_CONFLICT
            );
        }

        $readingChallenge->books()->create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'requirement_key' => $request->requirement_key,
        ]);

        $progress = $readingChallenge->userProgress(auth()->user());
        $progress[$request->requirement_key]++;
        $readingChallenge->updateProgress(auth()->user(), $progress);

        return response()->json(['message' => 'Book added to challenge']);
    }

    public function removeBook(ReadingChallenge $readingChallenge, Book $book): JsonResponse
    {
        $challengeBook = $readingChallenge->books()
            ->where([
                'user_id' => auth()->id(),
                'book_id' => $book->id,
            ])
            ->first();

        if (!$challengeBook) {
            return response()->json(
                ['message' => 'Book not found in this challenge'],
                Response::HTTP_NOT_FOUND
            );
        }

        $progress = $readingChallenge->userProgress(auth()->user());
        $progress[$challengeBook->requirement_key]--;
        $readingChallenge->updateProgress(auth()->user(), $progress);

        $challengeBook->delete();

        return response()->json(['message' => 'Book removed from challenge']);
    }

    public function userChallenges(Request $request): JsonResponse
    {
        $challenges = $request->user()
            ->participatingChallenges()
            ->with('creator:id,name')
            ->withCount('participants')
            ->latest()
            ->paginate();

        return response()->json($challenges);
    }
} 