<?php

namespace App\Jobs;

use App\Services\HlsVideoService;
use App\Services\CourseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessVideoToHlsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tempVideoPath;
    protected $hlsBasePath;
    protected $lessonId;
    protected $originalVideoPath;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600; // 1 hour

    /**
     * Create a new job instance.
     */
    public function __construct($tempVideoPath, $hlsBasePath, $lessonId, $originalVideoPath = null)
    {
        $this->tempVideoPath = $tempVideoPath;
        $this->hlsBasePath = $hlsBasePath;
        $this->lessonId = $lessonId;
        $this->originalVideoPath = $originalVideoPath;
    }

    /**
     * Execute the job.
     */
    public function handle(HlsVideoService $hlsVideoService, CourseService $courseService)
    {
        try {
            Log::info("Starting HLS processing job for lesson: {$this->lessonId}");

            // Check if temporary video file exists
            if (!Storage::disk('local')->exists($this->tempVideoPath)) {
                // If temp file doesn't exist, try to download from S3 original path
                if ($this->originalVideoPath && Storage::disk('s3')->exists($this->originalVideoPath)) {
                    Log::info("Temporary file not found, downloading from S3: {$this->originalVideoPath}");
                    
                    $videoContent = Storage::disk('s3')->get($this->originalVideoPath);
                    Storage::disk('local')->put($this->tempVideoPath, $videoContent);
                } else {
                    throw new \Exception('Video file not found for processing');
                }
            }

            // Get the full local path
            $localVideoPath = Storage::disk('local')->path($this->tempVideoPath);
            
            Log::info("Processing video file: {$localVideoPath}");

            // Process video to HLS
            $result = $hlsVideoService->processVideoToHls($localVideoPath, $this->hlsBasePath);

            if ($result['success']) {
                // Update lesson with HLS master playlist URL
                $courseService->updateLessonVideo($this->lessonId, $result['master_playlist']);
                
                Log::info("HLS processing completed successfully for lesson: {$this->lessonId}");
                Log::info("Generated qualities: " . implode(', ', $result['qualities_generated']));
                Log::info("Master playlist URL: {$result['master_playlist']}");

                // Optionally generate thumbnail
                $this->generateThumbnail($hlsVideoService, $localVideoPath);

            } else {
                Log::error("HLS processing failed for lesson: {$this->lessonId}");
                Log::error("Error: {$result['error']}");
                throw new \Exception('HLS processing failed: ' . $result['error']);
            }

        } catch (\Exception $e) {
            Log::error("HLS processing job failed for lesson: {$this->lessonId}");
            Log::error("Error: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Re-throw the exception to mark job as failed
            throw $e;
        } finally {
            // Clean up temporary file
            if (Storage::disk('local')->exists($this->tempVideoPath)) {
                Storage::disk('local')->delete($this->tempVideoPath);
                Log::info("Cleaned up temporary file: {$this->tempVideoPath}");
            }
        }
    }

    /**
     * Generate thumbnail for the video
     */
    protected function generateThumbnail($hlsVideoService, $localVideoPath)
    {
        try {
            // Generate thumbnail path based on lesson
            $thumbnailPath = str_replace('/hls/', '/thumbnails/', $this->hlsBasePath) . '/thumbnail.jpg';
            
            $success = $hlsVideoService->generateThumbnail($localVideoPath, $thumbnailPath, 10);
            
            if ($success) {
                Log::info("Thumbnail generated successfully: {$thumbnailPath}");
            } else {
                Log::warning("Failed to generate thumbnail for lesson: {$this->lessonId}");
            }
        } catch (\Exception $e) {
            Log::warning("Thumbnail generation failed: " . $e->getMessage());
            // Don't throw exception as thumbnail is optional
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error("HLS processing job permanently failed for lesson: {$this->lessonId}");
        Log::error("Final error: " . $exception->getMessage());
        
        // Clean up temporary file
        if (Storage::disk('local')->exists($this->tempVideoPath)) {
            Storage::disk('local')->delete($this->tempVideoPath);
        }
        
        // Optionally, you could update the lesson status to indicate processing failed
        // or send a notification to administrators
    }
}
