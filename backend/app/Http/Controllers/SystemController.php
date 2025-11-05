<?php namespace App\Http\Controllers;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 */
class SystemController extends Controller {
    protected $logService;
    
    public function __construct(SystemLogService $logService) {
        $this->logService = $logService;
    }
    
    /**
     * @OA\Post(
     *     path="/system/log",
     *     summary="Log system action",
     *     description="Create a system log entry",
     *     operationId="logAction",
     *     tags={"System"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"level", "message"},
     *             @OA\Property(property="level", type="string", enum={"INFO", "WARNING", "ERROR", "CRITICAL"}, example="INFO"),
     *             @OA\Property(property="message", type="string", example="User logged in"),
     *             @OA\Property(property="context", type="object", nullable=true, example={"user_id": "123", "action": "login"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Log created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/SystemLog")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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
    
    /**
     * @OA\Get(
     *     path="/system/logs",
     *     summary="Get system logs",
     *     description="Retrieve system logs (admin only)",
     *     operationId="getLogs",
     *     tags={"System"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         required=false,
     *         description="Filter by log level",
     *         @OA\Schema(type="string", enum={"INFO", "WARNING", "ERROR", "CRITICAL"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of system logs",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/SystemLog")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - admin access required"
     *     )
     * )
     */
    public function getLogs(Request $request) {
        $level = $request->query('level');
        return response()->json($this->logService->getAllLogs($level));
    }
    
    /**
     * @OA\Get(
     *     path="/system/logs/{id}",
     *     summary="Get specific log entry",
     *     description="Retrieve a specific system log entry (admin only)",
     *     operationId="getLog",
     *     tags={"System"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Log ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Log entry details",
     *         @OA\JsonContent(ref="#/components/schemas/SystemLog")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - admin access required"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Log not found"
     *     )
     * )
     */
    public function showLog($id) {
        return response()->json($this->logService->getLogById($id));
    }
    
    /**
     * @OA\Delete(
     *     path="/system/logs/clear",
     *     summary="Clear old logs",
     *     description="Delete system logs older than specified days (admin only)",
     *     operationId="clearOldLogs",
     *     tags={"System"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="days",
     *         in="query",
     *         required=false,
     *         description="Number of days to keep logs (default: 30)",
     *         @OA\Schema(type="integer", default=30, minimum=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logs cleared successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Deleted 150 old logs")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - admin access required"
     *     )
     * )
     */
    public function clearOldLogs(Request $request) {
        $days = $request->input('days', 30);
        $deleted = $this->logService->clearOldLogs($days);
        return response()->json(['message' => "Deleted $deleted old logs"]);
    }
    
    /**
     * @OA\Get(
     *     path="/system/cache",
     *     summary="Get cache status",
     *     description="Check the current cache driver and status",
     *     operationId="getCacheStatus",
     *     tags={"System"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cache status information",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="redis")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function cacheStatus() {
        return response()->json(['status' => Cache::getDefaultDriver()]);
    }
    
    /**
     * @OA\Delete(
     *     path="/system/cache",
     *     summary="Clear cache",
     *     description="Clear all cached data (admin only)",
     *     operationId="clearCache",
     *     tags={"System"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cache cleared successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - admin access required"
     *     )
     * )
     */
    /**
     * @OA\Get(
     *     path="/system/jobs",
     *     summary="Get queued jobs",
     *     description="Get list of queued jobs (admin only)",
     *     operationId="getJobs",
     *     tags={"System"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of queued jobs",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - admin access required"
     *     )
     * )
     */
    public function getJobs() {
        // This would typically integrate with Laravel's queue system
        return response()->json(['message' => 'Jobs listing not implemented yet']);
    }
    
    /**
     * @OA\Delete(
     *     path="/system/jobs/{id}",
     *     summary="Delete queued job",
     *     description="Delete a specific queued job (admin only)",
     *     operationId="deleteJob",
     *     tags={"System"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Job ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - admin access required"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Job not found"
     *     )
     * )
     */
    public function deleteJob($id) {
        // This would typically integrate with Laravel's queue system
        return response()->json(['message' => 'Job deletion not implemented yet']);
    }
    
    /**
     * @OA\Post(
     *     path="/system/jobs/{id}/retry",
     *     summary="Retry failed job",
     *     description="Retry a failed queued job (admin only)",
     *     operationId="retryJob",
     *     tags={"System"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Job ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job retry initiated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - admin access required"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Job not found"
     *     )
     * )
     */
    public function retryJob($id) {
        // This would typically integrate with Laravel's queue system
        return response()->json(['message' => 'Job retry not implemented yet']);
    }
}