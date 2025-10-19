<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider {
    public function register() {
        // Binding Repositories
        $this->app->bind(\App\Repositories\UserRepository::class, \App\Repositories\UserRepository::class);
        $this->app->bind(\App\Repositories\CourseRepository::class, \App\Repositories\CourseRepository::class);
        $this->app->bind(\App\Repositories\EnrollmentRepository::class, \App\Repositories\EnrollmentRepository::class);
        $this->app->bind(\App\Repositories\CertificateRepository::class, \App\Repositories\CertificateRepository::class);
        $this->app->bind(\App\Repositories\QuizRepository::class, \App\Repositories\QuizRepository::class);
        $this->app->bind(\App\Repositories\QuizAttemptRepository::class, \App\Repositories\QuizAttemptRepository::class);
        $this->app->bind(\App\Repositories\SystemLogRepository::class, \App\Repositories\SystemLogRepository::class);
    }
}