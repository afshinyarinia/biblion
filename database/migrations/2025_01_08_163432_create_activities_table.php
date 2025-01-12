<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // e.g., 'started_reading', 'finished_reading', 'reviewed', 'added_to_shelf'
            $table->morphs('subject'); // Polymorphic relationship to the activity subject (Book, Review, Shelf, etc.)
            $table->json('metadata')->nullable(); // Additional data about the activity
            $table->timestamps();

            // Index for efficient querying of user activities and feed generation
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
}; 