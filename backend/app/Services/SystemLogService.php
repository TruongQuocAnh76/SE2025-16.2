<?php namespace App\Services;
use App\Repositories\SystemLogRepository;
use Illuminate\Support\Str;

class SystemLogService {
    protected $logRepository;
    public function __construct(SystemLogRepository $logRepository) { $this->logRepository = $logRepository; }
    public function getAllLogs($level = null, $perPage = 20) {
        return $this->logRepository->getAll($level, $perPage);
    }
    public function getLogById($id) {
        return $this->logRepository->getById($id);
    }
    public function logAction($level, $message, $userId = null, $context = null, $ipAddress = null, $userAgent = null) {
        $data = ['id' => Str::uuid(), 'level' => $level, 'message' => $message, 'user_id' => $userId, 'context' => json_encode($context), 'ip_address' => $ipAddress, 'user_agent' => $userAgent];
        return $this->logRepository->create($data);
    }
    public function clearOldLogs($days = 30) {
        return $this->logRepository->deleteOlderThan($days);
    }
}