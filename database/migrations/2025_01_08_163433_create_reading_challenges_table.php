<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->json('requirements'); // Store challenge requirements (e.g., book categories, counts)
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['start_date', 'end_date']);
            $table->index('is_public');
            $table->index('is_featured');
        });

        Schema::create('reading_challenge_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reading_challenge_id')->constrained()->cascadeOnDelete();
            $table->json('progress'); // Track progress for each requirement
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'reading_challenge_id']);
            $table->index('is_completed');
        });

        Schema::create('reading_challenge_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reading_challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('requirement_key'); // Which challenge requirement this book fulfills
            $table->timestamps();

            $table->unique(['user_id', 'reading_challenge_id', 'book_id']);
            $table->index(['reading_challenge_id', 'requirement_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_challenge_books');
        Schema::dropIfExists('reading_challenge_participants');
        Schema::dropIfExists('reading_challenges');
    }
}; 