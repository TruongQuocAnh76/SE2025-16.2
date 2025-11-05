<?php

namespace App\Repositories;

use App\Models\Lesson;

class LessonRepository
{
    protected $model;

    public function __construct(Lesson $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function getByModuleId($moduleId)
    {
        return $this->model->where('module_id', $moduleId)
            ->orderBy('order_index')
            ->get();
    }

    public function update($id, array $data)
    {
        $lesson = $this->model->findOrFail($id);
        $lesson->update($data);
        return $lesson;
    }
}
