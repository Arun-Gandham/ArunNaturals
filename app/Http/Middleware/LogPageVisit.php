<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogPageVisit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't log admin or API routes
        if (!str_starts_with($request->path(), 'admin') && !str_starts_with($request->path(), 'api')) {
            \App\Models\PageVisit::create([
                'url' => $request->path(),
                'user_id' => auth()->check() ? auth()->id() : null,
                'ip' => $request->ip(),
            ]);
        }
        return $next($request);
    }
}
