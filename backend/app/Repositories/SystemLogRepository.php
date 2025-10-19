<?php namespace App\Repositories;
use App\Models\SystemLog;
use Carbon\Carbon;

class SystemLogRepository {
    protected $model;
    public function __construct(SystemLog $model) { $this->model = $model; }
    public function getAll($level = null, $perPage = 20) {
        $query = $this->model;
        if ($level) $query->where('level', $level);
        return $query->orderByDesc('created_at')->paginate($perPage);
    }
    public function getById($id) {
        return $this->model->findOrFail($id);
    }
    public function create(array $data) {
        return $this->model->create($data);
    }
    public function deleteOlderThan($days = 30) {
        return $this->model->where('created_at', '<', Carbon::now()->subDays($days))->delete();
    }
}