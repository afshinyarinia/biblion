<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('book_shelf', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('shelf_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure a book can only be added once to a shelf
            $table->unique(['book_id', 'shelf_id']);

            // Indexes for faster lookups
            $table->index('book_id');
            $table->index('shelf_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_shelf');
    }
};
