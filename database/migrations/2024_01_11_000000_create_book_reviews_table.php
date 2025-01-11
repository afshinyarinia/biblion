<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->comment('Rating from 1 to 5');
            $table->text('review')->nullable();
            $table->boolean('contains_spoilers')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // A user can only review a book once
            $table->unique(['user_id', 'book_id']);

            // Add indexes for common queries
            $table->index(['book_id', 'rating']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_reviews');
    }
}; 