<?php

namespace App\Providers;

use App\Repositories\TaskRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\CommentRepository;
use App\Repositories\NotificationRepository;
use App\Services\TaskService;
use App\Services\ProjectService;
use App\Services\CommentService;
use App\Services\NotificationService;
use App\Services\CacheService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repositories
        $this->app->bind(TaskRepository::class, function ($app) {
            return new TaskRepository(new \App\Models\Task());
        });

        $this->app->bind(ProjectRepository::class, function ($app) {
            return new ProjectRepository(new \App\Models\Project());
        });

        $this->app->bind(CommentRepository::class, function ($app) {
            return new CommentRepository(new \App\Models\Comment());
        });

        $this->app->bind(NotificationRepository::class, function ($app) {
            return new NotificationRepository(new \App\Models\Notification());
        });

        // Bind services
        $this->app->singleton(CacheService::class, function ($app) {
            return new CacheService();
        });

        $this->app->bind(TaskService::class, function ($app) {
            return new TaskService(
                $app->make(TaskRepository::class),
                $app->make(CacheService::class)
            );
        });

        $this->app->bind(ProjectService::class, function ($app) {
            return new ProjectService(
                $app->make(ProjectRepository::class)
            );
        });

        $this->app->bind(CommentService::class, function ($app) {
            return new CommentService(
                $app->make(CommentRepository::class)
            );
        });

        $this->app->bind(NotificationService::class, function ($app) {
            return new NotificationService(
                $app->make(NotificationRepository::class),
                $app->make(CacheService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}