<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only log API requests
        if (!$this->isApiRequest($request)) {
            return $next($request);
        }

        $startTime = microtime(true);

        // Generate request ID
        $requestId = $request->headers->get('X-Request-Id') ?? (string) Str::uuid();
        $request->headers->set('X-Request-Id', $requestId);

        // Prepare request data
        $requestData = $this->filterSensitiveData($request->all());

        try {
            $response = $next($request);
        } catch (Throwable $e) {
            // Calculate duration for error case
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);
            
            // Log error with full details
            Log::channel('api_json')->error('API ERROR', [
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'user' => auth()->id() ?? 'guest',
                'ip' => $request->ip(),
                'request_id' => $requestId,
                'duration_ms' => $durationMs,
                'status' => 500,
                'payload_size' => $this->calculateSize($requestData),
                'response_size' => '0B',
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        try {
            // Calculate duration and sizes
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);
            $responseContent = $this->getResponseContent($response);
            $status = $this->getResponseStatus($response);

            // Determine log level based on status code
            $logLevel = match(true) {
                $status >= 500 => 'error',
                $status >= 400 => 'warning',
                default => 'info'
            };

            // Prepare log context
            $logContext = [
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'user' => auth()->id() ?? 'guest',
                'ip' => $request->ip(),
                'request_id' => $requestId,
                'duration_ms' => $durationMs,
                'status' => $status,
                'payload_size' => $this->calculateSize($requestData),
                'response_size' => $this->calculateSize($responseContent),
            ];

            // Add error details for non-2xx responses
            if ($status >= 400 && is_array($responseContent)) {
                if (isset($responseContent['message'])) {
                    $logContext['error_message'] = $responseContent['message'];
                }
                if (isset($responseContent['errors'])) {
                    $logContext['validation_errors'] = $responseContent['errors'];
                }
                if (isset($responseContent['error'])) {
                    $logContext['error'] = $responseContent['error'];
                }
            }

            // Log API call with appropriate level
            Log::channel('api_json')->{$logLevel}('API CALL', $logContext);
        } catch (Throwable $e) {
            // If logging fails, log a minimal error to prevent crashing
            Log::channel('api_json')->error('API LOGGING ERROR', [
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'error' => $e->getMessage(),
                'request_id' => $requestId,
            ]);
        }

        // Add request ID to response
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }

    private function isApiRequest(Request $request): bool
    {
        return str_starts_with($request->path(), 'api/');
    }

    private function filterSensitiveData(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'current_password', 'token', 'secret', 'access_token', 'refresh_token', 'authorization', 'api_key'];
        
        return collect($data)->map(function ($value, $key) use ($sensitiveFields) {
            return in_array($key, $sensitiveFields) ? '******' : $value;
        })->toArray();
    }

    private function getResponseStatus($response): int
    {
        if (method_exists($response, 'getStatusCode')) {
            return $response->getStatusCode();
        }
        if (method_exists($response, 'status')) {
            return $response->status();
        }
        if (is_object($response) && property_exists($response, 'status')) {
            return $response->status;
        }
        return 200; // Default fallback
    }

    private function getResponseContent($response): string|array
    {
        try {
            // Handle different response types safely
            if (!is_object($response)) {
                return 'Non-object response: ' . gettype($response);
            }

            // Try to get content from JSON responses
            if (method_exists($response, 'getData')) {
                $data = $response->getData();
                if (is_array($data) || is_object($data)) {
                    return (array) $data;
                }
            }

            if (method_exists($response, 'content')) {
                $content = $response->content();
                if (is_string($content)) {
                    $decoded = json_decode($content, true);
                    return $decoded !== null ? $decoded : $content;
                }
                return $content;
            }
            
            if (method_exists($response, 'getContent')) {
                $content = $response->getContent();
                if (is_string($content)) {
                    $decoded = json_decode($content, true);
                    return $decoded !== null ? $decoded : $content;
                }
                return $content;
            }

            // Handle PSR-7 responses
            if (method_exists($response, 'getBody')) {
                $body = $response->getBody();
                if (is_string($body)) {
                    $decoded = json_decode($body, true);
                    return $decoded !== null ? $decoded : $body;
                }
                return $body;
            }

            // For responses that don't have content methods
            return 'Response content not accessible';
        } catch (Throwable $e) {
            return 'Error getting response content: ' . $e->getMessage();
        }
    }

    private function calculateSize($data): string
    {
        $size = strlen(json_encode($data));
        if ($size < 1024) {
            return $size . 'B';
        } elseif ($size < 1024 * 1024) {
            return round($size / 1024, 1) . 'KB';
        } else {
            return round($size / (1024 * 1024), 1) . 'MB';
        }
    }
}
