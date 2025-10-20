<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Certificate;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Policies\CoursePolicy;
use App\Policies\UserPolicy;
use App\Policies\QuizPolicy;
use App\Policies\CertificatePolicy;
use App\Policies\ModulePolicy;
use App\Policies\LessonPolicy;
use App\Policies\EnrollmentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Course::class => CoursePolicy::class,
        User::class => UserPolicy::class,
        Quiz::class => QuizPolicy::class,
        Certificate::class => CertificatePolicy::class,
        Module::class => ModulePolicy::class,
        Lesson::class => LessonPolicy::class,
        Enrollment::class => EnrollmentPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define additional gates for specific actions
        Gate::define('admin-only', function (User $user) {
            return $user->role === 'ADMIN';
        });

        Gate::define('teacher-or-admin', function (User $user) {
            return in_array($user->role, ['TEACHER', 'ADMIN']);
        });

        Gate::define('student-only', function (User $user) {
            return $user->role === 'STUDENT';
        });

        // Gate for system administration
        Gate::define('manage-system', function (User $user) {
            return $user->role === 'ADMIN';
        });

        // Gate for course enrollment management
        Gate::define('manage-enrollments', function (User $user) {
            return in_array($user->role, ['TEACHER', 'ADMIN']);
        });
    }
}
