<?php

namespace App\Repositories;

use App\Models\Lesson;
use App\Models\LessonComment;

class LessonRepository
{
    protected $model;
    protected $commentModel;

    public function __construct(Lesson $model, LessonComment $commentModel)
    {
        $this->model = $model;
        $this->commentModel = $commentModel;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function getById($id)
    {
        return $this->model->find($id);
    }

    public function getByIdOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    public function getByIdWithModule($id)
    {
        return $this->model->with(['module.course'])->find($id);
    }

    public function update($id, array $data)
    {
        $lesson = $this->model->findOrFail($id);
        $lesson->update($data);
        return $lesson;
    }

    public function delete($id)
    {
        $lesson = $this->model->findOrFail($id);
        return $lesson->delete();
    }

    public function getByModuleId($moduleId)
    {
        return $this->model->where('module_id', $moduleId)
            ->orderBy('order_index')
            ->get();
    }

    public function getMaxOrderIndex($moduleId)
    {
        return $this->model->where('module_id', $moduleId)->max('order_index') ?? 0;
    }

    // Comment methods
    public function getCommentsByLessonId($lessonId)
    {
        return $this->commentModel
            ->with(['user:id,first_name,last_name,avatar', 'replies.user:id,first_name,last_name,avatar'])
            ->where('lesson_id', $lessonId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createComment(array $data)
    {
        $comment = $this->commentModel->create($data);
        $comment->load('user:id,first_name,last_name,avatar');
        return $comment;
    }
}
