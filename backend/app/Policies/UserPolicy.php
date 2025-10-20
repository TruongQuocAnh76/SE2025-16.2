<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only ADMIN can list all users
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view their own profile, ADMIN can view any profile
        return $user->id === $model->id || $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only ADMIN can create users (registration is handled separately)
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update their own profile, ADMIN can update any profile
        return $user->id === $model->id || $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Only ADMIN can delete users, but cannot delete themselves
        return $user->role === 'ADMIN' && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can filter users by role.
     */
    public function filterByRole(User $user): bool
    {
        // Only ADMIN and TEACHER can filter users by role
        return in_array($user->role, ['ADMIN', 'TEACHER']);
    }

    /**
     * Determine whether the user can view user certificates.
     */
    public function viewCertificates(User $user, User $model): bool
    {
        // Users can view their own certificates, ADMIN and TEACHER can view any certificates
        return $user->id === $model->id || in_array($user->role, ['ADMIN', 'TEACHER']);
    }

    /**
     * Determine whether the user can view user reviews.
     */
    public function viewReviews(User $user, User $model): bool
    {
        // Users can view their own reviews, ADMIN and TEACHER can view any reviews
        return $user->id === $model->id || in_array($user->role, ['ADMIN', 'TEACHER']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->role === 'ADMIN';
    }
}
