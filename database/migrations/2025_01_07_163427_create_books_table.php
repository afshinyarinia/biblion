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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('isbn')->unique();
            $table->text('description')->nullable();
            $table->integer('total_pages');
            $table->string('cover_image')->nullable();
            $table->string('publisher')->nullable();
            $table->date('publication_date')->nullable();
            $table->string('language')->default('en');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for frequently searched columns
            $table->index(['title', 'author']);
            $table->index('isbn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
