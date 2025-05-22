<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Artesaos\SEOTools\Facades\SEOMeta;

class CanonicalHeaders
{
    public function handle(Request $request, Closure $next)
    {
        // Get the current URL without query parameters
        $canonical = url($request->path());
        
        // Remove trailing slashes
        $canonical = rtrim($canonical, '/');
        
        // Force HTTPS in production
        if (app()->environment('production')) {
            $canonical = str_replace('http://', 'https://', $canonical);
        }
        
        // Set canonical URL
        SEOMeta::setCanonical($canonical);
        
        // Add hreflang tags if you have multiple language versions
        // SEOMeta::addAlternateLanguage('es', 'https://es.example.com'.$request->path());
        
        return $next($request);
    }
}
