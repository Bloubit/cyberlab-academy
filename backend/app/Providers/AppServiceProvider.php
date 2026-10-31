<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AuthService;
use App\Services\CourseService;
use App\Services\LabService;
use App\Services\QuizService;
use App\Services\CertificateService;
use App\Services\PaymentService;
use App\Services\NotificationService;
use App\Services\UserService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthService::class);
        $this->app->singleton(CourseService::class);
        $this->app->singleton(LabService::class);
        $this->app->singleton(QuizService::class);
        $this->app->singleton(CertificateService::class);
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(UserService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
