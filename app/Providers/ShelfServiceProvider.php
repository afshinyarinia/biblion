<?php

namespace App\Providers;

use App\Repositories\Shelf\Contracts\ShelfRepositoryInterface;
use App\Repositories\Shelf\ShelfRepository;
use App\Services\Shelf\Contracts\ShelfServiceInterface;
use App\Services\Shelf\ShelfService;
use Illuminate\Support\ServiceProvider;

class ShelfServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ShelfRepositoryInterface::class, ShelfRepository::class);
        $this->app->bind(ShelfServiceInterface::class, ShelfService::class);
    }

    public function boot(): void
    {
        //
    }
}
