<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Artesaos\SEOTools\Facades\SEOMeta;

class MetaRobotsMiddleware
{
    /**
     * Routes that should be noindex
     */
    protected $noindexRoutes = [
        'login',
        'register',
        'password/*',
        'email/*',
        'admin/*',
        'dashboard',
    ];

    /**
     * Routes that should be nofollow
     */
    protected $nofollowRoutes = [
        'login',
        'register',
        'password/*',
    ];

    public function handle(Request $request, Closure $next)
    {
        $currentRoute = $request->path();
        $robotsDirectives = ['index', 'follow'];

        // Check if current route should be noindex
        foreach ($this->noindexRoutes as $route) {
            if ($this->routeMatches($currentRoute, $route)) {
                $robotsDirectives[0] = 'noindex';
                break;
            }
        }

        // Check if current route should be nofollow
        foreach ($this->nofollowRoutes as $route) {
            if ($this->routeMatches($currentRoute, $route)) {
                $robotsDirectives[1] = 'nofollow';
                break;
            }
        }

        // Add pagination handling
        if ($request->has('page') && $request->input('page') > 1) {
            $robotsDirectives[0] = 'noindex';
        }

        // Special handling for property search pages with filters
        if ($request->is('properties') && $request->hasAny(['sort', 'filter', 'price', 'location'])) {
            $robotsDirectives[0] = 'noindex';
        }

        // Set meta robots tag
        SEOMeta::setRobots(implode(',', $robotsDirectives));

        return $next($request);
    }

    /**
     * Check if a route matches a pattern
     */
    protected function routeMatches(string $route, string $pattern): bool
    {
        $pattern = str_replace('*', '.*', $pattern);
        return (bool) preg_match('#^'.$pattern.'$#', $route);
    }
}
