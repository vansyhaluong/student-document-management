<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Repositories\Contracts\ActivityLogRepository;
use App\Repositories\Contracts\DocumentStatusHistoryRepository;
use App\Repositories\Contracts\DocumentStatusRepository;
use App\Repositories\Contracts\DocumentTypeRepository;
use App\Repositories\Contracts\ReportRepository;
use App\Repositories\Contracts\StudentDocumentRepository;
use App\Repositories\Contracts\StudentRepository;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Eloquent\EloquentActivityLogRepository;
use App\Repositories\Eloquent\EloquentDocumentStatusHistoryRepository;
use App\Repositories\Eloquent\EloquentDocumentStatusRepository;
use App\Repositories\Eloquent\EloquentDocumentTypeRepository;
use App\Repositories\Eloquent\EloquentReportRepository;
use App\Repositories\Eloquent\EloquentStudentDocumentRepository;
use App\Repositories\Eloquent\EloquentStudentRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(StudentRepository::class, EloquentStudentRepository::class);
        $this->app->bind(
            DocumentTypeRepository::class,
            EloquentDocumentTypeRepository::class,
        );
        $this->app->bind(
            DocumentStatusRepository::class,
            EloquentDocumentStatusRepository::class,
        );
        $this->app->bind(
            StudentDocumentRepository::class,
            EloquentStudentDocumentRepository::class,
        );
        $this->app->bind(
            DocumentStatusHistoryRepository::class,
            EloquentDocumentStatusHistoryRepository::class,
        );
        $this->app->bind(ReportRepository::class, EloquentReportRepository::class);
        $this->app->bind(
            ActivityLogRepository::class,
            EloquentActivityLogRepository::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define(
            'view-reports',
            fn ($user): bool => $user->hasRole(UserRole::ADMIN, UserRole::SECRETARY),
        );
        Gate::define(
            'view-activity-log',
            fn ($user): bool => $user->hasRole(UserRole::ADMIN),
        );
    }
}
