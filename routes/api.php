<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\ReadingProgressController;
use App\Http\Controllers\Api\V1\ShelfController;
use App\Http\Controllers\Api\V1\BookReviewController;
use App\Http\Controllers\Api\V1\ReadingGoalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API V1 Routes
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Auth routes
    Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');

    // Public routes
    Route::get('books', [BookController::class, 'index'])->name('books.index');
    Route::get('books/search', [BookController::class, 'search'])->name('books.search');
    Route::get('books/{book}', [BookController::class, 'show'])->name('books.show');
    Route::get('shelves/{shelf}', [ShelfController::class, 'show'])->name('shelves.show');
    Route::get('books/{book}/reviews', [BookReviewController::class, 'index'])->name('books.reviews.index');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/user', [AuthController::class, 'user'])->name('auth.user');

        // Books management
        Route::post('books', [BookController::class, 'store'])->name('books.store');
        Route::put('books/{book}', [BookController::class, 'update'])->name('books.update');
        Route::delete('books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

        // Shelves management
        Route::apiResource('shelves', ShelfController::class)->except(['show']);
        Route::post('shelves/{shelf}/books', [ShelfController::class, 'addBook'])->name('shelves.books.add');
        Route::delete('shelves/{shelf}/books/{book}', [ShelfController::class, 'removeBook'])->name('shelves.books.remove');

        // Reading Progress
        Route::get('reading-progress', [ReadingProgressController::class, 'index'])->name('reading-progress.index');
        Route::get('reading-progress/statistics', [ReadingProgressController::class, 'statistics'])->name('reading-progress.statistics');
        Route::get('reading-progress/books/{book}', [ReadingProgressController::class, 'show'])->name('reading-progress.show');
        Route::put('reading-progress/books/{book}', [ReadingProgressController::class, 'update'])->name('reading-progress.update');

        // Book Reviews
        Route::post('books/{book}/reviews', [BookReviewController::class, 'store'])->name('books.reviews.store');
        Route::put('books/{book}/reviews/{review}', [BookReviewController::class, 'update'])->name('books.reviews.update');
        Route::delete('books/{book}/reviews/{review}', [BookReviewController::class, 'destroy'])->name('books.reviews.destroy');
        Route::get('user/reviews', [BookReviewController::class, 'userReviews'])->name('user.reviews');

        // Reading Goals
        Route::get('reading-goals/current', [ReadingGoalController::class, 'current'])->name('reading-goals.current');
        Route::apiResource('reading-goals', ReadingGoalController::class);
    });
}); 