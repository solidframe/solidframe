<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Book\BookRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentBookRepository;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BookRepository::class, EloquentBookRepository::class);
    }

    public function boot(): void {}
}
