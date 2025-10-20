<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EnrollmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // ADMIN and TEACHER can view enrollments
        return in_array($user->role, ['ADMIN', 'TEACHER']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Enrollment $enrollment): bool
    {
        // ADMIN can view any enrollment
        if ($user->role === 'ADMIN') {
            return true;
        }

        // TEACHER can view enrollments for their courses
        if ($user->role === 'TEACHER') {
            return $enrollment->course->teacher_id === $user->id;
        }

        // STUDENT can view their own enrollments
        if ($user->role === 'STUDENT') {
            return $enrollment->student_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // STUDENT can enroll themselves, ADMIN can enroll anyone
        return in_array($user->role, ['STUDENT', 'ADMIN']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Enrollment $enrollment): bool
    {
        // ADMIN can update any enrollment
        if ($user->role === 'ADMIN') {
            return true;
        }

        // TEACHER can update enrollments for their courses (e.g., approve/reject)
        if ($user->role === 'TEACHER') {
            return $enrollment->course->teacher_id === $user->id;
        }

        // STUDENT can update their own enrollment status (e.g., withdraw)
        if ($user->role === 'STUDENT') {
            return $enrollment->student_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Enrollment $enrollment): bool
    {
        // ADMIN can delete any enrollment
        if ($user->role === 'ADMIN') {
            return true;
        }

        // TEACHER can remove students from their courses
        if ($user->role === 'TEACHER') {
            return $enrollment->course->teacher_id === $user->id;
        }

        // STUDENT can withdraw from their own enrollment
        if ($user->role === 'STUDENT') {
            return $enrollment->student_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can enroll in a specific course.
     */
    public function enroll(User $user, $courseId): bool
    {
        // Only STUDENT role can enroll in courses
        if ($user->role !== 'STUDENT') {
            return false;
        }

        // Check if user is not already enrolled
        return !Enrollment::where('student_id', $user->id)
                         ->where('course_id', $courseId)
                         ->exists();
    }

    /**
     * Determine whether the user can view enrollment statistics.
     */
    public function viewStatistics(User $user): bool
    {
        // ADMIN and TEACHER can view enrollment statistics
        return in_array($user->role, ['ADMIN', 'TEACHER']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Enrollment $enrollment): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Enrollment $enrollment): bool
    {
        return $user->role === 'ADMIN';
    }
}
