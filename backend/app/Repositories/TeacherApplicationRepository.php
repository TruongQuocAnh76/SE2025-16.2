<?php

namespace App\Repositories;

use App\Models\TeacherApplication;

class TeacherApplicationRepository
{
    protected $model;

    public function __construct(TeacherApplication $model)
    {
        $this->model = $model;
    }

    /**
     * Get all teacher applications
     */
    public function getAll()
    {
        return $this->model->with(['user', 'reviewer'])->get();
    }

    /**
     * Get application by ID
     */
    public function getById($id)
    {
        return $this->model->with(['user', 'reviewer'])->findOrFail($id);
    }

    /**
     * Create a new teacher application
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update an application
     */
    public function update($id, array $data)
    {
        $application = $this->model->findOrFail($id);
        $application->update($data);
        return $application;
    }

    /**
     * Get applications by user ID
     */
    public function getByUserId($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->with('reviewer')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get applications by status
     */
    public function getByStatus($status)
    {
        return $this->model
            ->where('status', $status)
            ->with(['user', 'reviewer'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Check if user has a pending application
     */
    public function hasPendingApplication($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('status', 'PENDING')
            ->exists();
    }

    /**
     * Get user's latest application
     */
    public function getLatestByUserId($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->with('reviewer')
            ->orderByDesc('created_at')
            ->first();
    }
}
