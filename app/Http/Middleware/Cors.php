<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get the origin from the request
        $origin = $request->headers->get('Origin');
        
        // Get allowed origins from config (can be comma-separated or single value)
        $allowedOrigins = $this->getAllowedOrigins();
        
        // Determine which origin to allow
        $allowedOrigin = $this->getAllowedOrigin($origin, $allowedOrigins);
        
        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $allowedOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        $response = $next($request);

        // Only set CORS headers if we have an allowed origin
        if ($allowedOrigin) {
            $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }

    /**
     * Get allowed origins from config
     */
    private function getAllowedOrigins(): array
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        
        // Support comma-separated origins
        $origins = explode(',', $frontendUrl);
        
        // Trim whitespace and filter empty values
        return array_filter(array_map('trim', $origins));
    }

    /**
     * Determine which origin to allow based on request origin
     */
    private function getAllowedOrigin(?string $requestOrigin, array $allowedOrigins): ?string
    {
        // If no origin in request, use first allowed origin (for same-origin requests)
        if (!$requestOrigin) {
            return !empty($allowedOrigins) ? reset($allowedOrigins) : null;
        }

        // Check if request origin is in allowed list
        foreach ($allowedOrigins as $allowed) {
            // Exact match
            if ($requestOrigin === $allowed) {
                return $requestOrigin;
            }
            
            // Support wildcard subdomains (e.g., *.example.com)
            if (strpos($allowed, '*') !== false) {
                $pattern = '/^' . str_replace(['*', '.'], ['.*', '\.'], $allowed) . '$/';
                if (preg_match($pattern, $requestOrigin)) {
                    return $requestOrigin;
                }
            }
        }

        // If origin doesn't match, return first allowed origin (fallback)
        // In production, you might want to return null to reject unauthorized origins
        return !empty($allowedOrigins) ? reset($allowedOrigins) : null;
    }
}

