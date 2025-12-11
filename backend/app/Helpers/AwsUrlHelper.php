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
        return env('FRONTEND_AWS_ENDPOINT');
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
            $internalHost = parse_url(env('AWS_ENDPOINT'), PHP_URL_HOST) ?? '';
            $internalPort = parse_url(env('AWS_ENDPOINT'), PHP_URL_PORT) ?? '';
            $internalEndpoint = $internalHost . ($internalPort !== '' ? ':' . $internalPort : '');
            $frontendHost = parse_url(env('FRONTEND_AWS_ENDPOINT'), PHP_URL_HOST) ?? '';
            $frontendPort = parse_url(env('FRONTEND_AWS_ENDPOINT'), PHP_URL_PORT) ?? '';
            $frontendEndpoint = $frontendHost . ($frontendPort !== '' ? ':' . $frontendPort : '');
            
            return str_replace($internalEndpoint, $frontendEndpoint, $internalUrl);
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