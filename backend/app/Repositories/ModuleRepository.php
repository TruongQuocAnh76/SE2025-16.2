<?php

namespace App\Repositories;

use App\Models\Module;

class ModuleRepository
{
    protected $model;

    public function __construct(Module $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function getByCourseId($courseId)
    {
        return $this->model->where('course_id', $courseId)
            ->with(['lessons' => function ($query) {
                $query->orderBy('order_index');
            }])
            ->orderBy('order_index')
            ->get();
    }

    public function firstOrCreate(array $attributes, array $values = [])
    {
        return $this->model->firstOrCreate($attributes, $values);
    }
}
