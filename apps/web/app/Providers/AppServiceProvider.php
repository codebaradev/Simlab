<?php

namespace App\Providers;

use App\Services\DepartmentService;
use App\Services\StudentService;
use App\Services\StudyProgramService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(UserService::class, function ($app) {
            return new UserService();
        });

        $this->app->singleton(DepartmentService::class, function ($app) {
            return new DepartmentService();
        });

        $this->app->singleton(StudyProgramService::class, function ($app) {
            return new StudyProgramService();
        });

        $this->app->singleton(StudentService::class, function ($app) {
            return new StudentService();
        });
    }
}
