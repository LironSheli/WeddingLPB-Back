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
        
        // If no origin header (server-to-server request from proxy), skip CORS
        // The proxy handles same-origin requests, so CORS doesn't apply
        if (!$origin) {
            return $next($request);
        }
        
        // Get allowed origins from config
        $allowedOrigins = $this->getAllowedOrigins();
        
        // Determine which origin to allow
        // When using credentials, we MUST return the exact origin from the request if it's allowed
        $allowedOrigin = $this->getAllowedOrigin($origin, $allowedOrigins);
        
        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
            
            if ($allowedOrigin) {
                $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
                $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
                $response->headers->set('Access-Control-Max-Age', '86400');
            }
            
            return $response;
        }

        $response = $next($request);

        // Set CORS headers only if origin was present (direct browser requests)
        if ($allowedOrigin && $origin) {
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
        $origins = array_filter(array_map('trim', $origins));
        
        // In development, also allow common localhost variations
        if (config('app.env') === 'local' || config('app.debug')) {
            $origins[] = 'http://localhost:3000';
            $origins[] = 'http://127.0.0.1:3000';
            $origins[] = 'http://localhost';
            $origins[] = 'http://127.0.0.1';
        }
        
        return array_unique($origins);
    }

    /**
     * Determine which origin to allow based on request origin
     * When using credentials, we MUST return the exact origin from the request
     */
    private function getAllowedOrigin(?string $requestOrigin, array $allowedOrigins): ?string
    {
        // If no origin in request (same-origin or server-to-server), allow it
        // This handles requests from Next.js proxy (server-to-server) or same-origin requests
        if (!$requestOrigin) {
            // For server-to-server requests (like from Next.js proxy), allow all
            // Or return first allowed origin as fallback
            return !empty($allowedOrigins) ? reset($allowedOrigins) : '*';
        }

        // Check if request origin is in allowed list (exact match required for credentials)
        foreach ($allowedOrigins as $allowed) {
            if ($requestOrigin === $allowed) {
                // Return the exact origin from the request (required for credentials)
                return $requestOrigin;
            }
        }

        // In development, be more permissive
        if (config('app.env') === 'local' || config('app.debug')) {
            // Allow localhost variations
            if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $requestOrigin)) {
                return $requestOrigin;
            }
        }

        // If origin doesn't match and we're in production, return null to reject
        // Otherwise, return first allowed origin as fallback
        if (config('app.env') === 'production') {
            return null;
        }

        return !empty($allowedOrigins) ? reset($allowedOrigins) : null;
    }
}

