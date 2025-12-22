<?php

namespace App\Repositories;

use App\Models\Course;
use App\Models\Module;

class CourseRepository
{
    protected $model;

    public function __construct(Course $model)
    {
        $this->model = $model;
    }

    /**
     * Get all courses with filters and pagination.
     * Eager loads relationships and counts for performance.
     */
    public function getAll($filters = [], $perPage = 12)
    {
        $query = $this->model->with([
            'teacher:id,first_name,last_name',
            'tags:id,name,slug'
        ])
        ->withCount('enrollments')
        ->withAvg('reviews', 'rating');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }
        if (isset($filters['keyword'])) {
            $query->where('title', 'like', "%{$filters['keyword']}%");
        }
        
        if (!empty($filters['tag'])) {
            $query->whereHas('tags', function ($tagQuery) use ($filters) {
                $tagQuery->where('slug', $filters['tag']);
            });
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Get a single course by its ID with detailed relationships.
     */
    public function getById($id)
    {
        return $this->model->with([
            'teacher:id,first_name,last_name,email',
            'modules' => function ($query) {
                $query->orderBy('order_index');
            },
            'modules.lessons' => function ($query) {
                $query->select('id', 'title', 'order_index', 'module_id', 'is_free')
                      ->orderBy('order_index');
            },
            'reviews.student:id,first_name,last_name',
            'tags:id,name,slug',
            'quizzes' => function ($query) {
                $query->orderBy('order_index');
            }
        ])
        ->withCount('enrollments')
        ->withAvg('reviews', 'rating')
        ->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $course = $this->model->findOrFail($id);
        $course->update($data);
        return $course;
    }

    public function delete($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function searchCourses($query, $limit = 10)
    {
        return $this->model
            ->with(['teacher:id,first_name,last_name', 'tags:id,name,slug'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhereHas('tags', function ($tagQuery) use ($query) {
                      $tagQuery->where('name', 'like', "%{$query}%");
                  });
            })
            ->limit($limit)
            ->get();
    }

    public function getModulesWithLessons($courseId)
    {
        return Module::where('course_id', $courseId)
            ->with(['lessons' => function ($query) {
                $query->orderBy('order_index');
            }])
            ->orderBy('order_index')
            ->get();
    }

    /**
     * Get popular courses based on enrollment count.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPopularCourses($limit = 10)
    {
        return $this->model
            ->where('status', 'PUBLISHED')
            ->with([
                'teacher:id,first_name,last_name',
                'tags:id,name,slug'
            ])
            ->withCount('enrollments')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('enrollments_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get content-based recommendations for a user.
     *
     * @param \App\Models\User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getContentBasedRecommendations(\App\Models\User $user, $limit = 10)
    {
        // Get IDs of courses the user is enrolled in
        $enrolledCourseIds = $user->enrollments()->pluck('course_id')->toArray();

        // If no enrollments, return popular courses
        if (empty($enrolledCourseIds)) {
            return $this->getPopularCourses($limit);
        }

        // Get tags from the user's enrolled courses
        $tags = \App\Models\Tag::whereHas('courses', function ($query) use ($enrolledCourseIds) {
            $query->whereIn('courses.id', $enrolledCourseIds);
        })->pluck('id')->toArray();

        // If no tags found, return popular courses
        if (empty($tags)) {
            return $this->getPopularCourses($limit);
        }

        // Find courses with similar tags, excluding already enrolled courses
        $recommendedCourses = $this->model
            ->where('status', 'PUBLISHED')
            ->with([
                'teacher:id,first_name,last_name',
                'tags:id,name,slug'
            ])
            ->whereHas('tags', function ($query) use ($tags) {
                $query->whereIn('tags.id', $tags);
            })
            ->whereNotIn('id', $enrolledCourseIds)
            ->withCount('enrollments')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('enrollments_count')
            ->limit($limit)
            ->get();

        // If not enough recommendations, fill with popular courses
        if ($recommendedCourses->count() < $limit) {
            $remainingLimit = $limit - $recommendedCourses->count();
            $popularCourses = $this->model
                ->where('status', 'PUBLISHED')
                ->with([
                    'teacher:id,first_name,last_name',
                    'tags:id,name,slug'
                ])
                ->whereNotIn('id', $enrolledCourseIds)
                ->whereNotIn('id', $recommendedCourses->pluck('id'))
                ->withCount('enrollments')
                ->withAvg('reviews', 'rating')
                ->orderByDesc('enrollments_count')
                ->limit($remainingLimit)
                ->get();
            
            return $recommendedCourses->concat($popularCourses);
        }

        return $recommendedCourses;
    }
}