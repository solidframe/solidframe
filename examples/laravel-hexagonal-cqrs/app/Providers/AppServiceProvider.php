<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Project\Port\ProjectRepository;
use App\Domain\Task\Port\TaskRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentProjectRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentTaskRepository;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProjectRepository::class, EloquentProjectRepository::class);
        $this->app->bind(TaskRepository::class, EloquentTaskRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
