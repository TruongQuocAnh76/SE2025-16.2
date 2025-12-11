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
     * === ĐÃ CẬP NHẬT ===
     * Thêm 'with('tags')' và logic lọc 'whereHas('tags')'
     */
    public function getAll($filters = [], $perPage = 12)
    {
        // 1. Tải kèm (eager load) 'tags'
        $query = $this->model->with(['teacher:id,first_name,last_name', 'tags:id,name,slug']);

        if (isset($filters['status'])) $query->where('status', $filters['status']);
        if (isset($filters['level'])) $query->where('level', $filters['level']);
        if (isset($filters['keyword'])) $query->where('title', 'like', "%{$filters['keyword']}%");

        // 2. Thêm logic lọc theo Tag (dùng cho dropdown)
        if (!empty($filters['tag'])) {
            $query->whereHas('tags', function ($tagQuery) use ($filters) {
                $tagQuery->where('slug', $filters['tag']);
            });
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * === ĐÃ CẬP NHẬT ===
     * Thêm 'with('tags')' để trang chi tiết có thể hiển thị
     */
    public function getById($id)
    {
        return $this->model->with([
            'teacher:id,first_name,last_name,email',
            'modules.lessons:id,title,order_index,module_id,is_free',
            'reviews.student:id,first_name,last_name',
            'tags:id,name,slug' // <-- ĐÃ THÊM
        ])->findOrFail($id);
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
            
            // 2. Cập nhật logic tìm kiếm
            ->where(function ($q) use ($query) {
                // Tìm trong Title
                $q->where('title', 'like', "%{$query}%")
                // Tìm trong Description
                  ->orWhere('description', 'like', "%{$query}%")
                // Tìm trong TÊN CỦA TAGS
                  ->orWhereHas('tags', function ($tagQuery) use ($query) {
                        $tagQuery->where('name', 'like', "%{$query}%");
                  });
            })
            ->orderByRaw("CASE 
                WHEN title LIKE ? THEN 1 
                WHEN title LIKE ? THEN 2 
                ELSE 3 
            END", ["{$query}%", "%{$query}%"])
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
     * Get recommended courses based on popularity and ratings
     * Algorithm: Mix of highly rated courses and popular courses (by enrollment count)
     * @param int $limit Maximum number of courses to return
     * @return \Illuminate\Support\Collection
     */
    public function getRecommendedCourses($limit = 4)
    {
        return $this->model
            ->with(['teacher:id,first_name,last_name', 'tags:id,name,slug'])
            ->where('status', 'PUBLISHED')
            ->withCount('enrollments')
            ->orderByRaw('(average_rating * 0.6 + (enrollments_count / 10) * 0.4) DESC')
            ->orderBy('average_rating', 'DESC')
            ->orderBy('review_count', 'DESC')
            ->limit($limit)
            ->get();
    }
}