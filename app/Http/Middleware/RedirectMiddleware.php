<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RedirectMiddleware
{
    /**
     * Permanent redirects
     */
    protected $redirects = [
        // Examples:
        // 'old-blog' => 'blog',
        // 'about-us' => 'about',
    ];

    /**
     * Handle legacy URLs and patterns
     */
    protected $patterns = [
        // Examples:
        // 'blog/(\d{4})/(\d{2})/(.*)' => 'blog/$3',
        // 'properties/cat/(.*)' => 'properties?category=$1',
    ];

    public function handle(Request $request, Closure $next)
    {
        $path = $request->path();

        // Check for exact matches first
        if (isset($this->redirects[$path])) {
            return redirect($this->redirects[$path], 301);
        }

        // Check pattern matches
        foreach ($this->patterns as $pattern => $replacement) {
            if (preg_match('#^'.$pattern.'$#', $path, $matches)) {
                $newPath = preg_replace('#^'.$pattern.'$#', $replacement, $path);
                return redirect($newPath, 301);
            }
        }

        // Ensure lowercase URLs
        if ($path !== strtolower($path)) {
            return redirect(strtolower($path), 301);
        }

        // Remove trailing slashes (except root URL)
        if ($path !== '/' && substr($path, -1) === '/') {
            return redirect(rtrim($path, '/'), 301);
        }

        // Force HTTPS in production
        if (app()->environment('production') && !$request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        // Handle WWW redirects in production
        if (app()->environment('production')) {
            $host = $request->header('host');
            if (str_starts_with($host, 'www.')) {
                $newHost = substr($host, 4);
                return redirect()->to($request->getScheme() . '://' . $newHost . $request->getRequestUri(), 301);
            }
        }

        return $next($request);
    }
}
