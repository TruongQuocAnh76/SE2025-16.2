<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\X264;

class HlsVideoService
{
    /**
     * Video quality configurations for adaptive streaming
     */
    protected $qualityLevels = [
        '360p' => [
            'width' => 640,
            'height' => 360,
            'bitrate' => '800k',
            'audio_bitrate' => '96k'
        ],
        '480p' => [
            'width' => 854,
            'height' => 480,
            'bitrate' => '1400k',
            'audio_bitrate' => '128k'
        ],
        '720p' => [
            'width' => 1280,
            'height' => 720,
            'bitrate' => '2800k',
            'audio_bitrate' => '128k'
        ],
        '1080p' => [
            'width' => 1920,
            'height' => 1080,
            'bitrate' => '5000k',
            'audio_bitrate' => '192k'
        ]
    ];

    /**
     * HLS segment length in seconds
     */
    protected $segmentLength = 6;

    /**
     * Process video into HLS adaptive streaming format
     *
     * @param string $inputVideoPath Path to the uploaded video file
     * @param string $outputBasePath Base path for HLS output (without extension)
     * @param array $qualities Array of quality levels to generate (default: all)
     * @return array Array containing playlist URLs and processing status
     */
    public function processVideoToHls($inputVideoPath, $outputBasePath, $qualities = null)
    {
        try {
            Log::info("Starting HLS processing for video: {$inputVideoPath}");
            
            // Use all qualities if none specified
            if ($qualities === null) {
                $qualities = array_keys($this->qualityLevels);
            }

            // Create temporary directory for processing
            $tempDir = storage_path('app/temp/hls/' . uniqid());
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $generatedPlaylists = [];
            $masterPlaylistContent = "#EXTM3U\n#EXT-X-VERSION:3\n";

            // Process each quality level
            foreach ($qualities as $quality) {
                if (!isset($this->qualityLevels[$quality])) {
                    Log::warning("Unknown quality level: {$quality}");
                    continue;
                }

                $config = $this->qualityLevels[$quality];
                $playlistPath = $this->processQualityLevel(
                    $inputVideoPath,
                    $tempDir,
                    $quality,
                    $config,
                    $outputBasePath
                );

                if ($playlistPath) {
                    $generatedPlaylists[$quality] = $playlistPath;
                    
                    // Add to master playlist
                    $bandwidth = intval(str_replace('k', '000', $config['bitrate']));
                    $masterPlaylistContent .= "#EXT-X-STREAM-INF:BANDWIDTH={$bandwidth},RESOLUTION={$config['width']}x{$config['height']}\n";
                    $masterPlaylistContent .= $quality . "/playlist.m3u8\n";
                }
            }

            // Create and upload master playlist
            $masterPlaylistPath = $this->createMasterPlaylist($masterPlaylistContent, $outputBasePath);

            // Clean up temporary directory
            $this->cleanupTempDirectory($tempDir);

            Log::info("HLS processing completed successfully");

            return [
                'success' => true,
                'master_playlist' => $masterPlaylistPath,
                'quality_playlists' => $generatedPlaylists,
                'qualities_generated' => array_keys($generatedPlaylists)
            ];

        } catch (\Exception $e) {
            Log::error("HLS processing failed: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'master_playlist' => null,
                'quality_playlists' => [],
                'qualities_generated' => []
            ];
        }
    }

    /**
     * Process a single quality level
     *
     * @param string $inputPath
     * @param string $tempDir
     * @param string $quality
     * @param array $config
     * @param string $outputBasePath
     * @return string|null
     */
    protected function processQualityLevel($inputPath, $tempDir, $quality, $config, $outputBasePath)
    {
        try {
            Log::info("Processing quality level: {$quality}");

            $qualityTempDir = $tempDir . '/' . $quality;
            if (!file_exists($qualityTempDir)) {
                mkdir($qualityTempDir, 0755, true);
            }

            // Create format with specific settings
            $format = new X264('aac');
            $format->setKiloBitrate(intval(str_replace('k', '', $config['bitrate'])));
            $format->setAudioKiloBitrate(intval(str_replace('k', '', $config['audio_bitrate'])));

            // Get video for processing
            $media = FFMpeg::fromDisk('local')
                ->open($inputPath)
                ->addFilter(['-vf', "scale={$config['width']}:{$config['height']}"])
                ->addFilter(['-c:v', 'libx264'])
                ->addFilter(['-preset', 'medium'])
                ->addFilter(['-crf', '23'])
                ->addFilter(['-c:a', 'aac'])
                ->addFilter(['-ar', '44100'])
                ->addFilter(['-f', 'hls'])
                ->addFilter(['-hls_time', $this->segmentLength])
                ->addFilter(['-hls_list_size', '0'])
                ->addFilter(['-hls_segment_filename', $qualityTempDir . '/segment_%03d.ts']);

            // Generate playlist in temp directory
            $playlistFile = $qualityTempDir . '/playlist.m3u8';
            $media->export()->toDisk('local')->save($playlistFile);

            // Upload all segments and playlist to S3
            $s3QualityPath = $outputBasePath . '/' . $quality;
            $this->uploadHlsFiles($qualityTempDir, $s3QualityPath);

            // Return S3 path for the playlist
            $awsEndpoint = env('AWS_ENDPOINT');
            $awsBucket = env('AWS_BUCKET');
            return $awsEndpoint . '/' . $awsBucket . '/' . $s3QualityPath . '/playlist.m3u8';

        } catch (\Exception $e) {
            Log::error("Failed to process quality {$quality}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Upload HLS files to S3
     *
     * @param string $localDir
     * @param string $s3BasePath
     */
    protected function uploadHlsFiles($localDir, $s3BasePath)
    {
        $files = glob($localDir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                $s3Path = $s3BasePath . '/' . $filename;
                
                $content = file_get_contents($file);
                
                // Set appropriate content type
                $contentType = $this->getContentType($filename);
                
                Storage::disk('s3')->put($s3Path, $content, [
                    'ContentType' => $contentType,
                    'CacheControl' => 'max-age=31536000'
                ]);
                
                Log::info("Uploaded HLS file: {$s3Path}");
            }
        }
    }

    /**
     * Create and upload master playlist
     *
     * @param string $content
     * @param string $outputBasePath
     * @return string
     */
    protected function createMasterPlaylist($content, $outputBasePath)
    {
        $masterPlaylistPath = $outputBasePath . '/master.m3u8';
        
        Storage::disk('s3')->put($masterPlaylistPath, $content, [
            'ContentType' => 'application/vnd.apple.mpegurl',
            'CacheControl' => 'max-age=3600'
        ]);
        
        Log::info("Created master playlist: {$masterPlaylistPath}");
        
        $awsEndpoint = env('AWS_ENDPOINT');
        $awsBucket = env('AWS_BUCKET');
        return $awsEndpoint . '/' . $awsBucket . '/' . $masterPlaylistPath;
    }

    /**
     * Get appropriate content type for file
     *
     * @param string $filename
     * @return string
     */
    protected function getContentType($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        switch (strtolower($extension)) {
            case 'm3u8':
                return 'application/vnd.apple.mpegurl';
            case 'ts':
                return 'video/mp2t';
            default:
                return 'application/octet-stream';
        }
    }

    /**
     * Clean up temporary directory
     *
     * @param string $tempDir
     */
    protected function cleanupTempDirectory($tempDir)
    {
        if (file_exists($tempDir)) {
            $this->recursiveDelete($tempDir);
        }
    }

    /**
     * Recursively delete directory
     *
     * @param string $dir
     */
    protected function recursiveDelete($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->recursiveDelete($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Get video information
     *
     * @param string $videoPath
     * @return array
     */
    public function getVideoInfo($videoPath)
    {
        try {
            $media = FFMpeg::fromDisk('local')->open($videoPath);
            $durationInSeconds = $media->getDurationInSeconds();
            
            return [
                'duration' => $durationInSeconds,
                'formatted_duration' => gmdate('H:i:s', $durationInSeconds)
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get video info: " . $e->getMessage());
            return [
                'duration' => 0,
                'formatted_duration' => '00:00:00'
            ];
        }
    }

    /**
     * Generate thumbnail from video
     *
     * @param string $videoPath
     * @param string $thumbnailPath
     * @param int $timeInSeconds
     * @return bool
     */
    public function generateThumbnail($videoPath, $thumbnailPath, $timeInSeconds = 10)
    {
        try {
            $media = FFMpeg::fromDisk('local')->open($videoPath);
            
            $frame = $media->getFrameFromSeconds($timeInSeconds);
            $frame->export()->toDisk('s3')->save($thumbnailPath);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to generate thumbnail: " . $e->getMessage());
            return false;
        }
    }
}
