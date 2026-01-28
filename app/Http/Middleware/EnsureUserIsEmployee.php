<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsEmployee
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'employee') {
            return $next($request);
        }
        
        return redirect()->route('login')->with('error', 'You must be an employee to access this page.');
    }
}