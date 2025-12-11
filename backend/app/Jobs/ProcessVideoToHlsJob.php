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

            $localVideoPath = null;

            // New flow: Video is already in S3, download it for processing
            if ($this->originalVideoPath && Storage::disk('s3')->exists($this->originalVideoPath)) {
                Log::info("Downloading video from S3 for processing: {$this->originalVideoPath}");
                
                // Create a temporary file path for processing
                $tempFileName = 'temp/videos/' . uniqid() . '_lesson_' . $this->lessonId . '.mp4';
                
                $videoContent = Storage::disk('s3')->get($this->originalVideoPath);
                Storage::disk('local')->put($tempFileName, $videoContent);
                
                $localVideoPath = Storage::disk('local')->path($tempFileName);
                $this->tempVideoPath = $tempFileName; // Update for cleanup
                
            } else if ($this->tempVideoPath && Storage::disk('local')->exists($this->tempVideoPath)) {
                // Legacy flow: Video was uploaded directly and temporarily stored locally
                $localVideoPath = Storage::disk('local')->path($this->tempVideoPath);
                Log::info("Using temporary file for processing: {$this->tempVideoPath}");
                
            } else {
                throw new \Exception('No video file found for processing. Neither S3 original nor temp file exists.');
            }

            Log::info("Processing video file: {$localVideoPath}");

            // Process video to HLS
            $result = $hlsVideoService->processVideoToHls($localVideoPath, $this->hlsBasePath);

            if ($result['success']) {
                // Update lesson with HLS master playlist URL
                $courseService->updateLessonVideo($this->lessonId, $result['master_playlist']);
                
                Log::info("HLS processing completed successfully for lesson: {$this->lessonId}");
                Log::info("Generated qualities: " . implode(', ', $result['qualities_generated']));
                Log::info("Master playlist URL: {$result['master_playlist']}");

                // Generate thumbnail
                $this->generateThumbnail($hlsVideoService, $localVideoPath);

                // Delete the original video from S3 after successful processing
                if ($this->originalVideoPath && Storage::disk('s3')->exists($this->originalVideoPath)) {
                    Storage::disk('s3')->delete($this->originalVideoPath);
                    Log::info("Deleted original video file from S3: {$this->originalVideoPath}");
                }

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
            if ($this->tempVideoPath && Storage::disk('local')->exists($this->tempVideoPath)) {
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
        if ($this->tempVideoPath && Storage::disk('local')->exists($this->tempVideoPath)) {
            Storage::disk('local')->delete($this->tempVideoPath);
            Log::info("Cleaned up temporary file after failure: {$this->tempVideoPath}");
        }
        
        // Note: We keep the original video file in S3 on failure for potential retry
        // or manual processing later
        
        // Optionally, you could update the lesson status to indicate processing failed
        // or send a notification to administrators
    }
}
