<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Contracts\StorageServiceInterface;
use App\Helpers\StorageUrlHelper;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\X264;

class HlsVideoService
{
    protected StorageServiceInterface $storage;

    public function __construct(StorageServiceInterface $storage)
    {
        $this->storage = $storage;
    }

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

            if ($qualities === null) {
                $qualities = array_keys($this->qualityLevels);
            }

            // create temp root (absolute)
            $tempRoot = storage_path('app/temp/hls/' . uniqid('hls_'));
            if (!is_dir($tempRoot)) {
                mkdir($tempRoot, 0755, true);
            }

            $generatedPlaylists = [];
            $masterLines = ["#EXTM3U", "#EXT-X-VERSION:3"];

            // Normalize input path: prefer absolute path if provided, otherwise assume storage disk 'local' path
            $inputAbsolute = $this->resolveInputPath($inputVideoPath);
            if (!file_exists($inputAbsolute)) {
                throw new \Exception("Input video not found at path: {$inputAbsolute}");
            }

            foreach ($qualities as $quality) {
                if (!isset($this->qualityLevels[$quality])) {
                    Log::warning("Unknown quality level: {$quality}");
                    continue;
                }
                $config = $this->qualityLevels[$quality];

                $qualityPlaylistUrl = $this->processQualityLevel(
                    $inputAbsolute,
                    $tempRoot,
                    $quality,
                    $config,
                    $outputBasePath
                );

                if ($qualityPlaylistUrl) {
                    $generatedPlaylists[$quality] = $qualityPlaylistUrl;

                    // bandwidth integer (bps)
                    $bandwidth = intval(str_replace('k','',$config['bitrate'])) * 1000;
                    $masterLines[] = "#EXT-X-STREAM-INF:BANDWIDTH={$bandwidth},RESOLUTION={$config['width']}x{$config['height']}";
                    // use relative path in master (so browser resolves against master location)
                    $masterLines[] = "{$quality}/playlist.m3u8";
                }
            }

            // write & upload master playlist
            $masterContent = implode("\n", $masterLines) . "\n";
            $masterRemoteUrl = $this->createMasterPlaylist($masterContent, $outputBasePath);

            // cleanup
            $this->cleanupTempDirectory($tempRoot);

            Log::info("HLS processing completed successfully");

            return [
                'success' => true,
                'master_playlist' => $masterRemoteUrl,
                'quality_playlists' => $generatedPlaylists,
                'qualities_generated' => array_keys($generatedPlaylists)
            ];

        } catch (\Exception $e) {
            Log::error("HLS processing failed: " . $e->getMessage());
            Log::error($e->getTraceAsString());
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
    protected function processQualityLevel($inputAbsolutePath, $tempRoot, $quality, $config, $outputBasePath)
    {
        $qualityTempDir = $tempRoot . '/' . $quality;
        if (!is_dir($qualityTempDir)) {
            mkdir($qualityTempDir, 0755, true);
        }

        $playlistLocalPath = $qualityTempDir . '/playlist.m3u8';

        // build ffmpeg CLI (explicit and robust) - CHANGED
        $vbit = $config['bitrate'];
        $ab  = $config['audio_bitrate'];
        $w   = intval($config['width']);
        $h   = intval($config['height']);
        $segFmt = $qualityTempDir . '/segment_%03d.ts';

        // Use -preset and -crf for reasonable quality/size; tune for your needs
        $cmd = [
            'ffmpeg',
            '-y',                                    // Overwrite output files without asking
            '-i', escapeshellarg($inputAbsolutePath), // Input video file path

            // scale to target resolution
            '-vf', escapeshellarg("scale={$w}:{$h}"), // Video filter: scale video to target width/height

            // video encoding
            '-c:v', 'libx264',                       // Video codec: H.264 (libx264 encoder)
            '-b:v', escapeshellarg($vbit),           // Video bitrate (e.g., '2800k' for 2.8 Mbps)
            '-preset', 'medium',                     // Encoding preset: balance between speed and quality
            '-crf', '23',                           // Constant Rate Factor: quality level (lower = better quality)

            // **ensure segment starts with keyframes**
            '-g', '48',                             // GOP size: maximum keyframe interval (≈ 2 seconds at 24fps)
            '-keyint_min', '48',                    // Minimum keyframe interval (same as GOP size)
            '-sc_threshold', '0',                   // Scene change threshold: disable automatic scene-cut keyframes
            '-hls_flags', 'independent_segments',   // HLS flag: make each segment independently playable

            // audio encoding
            '-c:a', 'aac',                          // Audio codec: Advanced Audio Coding
            '-b:a', escapeshellarg($ab),            // Audio bitrate (e.g., '128k' for 128 kbps)
            '-ar', '44100',                         // Audio sample rate: 44.1 kHz (CD quality)
            '-ac', '2',                             // Audio channels: 2 (stereo)

            // HLS config
            '-f', 'hls',                            // Output format: HTTP Live Streaming
            '-hls_time', (string)$this->segmentLength, // Segment duration in seconds (default: 6)
            '-hls_list_size', '0',                  // Playlist size: 0 = keep all segments
            '-hls_playlist_type', 'vod',            // Playlist type: Video on Demand (not live)
            '-hls_segment_filename', escapeshellarg($segFmt), // Pattern for segment filenames

            escapeshellarg($playlistLocalPath)      // Output playlist file path
        ];


        // Flatten command to string
        $cmdStr = implode(' ', $cmd);

        Log::info("Running ffmpeg for quality {$quality}: {$cmdStr}");

        // run and capture output - NEW
        [$exitCode, $output] = $this->runCommand($cmdStr);
        Log::info("FFmpeg exit code: {$exitCode}");
        Log::info("FFmpeg output for {$quality}: " . $output);

        if ($exitCode !== 0) {
            Log::error("FFmpeg failed for quality {$quality}");
            return null;
        }

        // verify playlist created and contains segments - NEW
        if (!file_exists($playlistLocalPath) || filesize($playlistLocalPath) === 0) {
            Log::error("Playlist missing or empty: {$playlistLocalPath}");
            return null;
        }

        $playlistContent = file_get_contents($playlistLocalPath);
        if (strpos($playlistContent, '#EXTINF:') === false) {
            Log::error("Playlist does not contain #EXTINF (no segments). Content:\n" . $playlistContent);
            return null;
        }

        // upload files from qualityTempDir
        $s3QualityPath = rtrim($outputBasePath, '/') . '/' . $quality;
        $this->uploadHlsFiles($qualityTempDir, $s3QualityPath);

        // build returned remote URL (use frontend endpoint for client access)
        return StorageUrlHelper::buildFullUrl(ltrim($s3QualityPath, '/') . '/playlist.m3u8');
    }

    /**
     * Upload HLS files to S3
     *
     * @param string $localDir
     * @param string $s3BasePath
     */
    protected function uploadHlsFiles($localDir, $s3BasePath)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($localDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $localPath = $file->getPathname();
                $relative = ltrim(str_replace($localDir . '/', '', $localPath), '/');
                $s3Path = rtrim($s3BasePath, '/') . '/' . $relative;

                $content = file_get_contents($localPath);
                $contentType = $this->getContentType($localPath);

                $this->storage->put($s3Path, $content, [
                    'ContentType' => $contentType,
                    'CacheControl' => 'max-age=31536000'
                ]);

                Log::info("Uploaded HLS file: {$s3Path} (local: {$localPath})");
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
        // save master locally first (optional)
        $tempMaster = storage_path('app/temp/master_' . uniqid() . '.m3u8');
        file_put_contents($tempMaster, $content);

        // upload master (ensure path normalized)
        $masterRemotePath = rtrim($outputBasePath, '/') . '/master.m3u8';
        $this->storage->put($masterRemotePath, $content, [
            'ContentType' => 'application/vnd.apple.mpegurl',
            'CacheControl' => 'max-age=3600'
        ]);

        Log::info("Created master playlist: {$masterRemotePath}");

        // Use frontend endpoint for URLs that will be accessed by browser
        return StorageUrlHelper::buildFullUrl(ltrim($masterRemotePath, '/'));
    }

    /**
     * Get appropriate content type for file
     *
     * @param string $filename
     * @return string
     */
        protected function getContentType($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'm3u8': return 'application/vnd.apple.mpegurl';
            case 'ts':   return 'video/mp2t';
            case 'jpg': case 'jpeg': return 'image/jpeg';
            case 'png':  return 'image/png';
            default:     return 'application/octet-stream';
        }
    }

    /**
     * Clean up temporary directory
     *
     * @param string $tempDir
     */
    protected function cleanupTempDirectory($tempDir)
    {
        if (is_dir($tempDir)) {
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
        if (!is_dir($dir)) return;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        rmdir($dir);
    }

    /**
     * Resolve input path passed to service to absolute filesystem path
     *
     * - If given path exists as-is, return it
     * - Otherwise assume it's relative to storage/app and return absolute path
     */
    protected function resolveInputPath($inputPath)
    {
        if (file_exists($inputPath)) return $inputPath;

        $candidate = storage_path('app/' . ltrim($inputPath, '/'));
        return $candidate;
    }

    /**
     * Run shell command and return [exitCode, output]
     */
    protected function runCommand(string $cmd): array
    {
        // Redirect stderr to stdout
        $output = [];
        $exit = null;
        exec($cmd . ' 2>&1', $output, $exit);
        return [$exit, implode("\n", $output)];
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
