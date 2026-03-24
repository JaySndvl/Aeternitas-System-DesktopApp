<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\LoginLog;
use Symfony\Component\HttpFoundation\Response;

class LogUserLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log successful login only if user is authenticated
        if (auth()->check() && $request->isMethod('post') && $request->route()->getName() === 'login.post') {
            try {
                LoginLog::create([
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent')
                ]);
            } catch (\Exception $e) {
                // Log error but don't interrupt login flow
                \Log::error('Failed to log user login: ' . $e->getMessage());
            }
        }

        return $response;
    }
}