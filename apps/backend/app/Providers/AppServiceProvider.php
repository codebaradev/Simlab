<?php

namespace App\Providers;

use App\Services\ApplicationService;
use App\Services\ComputerService;
use App\Services\DepartmentService;
use App\Services\RoomService;
use App\Services\ScheduleRequestService;
use App\Services\ScheduleService;
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

        $this->app->singleton(RoomService::class, function ($app) {
            return new RoomService();
        });
        $this->app->singleton(ComputerService::class, function ($app) {
            return new ComputerService();
        });
        $this->app->singleton(ApplicationService::class, function ($app) {
            return new ApplicationService();
        });
        $this->app->singleton(ScheduleService::class, function ($app) {
            return new ScheduleService();
        });
        $this->app->singleton(ScheduleRequestService::class, function ($app) {
            return new ScheduleRequestService(
                $app->make(ScheduleService::class)
            );
        });
    }
}
