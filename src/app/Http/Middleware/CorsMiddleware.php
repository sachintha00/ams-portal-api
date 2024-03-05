<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Set CORS headers
        $response->header('Access-Control-Allow-Origin', $request->header('Origin'));
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        
        // Ensure 'Access-Control-Allow-Credentials' header is set to 'true'
        $response->header('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}