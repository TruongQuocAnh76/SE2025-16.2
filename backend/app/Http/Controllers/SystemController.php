<?php namespace App\Http\Controllers;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SystemController extends Controller {
    protected $logService;
    
    public function __construct(SystemLogService $logService) {
        $this->logService = $logService;
    }
    
    public function logAction(Request $request) {
        $validated = $request->validate([
            'level' => 'required|in:INFO,WARNING,ERROR,CRITICAL',
            'message' => 'required|string',
            'context' => 'nullable|array',
        ]);
        
        $log = $this->logService->logAction(
            $validated['level'],
            $validated['message'],
            Auth::id(),
            $validated['context'] ?? null,
            $request->ip(),
            $request->header('User-Agent')
        );
        
        return response()->json(['message' => 'Log created successfully', 'data' => $log], 201);
    }
    
    public function getLogs(Request $request) {
        $level = $request->query('level');
        return response()->json($this->logService->getAllLogs($level));
    }
    
    public function showLog($id) {
        return response()->json($this->logService->getLogById($id));
    }
    
    public function clearOldLogs(Request $request) {
        $days = $request->input('days', 30);
        $deleted = $this->logService->clearOldLogs($days);
        return response()->json(['message' => "Deleted $deleted old logs"]);
    }
    
    public function cacheStatus() {
        return response()->json(['status' => Cache::getDefaultDriver()]);
    }
    
    public function clearCache() {
        Cache::flush();
        return response()->json(['message' => 'Cache cleared successfully']);
    }
}