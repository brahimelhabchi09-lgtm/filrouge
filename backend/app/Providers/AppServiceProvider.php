<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\RejectTeacherReason\Repository\RejectTeacherReasonRepository;
use App\Infrastructure\Persistence\Eloquent\Repository\RejectTeacherReasonRepository as EloquentRejectTeacherReasonRepository;
use App\Domain\User\Repository\UserRepository;
use App\Infrastructure\Persistence\Eloquent\Repository\UserRepository as EloquentUserRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            RejectTeacherReasonRepository::class,
            EloquentRejectTeacherReasonRepository::class
        );

        $this->app->bind(
            UserRepository::class,
            EloquentUserRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
