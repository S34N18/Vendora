<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Debug: Log the user info
        if (auth()->check()) {
            $user = auth()->user();
            \Log::info('AdminMiddleware - User Role Check:', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'role_type' => gettype($user->role),
            ]);
        }

        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        // Instead of abort, redirect to regular dashboard with error message
        return redirect()->route('dashboard')
            ->with('error', 'Access denied. Administrator privileges required.');
    }
}