<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'USER') {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized (User Only)'], 403);
    }
}
