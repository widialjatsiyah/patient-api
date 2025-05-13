<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $validKey = env('ACCESS_KEY', 'secret123');
        $headerKey = $request->header('accessKey');

        if ($headerKey !== $validKey) {
            return response()->json(['message' => 'Unauthorized: Invalid access key'], 401);
        }

        return $next($request);
    }
}
