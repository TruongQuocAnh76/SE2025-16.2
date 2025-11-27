<?php

namespace App\Repositories;

use App\Models\Question;

class QuestionRepository
{
    protected $model;

    public function __construct(Question $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function getByQuizId($quizId)
    {
        return $this->model->where('quiz_id', $quizId)
            ->orderBy('order_index')
            ->get();
    }
}
