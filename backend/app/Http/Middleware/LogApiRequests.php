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
                'endpoint' => '/' . $request->path(),
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

        // Calculate duration and sizes
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);
        $responseContent = $this->getResponseContent($response);

        // Determine log level based on status code
        $status = $response->status();
        $logLevel = match(true) {
            $status >= 500 => 'error',
            $status >= 400 => 'warning',
            default => 'info'
        };

        // Prepare log context
        $logContext = [
            'method' => $request->method(),
            'endpoint' => '/' . $request->path(),
            'user' => auth()->id() ?? 'guest',
            'ip' => $request->ip(),
            'request_id' => $requestId,
            'duration_ms' => $durationMs,
            'status' => $status,
            'payload_size' => $this->calculateSize($requestData),
            'response_size' => $this->calculateSize($responseContent),
        ];

        // Add error details for non-2xx responses
        if ($status >= 400) {
            // Debug: always add response content to see what we get
            $logContext['response_content_type'] = gettype($responseContent);
            
            if (is_array($responseContent)) {
                // Extract error message and details
                if (isset($responseContent['message'])) {
                    $logContext['error_message'] = $responseContent['message'];
                }
                if (isset($responseContent['errors'])) {
                    $logContext['validation_errors'] = $responseContent['errors'];
                }
                if (isset($responseContent['error'])) {
                    $logContext['error'] = $responseContent['error'];
                }
                // Include full response for reference
                $logContext['response_body'] = $responseContent;
            } elseif (is_string($responseContent)) {
                $logContext['error_message'] = $responseContent;
                // Try to decode if it's JSON string
                $decoded = json_decode($responseContent, true);
                if ($decoded !== null) {
                    $logContext['response_body'] = $decoded;
                    if (isset($decoded['message'])) {
                        $logContext['error_message'] = $decoded['message'];
                    }
                    if (isset($decoded['errors'])) {
                        $logContext['validation_errors'] = $decoded['errors'];
                    }
                }
            }
        }

        // Log API call with appropriate level
        Log::channel('api_json')->{$logLevel}('API CALL', $logContext);

        // Add request ID to response
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }

    private function filterSensitiveData(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'current_password', 'token', 'secret', 'access_token', 'refresh_token', 'authorization', 'api_key'];
        
        return collect($data)->map(function ($value, $key) use ($sensitiveFields) {
            return in_array($key, $sensitiveFields) ? '******' : $value;
        })->toArray();
    }

    private function getResponseContent($response): string|array
    {
        if (method_exists($response, 'content')) {
            $content = $response->content();
            $decoded = json_decode($content, true);
            return $decoded !== null ? $decoded : $content;
        }
        return 'Response content not available';
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
