<?php

namespace App\Helpers;

use App\Contracts\StorageServiceInterface;

class StorageUrlHelper
{
    protected StorageServiceInterface $storage;

    public function __construct(StorageServiceInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Get the storage endpoint URL (internal, for backend operations)
     * 
     * @return string
     */
    public static function getStorageEndpoint(): string
    {
        return env('STORAGE_ENDPOINT', env('AWS_ENDPOINT'));
    }

    /**
     * Get the frontend storage endpoint URL (for browser-accessible URLs)
     * 
     * @return string
     */
    public static function getFrontendStorageEndpoint(): string
    {
        return env('FRONTEND_STORAGE_ENDPOINT', self::getStorageEndpoint());
    }
    
    /**
     * Generate a pre-signed URL that's accessible from the frontend
     * 
     * @param string $path
     * @param \DateTimeInterface $expiry
     * @param array $options
     * @return string
     */
    public function generateFrontendPresignedUrl(string $path, \DateTimeInterface $expiry, array $options = []): string
    {
        return $this->storage->temporaryUrl($path, $expiry, $options);
    }

    /**
     * Generate a public URL for a file
     * 
     * @param string $path
     * @return string
     */
    public function generatePublicUrl(string $path): string
    {
        return $this->storage->url($path);
    }

    /**
     * Get bucket name from configuration
     * 
     * @return string
     */
    public static function getBucket(): string
    {
        return env('STORAGE_BUCKET', env('AWS_BUCKET'));
    }

    /**
     * Build a full URL for a path using frontend endpoint and bucket
     * Uses FRONTEND_STORAGE_ENDPOINT for browser-accessible URLs
     * 
     * @param string $path The relative path within the bucket
     * @return string The full URL
     */
    public static function buildFullUrl(string $path): string
    {
        $endpoint = rtrim(self::getFrontendStorageEndpoint(), '/');
        $bucket = self::getBucket();
        
        return $endpoint . '/' . $bucket . '/' . ltrim($path, '/');
    }
}
