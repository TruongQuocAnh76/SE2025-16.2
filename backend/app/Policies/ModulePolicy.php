<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Auth\Access\Response;

class ModulePolicy
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
    public function view(User $user, Module $module): bool
    {
        // ADMIN can view any module
        if ($user->role === 'ADMIN') {
            return true;
        }

        // TEACHER can view modules in their own courses
        if ($user->role === 'TEACHER') {
            return $module->course->teacher_id === $user->id;
        }

        // STUDENT can view modules in courses they are enrolled in
        if ($user->role === 'STUDENT') {
            return Enrollment::where('student_id', $user->id)
                           ->where('course_id', $module->course_id)
                           ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only TEACHER and ADMIN can create modules
        return in_array($user->role, ['TEACHER', 'ADMIN']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Module $module): bool
    {
        // ADMIN can update any module, TEACHER can only update modules in their own courses
        return $user->role === 'ADMIN' || 
               ($user->role === 'TEACHER' && $module->course->teacher_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Module $module): bool
    {
        // ADMIN can delete any module, TEACHER can only delete modules in their own courses
        return $user->role === 'ADMIN' || 
               ($user->role === 'TEACHER' && $module->course->teacher_id === $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Module $module): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Module $module): bool
    {
        return $user->role === 'ADMIN';
    }
}
