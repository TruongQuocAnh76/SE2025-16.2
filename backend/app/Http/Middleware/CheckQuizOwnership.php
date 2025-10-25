<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckQuizOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $quizId = $request->route('quizId') ?? $request->route('id');

        if (!$quizId) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz ID is required'
            ], 400);
        }

        // TODO: Add actual ownership check when auth is implemented
        // For now, just pass through
        return $next($request);
    }
}
