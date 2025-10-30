<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class ApiJsonFormatter extends JsonFormatter
{
    public function format(LogRecord $record): string
    {
        $context = $record->context;
        
        // Base context with required fields
        $baseContext = [
            'user' => $context['user'] ?? 'guest',
            'ip' => $context['ip'] ?? 'unknown',
            'method' => $context['method'] ?? 'GET',
            'endpoint' => $context['endpoint'] ?? '/',
            'duration_ms' => $context['duration_ms'] ?? 0,
            'status' => $context['status'] ?? 0,
            'payload_size' => $context['payload_size'] ?? '0B',
            'response_size' => $context['response_size'] ?? '0B',
        ];
        
        // Merge with any additional context fields (like error_message, validation_errors, etc.)
        $additionalContext = array_diff_key($context, $baseContext);
        $fullContext = array_merge($baseContext, $additionalContext);
        
        $data = [
            'message' => 'API CALL',
            'context' => $fullContext,
            'level_name' => $record->level->getName(),
            'channel' => $record->channel,
            'datetime' => $record->datetime->format('Y-m-d\TH:i:s.uP'),
        ];

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    }

    public function formatBatch(array $records): string
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }
        return $message;
    }
}
