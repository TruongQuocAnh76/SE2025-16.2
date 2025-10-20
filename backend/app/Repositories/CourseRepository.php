<?php namespace App\Repositories;
use App\Models\Course;

class CourseRepository {
    protected $model;
    public function __construct(Course $model) { $this->model = $model; }
    
    public function getAll($filters = [], $perPage = 12) {
        $query = $this->model->with('teacher:id,first_name,last_name');
        if (isset($filters['status'])) $query->where('status', $filters['status']);
        if (isset($filters['level'])) $query->where('level', $filters['level']);
        if (isset($filters['keyword'])) $query->where('title', 'like', "%{$filters['keyword']}%");
        return $query->orderByDesc('created_at')->paginate($perPage);
    }
    
    public function getById($id) {
        return $this->model->with('teacher:id,first_name,last_name,email', 'modules.lessons:id,title,order_index,module_id,is_free', 'reviews.student:id,first_name,last_name')->findOrFail($id);
    }
    
    public function create(array $data) {
        return $this->model->create($data);
    }
    
    public function update($id, array $data) {
        $course = $this->model->findOrFail($id);
        $course->update($data);
        return $course;
    }
    
    public function delete($id) {
        return $this->model->findOrFail($id)->delete();
    }
}