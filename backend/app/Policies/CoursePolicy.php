<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view courses
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Course $course): bool
    {
        // All authenticated users can view individual courses
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only TEACHER and ADMIN roles can create courses
        return in_array($user->role, ['TEACHER', 'ADMIN']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Course $course): bool
    {
        // ADMIN can update any course, TEACHER can only update their own courses
        return $user->role === 'ADMIN' || 
               ($user->role === 'TEACHER' && $course->teacher_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Course $course): bool
    {
        // ADMIN can delete any course, TEACHER can only delete their own courses
        return $user->role === 'ADMIN' || 
               ($user->role === 'TEACHER' && $course->teacher_id === $user->id);
    }

    /**
     * Determine whether the user can enroll in the course.
     */
    public function enroll(User $user, Course $course): bool
    {
        // Only STUDENT role can enroll in courses
        // Teachers cannot enroll in courses, they create them
        return $user->role === 'STUDENT';
    }

    /**
     * Determine whether the user can view enrolled students.
     */
    public function viewStudents(User $user, Course $course): bool
    {
        // ADMIN can view students of any course, TEACHER can only view students of their own courses
        return $user->role === 'ADMIN' || 
               ($user->role === 'TEACHER' && $course->teacher_id === $user->id);
    }

    /**
     * Determine whether the user can manage course modules and lessons.
     */
    public function manageContent(User $user, Course $course): bool
    {
        // ADMIN can manage any course content, TEACHER can only manage their own courses
        return $user->role === 'ADMIN' || 
               ($user->role === 'TEACHER' && $course->teacher_id === $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Course $course): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        return $user->role === 'ADMIN';
    }
}
