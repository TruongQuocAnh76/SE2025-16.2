<?php

namespace App\Services\Storage;

use App\Contracts\StorageServiceInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Aws\S3\S3Client;

class MinioStorageService implements StorageServiceInterface
{
    /**
     * The storage disk to use (default: 's3' which will be configured for Minio)
     */
    protected string $disk;

    /**
     * Create a new MinioStorageService instance
     *
     * @param string $disk The storage disk to use (default: 's3')
     */
    public function __construct(string $disk = 's3')
    {
        $this->disk = $disk;
    }

    /**
     * Store a file in the storage system
     */
    public function put(string $path, $contents, array $options = []): bool
    {
        try {
            return Storage::disk($this->disk)->put($path, $contents, $options);
        } catch (\Exception $e) {
            Log::error("Failed to put file to storage: {$path}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Get the contents of a file
     */
    public function get(string $path): string
    {
        try {
            return Storage::disk($this->disk)->get($path);
        } catch (\Exception $e) {
            Log::error("Failed to get file from storage: {$path}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Check if a file exists
     */
    public function exists(string $path): bool
    {
        try {
            return Storage::disk($this->disk)->exists($path);
        } catch (\Exception $e) {
            Log::error("Failed to check file existence: {$path}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            return false;
        }
    }

    /**
     * Delete a file from storage
     */
    public function delete(string $path): bool
    {
        try {
            return Storage::disk($this->disk)->delete($path);
        } catch (\Exception $e) {
            Log::error("Failed to delete file from storage: {$path}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Delete multiple files from storage
     */
    public function deleteMultiple(array $paths): bool
    {
        try {
            return Storage::disk($this->disk)->delete($paths);
        } catch (\Exception $e) {
            Log::error("Failed to delete multiple files from storage", [
                'error' => $e->getMessage(),
                'paths' => $paths,
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Generate a temporary/pre-signed URL for a file (GET request)
     */
    public function temporaryUrl(string $path, \DateTimeInterface $expiry, array $options = []): string
    {
        try {
            $url = Storage::disk($this->disk)->temporaryUrl($path, $expiry, $options);
            return $url;
        } catch (\Exception $e) {
            Log::error("Failed to generate temporary URL: {$path}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Generate a temporary/pre-signed URL for uploading a file (PUT request)
     * Uses FRONTEND_STORAGE_ENDPOINT for browser-accessible URLs
     */
    public function temporaryUploadUrl(string $path, \DateTimeInterface $expiry, array $options = []): string
    {
        try {
            // Use frontend endpoint for presigned URLs (accessible from browser)
            $frontendEndpoint = env('FRONTEND_STORAGE_ENDPOINT', config('filesystems.disks.' . $this->disk . '.endpoint'));
            
            $client = new S3Client([
                'version' => 'latest',
                'region' => config('filesystems.disks.' . $this->disk . '.region', 'us-east-1'),
                'endpoint' => $frontendEndpoint,
                'use_path_style_endpoint' => config('filesystems.disks.' . $this->disk . '.use_path_style_endpoint', true),
                'credentials' => [
                    'key' => config('filesystems.disks.' . $this->disk . '.key'),
                    'secret' => config('filesystems.disks.' . $this->disk . '.secret'),
                ],
            ]);

            $commandOptions = [
                'Bucket' => $this->getBucket(),
                'Key' => $path,
            ];

            // Add ContentType if specified
            if (isset($options['ContentType'])) {
                $commandOptions['ContentType'] = $options['ContentType'];
            }

            $cmd = $client->getCommand('PutObject', $commandOptions);
            
            // Calculate expiry duration
            $now = new \DateTime();
            $expiryDateTime = $expiry instanceof \DateTimeInterface ? $expiry : new \DateTime($expiry);
            $duration = '+' . $now->diff($expiryDateTime)->format('%i') . ' minutes';
            
            // If more than 60 minutes, use hours
            $diffSeconds = $expiryDateTime->getTimestamp() - $now->getTimestamp();
            if ($diffSeconds > 3600) {
                $duration = '+' . ceil($diffSeconds / 3600) . ' hours';
            } elseif ($diffSeconds > 60) {
                $duration = '+' . ceil($diffSeconds / 60) . ' minutes';
            } else {
                $duration = '+' . $diffSeconds . ' seconds';
            }

            $request = $client->createPresignedRequest($cmd, $duration);
            
            return (string) $request->getUri();
        } catch (\Exception $e) {
            Log::error("Failed to generate temporary upload URL: {$path}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Get the public URL for a file
     */
    public function url(string $path): string
    {
        try {
            $bucket = config('filesystems.disks.' . $this->disk . '.bucket');
            $endpoint = env('STORAGE_ENDPOINT', env('AWS_ENDPOINT'));
            
            return rtrim($endpoint, '/') . '/' . ltrim($bucket, '/') . '/' . ltrim($path, '/');
        } catch (\Exception $e) {
            Log::error("Failed to generate URL: {$path}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Get the size of a file in bytes
     */
    public function size(string $path): int
    {
        try {
            return Storage::disk($this->disk)->size($path);
        } catch (\Exception $e) {
            Log::error("Failed to get file size: {$path}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Get the MIME type of a file
     */
    public function mimeType(string $path): string
    {
        try {
            return Storage::disk($this->disk)->mimeType($path);
        } catch (\Exception $e) {
            Log::error("Failed to get MIME type: {$path}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Get the last modified time of a file
     */
    public function lastModified(string $path): int
    {
        try {
            return Storage::disk($this->disk)->lastModified($path);
        } catch (\Exception $e) {
            Log::error("Failed to get last modified time: {$path}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Copy a file to a new location
     */
    public function copy(string $from, string $to): bool
    {
        try {
            return Storage::disk($this->disk)->copy($from, $to);
        } catch (\Exception $e) {
            Log::error("Failed to copy file", [
                'from' => $from,
                'to' => $to,
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Move a file to a new location
     */
    public function move(string $from, string $to): bool
    {
        try {
            return Storage::disk($this->disk)->move($from, $to);
        } catch (\Exception $e) {
            Log::error("Failed to move file", [
                'from' => $from,
                'to' => $to,
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Get all files in a directory
     */
    public function files(string $directory = '', bool $recursive = false): array
    {
        try {
            return $recursive 
                ? Storage::disk($this->disk)->allFiles($directory)
                : Storage::disk($this->disk)->files($directory);
        } catch (\Exception $e) {
            Log::error("Failed to list files in directory: {$directory}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Get all directories within a directory
     */
    public function directories(string $directory = '', bool $recursive = false): array
    {
        try {
            return $recursive 
                ? Storage::disk($this->disk)->allDirectories($directory)
                : Storage::disk($this->disk)->directories($directory);
        } catch (\Exception $e) {
            Log::error("Failed to list directories: {$directory}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Create a directory
     */
    public function makeDirectory(string $path): bool
    {
        try {
            return Storage::disk($this->disk)->makeDirectory($path);
        } catch (\Exception $e) {
            Log::error("Failed to create directory: {$path}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }

    /**
     * Delete a directory
     */
    public function deleteDirectory(string $path): bool
    {
        try {
            return Storage::disk($this->disk)->deleteDirectory($path);
        } catch (\Exception $e) {
            Log::error("Failed to delete directory: {$path}", [
                'error' => $e->getMessage(),
                'disk' => $this->disk
            ]);
            throw $e;
        }
    }



    /**
     * Get the configured bucket name
     */
    public function getBucket(): string
    {
        return config('filesystems.disks.' . $this->disk . '.bucket');
    }

    /**
     * Get the disk name being used
     */
    public function getDisk(): string
    {
        return $this->disk;
    }
}
