<?php

namespace App\Helpers;

class AwsUrlHelper
{
    /**
     * Get the AWS endpoint URL for frontend access
     * 
     * @return string
     */
    public static function getFrontendAwsEndpoint(): string
    {
        $awsEndpoint = env('AWS_ENDPOINT');
        
        // In development with LocalStack, replace docker service name with localhost
        if (app()->environment('local', 'development')) {
            return str_replace('localstack:4566', 'localhost:4566', $awsEndpoint);
        }
        
        return $awsEndpoint;
    }
    
    /**
     * Get the internal AWS endpoint URL (for backend services)
     * 
     * @return string
     */
    public static function getInternalAwsEndpoint(): string
    {
        return env('AWS_ENDPOINT');
    }
    
    /**
     * Convert an internal S3 URL to a frontend-accessible URL
     * 
     * @param string $internalUrl
     * @return string
     */
    public static function convertToFrontendUrl(string $internalUrl): string
    {
        if (app()->environment('local', 'development')) {
            return str_replace('localstack:4566', 'localhost:4566', $internalUrl);
        }
        
        return $internalUrl;
    }
    
    /**
     * Generate a pre-signed URL that's accessible from the frontend
     * 
     * @param string $path
     * @param \DateTimeInterface $expiry
     * @param array $options
     * @return string
     */
    public static function generateFrontendPresignedUrl(string $path, \DateTimeInterface $expiry, array $options = []): string
    {
        $url = \Storage::disk('s3')->temporaryUrl($path, $expiry, $options);
        return self::convertToFrontendUrl($url);
    }
}