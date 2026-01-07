<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireTimeInMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If user is not authenticated, let other middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Allow admin and hr roles to access all modules regardless of time-in status
        if (in_array($user->role, ['admin', 'hr'])) {
            return $next($request);
        }

        // Try to get employee relationship, or find by employee_id if relationship fails
        $employee = $user->employee;
        
        // If relationship is null but employee_id exists, try to find the employee directly
        if (!$employee && $user->employee_id) {
            $employee = \App\Models\Employee::find($user->employee_id);
        }

        // If no employee record, let other middleware handle it
        if (!$employee) {
            return $next($request);
        }

        // Allow access to dashboard, logout, time-in/out routes, and company switching routes regardless of time-in status
        if ($request->routeIs(['dashboard', 'logout', 'attendance.time-in', 'attendance.time-out', 'companies.switch', 'companies.index'])) {
            return $next($request);
        }

        // Check if employee has timed in today AND hasn't timed out yet
        $todayAttendance = $employee->getTodayAttendance();

        if (!$todayAttendance || !$todayAttendance->time_in || $todayAttendance->time_out) {
            return redirect()->route('dashboard')
                ->with('error', 'You must be currently timed in to access other modules.');
        }

        return $next($request);
    }
}