<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('following_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // A user can only follow another user once
            $table->unique(['follower_id', 'following_id']);

            // Index for efficient queries
            $table->index(['follower_id', 'created_at']);
            $table->index(['following_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('followers');
    }
}; 