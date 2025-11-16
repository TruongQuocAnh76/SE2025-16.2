<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\CourseRepository;

class RecommendationService
{
    protected $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Get course recommendations for a user.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getRecommendations(User $user)
    {
        // Check if user has any enrollments
        if ($user->enrollments->isEmpty()) {
            // New user: return popular courses
            return $this->courseRepository->getPopularCourses();
        }

        // Existing user: return content-based recommendations
        return $this->courseRepository->getContentBasedRecommendations($user);
    }
}
