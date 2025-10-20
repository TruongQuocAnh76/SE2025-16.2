<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Auth\Access\Response;

class LessonPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Lesson $lesson): bool
    {
        // ADMIN can view any lesson
        if ($user->role === 'ADMIN') {
            return true;
        }

        // TEACHER can view lessons in their own courses
        if ($user->role === 'TEACHER') {
            return $lesson->module->course->teacher_id === $user->id;
        }

        // STUDENT can view lessons in courses they are enrolled in
        // Free lessons can be viewed by anyone enrolled
        if ($user->role === 'STUDENT') {
            $courseId = $lesson->module->course_id;
            $isEnrolled = Enrollment::where('student_id', $user->id)
                                  ->where('course_id', $courseId)
                                  ->exists();
            
            // If lesson is free, enrolled students can view it
            // If lesson is paid, they need full enrollment
            return $isEnrolled && ($lesson->is_free || $lesson->module->course->price > 0);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only TEACHER and ADMIN can create lessons
        return in_array($user->role, ['TEACHER', 'ADMIN']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Lesson $lesson): bool
    {
        // ADMIN can update any lesson, TEACHER can only update lessons in their own courses
        return $user->role === 'ADMIN' || 
               ($user->role === 'TEACHER' && $lesson->module->course->teacher_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lesson $lesson): bool
    {
        // ADMIN can delete any lesson, TEACHER can only delete lessons in their own courses
        return $user->role === 'ADMIN' || 
               ($user->role === 'TEACHER' && $lesson->module->course->teacher_id === $user->id);
    }

    /**
     * Determine whether the user can access lesson content.
     */
    public function accessContent(User $user, Lesson $lesson): bool
    {
        // Free lessons can be accessed by enrolled students
        if ($lesson->is_free && $user->role === 'STUDENT') {
            return Enrollment::where('student_id', $user->id)
                           ->where('course_id', $lesson->module->course_id)
                           ->exists();
        }

        // Paid lessons require full course enrollment
        return $this->view($user, $lesson);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Lesson $lesson): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Lesson $lesson): bool
    {
        return $user->role === 'ADMIN';
    }
}
