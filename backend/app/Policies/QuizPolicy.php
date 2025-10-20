<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Auth\Access\Response;

class QuizPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view quizzes (with course restrictions)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Quiz $quiz): bool
    {
        // ADMIN can view any quiz
        if ($user->role === 'ADMIN') {
            return true;
        }

        // TEACHER can view quizzes in their own courses
        if ($user->role === 'TEACHER') {
            return $quiz->course->teacher_id === $user->id;
        }

        // STUDENT can view quizzes in courses they are enrolled in
        if ($user->role === 'STUDENT') {
            return Enrollment::where('student_id', $user->id)
                           ->where('course_id', $quiz->course_id)
                           ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Course $course = null): bool
    {
        // Only TEACHER and ADMIN can create quizzes
        if (!in_array($user->role, ['TEACHER', 'ADMIN'])) {
            return false;
        }

        // If course is provided, check ownership
        if ($course) {
            return $user->role === 'ADMIN' || 
                   ($user->role === 'TEACHER' && $course->teacher_id === $user->id);
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Quiz $quiz): bool
    {
        // ADMIN can update any quiz, TEACHER can only update quizzes in their own courses
        return $user->role === 'ADMIN' || 
               ($user->role === 'TEACHER' && $quiz->course->teacher_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Quiz $quiz): bool
    {
        // ADMIN can delete any quiz, TEACHER can only delete quizzes in their own courses
        return $user->role === 'ADMIN' || 
               ($user->role === 'TEACHER' && $quiz->course->teacher_id === $user->id);
    }

    /**
     * Determine whether the user can take/attempt the quiz.
     */
    public function attempt(User $user, Quiz $quiz): bool
    {
        // Only STUDENT can attempt quizzes and must be enrolled in the course
        if ($user->role !== 'STUDENT') {
            return false;
        }

        return Enrollment::where('student_id', $user->id)
                        ->where('course_id', $quiz->course_id)
                        ->exists();
    }

    /**
     * Determine whether the user can view quiz results.
     */
    public function viewResults(User $user, Quiz $quiz): bool
    {
        // ADMIN can view any quiz results
        if ($user->role === 'ADMIN') {
            return true;
        }

        // TEACHER can view results for their course quizzes
        if ($user->role === 'TEACHER') {
            return $quiz->course->teacher_id === $user->id;
        }

        // STUDENT can view their own quiz results if enrolled
        if ($user->role === 'STUDENT') {
            return Enrollment::where('student_id', $user->id)
                           ->where('course_id', $quiz->course_id)
                           ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Quiz $quiz): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Quiz $quiz): bool
    {
        return $user->role === 'ADMIN';
    }
}
