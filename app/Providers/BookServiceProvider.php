<?php

namespace App\Providers;

use App\Repositories\Book\BookRepository;
use App\Repositories\Book\Contracts\BookRepositoryInterface;
use App\Services\Book\BookService;
use App\Services\Book\Contracts\BookServiceInterface;
use Illuminate\Support\ServiceProvider;

class BookServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BookRepositoryInterface::class, BookRepository::class);
        $this->app->bind(BookServiceInterface::class, BookService::class);
    }

    public function boot(): void
    {
        //
    }
} 