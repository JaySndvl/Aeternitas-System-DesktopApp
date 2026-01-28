<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\Account;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getLoginLogs(Request $request)
    {
        $user = auth()->user();
        
        // Determine what logs to show based on role
        if (in_array($user->role, ['admin', 'hr', 'manager'])) {
            // Get recent login logs for all employees
            $logs = LoginLog::with(['account.employee'])
                ->latest()
                ->limit(20)
                ->get();
        } else {
            // Only HR and Admin can see login logs
            if (!in_array($user->role, ['admin', 'hr'])) {
                return response()->json([
                    'logs' => [],
                    'unread_count' => 0,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Get only the current user's login logs
            $logs = LoginLog::where('account_id', $user->id)
                ->latest()
                ->limit(10)
                ->get();
        }

        return response()->json([
            'logs' => $logs->map(function($log) {
                $employeeName = $log->account && $log->account->employee 
                    ? $log->account->employee->first_name . ' ' . $log->account->employee->last_name
                    : ($log->account ? 'System Account' : 'Unknown Employee');
                
                return [
                    'id' => $log->id,
                    'employee_name' => $employeeName,
                    'employee_email' => $log->account->email ?? 'N/A',
                    'ip_address' => $log->ip_address,
                    'user_agent' => $this->parseUserAgent($log->user_agent),
                    'login_time' => $log->created_at->format('M d, Y g:i A'),
                    'time_ago' => $log->created_at->diffForHumans(),
                ];
            }),
            'unread_count' => $this->getUnreadCount($user),
            'user_role' => $user->role,
        ]);
    }

    private function parseUserAgent($userAgent)
    {
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        if (strpos($userAgent, 'Opera') !== false) return 'Opera';
        if (strpos($userAgent, 'Mozilla') !== false) return 'Mozilla';
        return 'Unknown Browser';
    }

    private function getUnreadCount($user)
    {
        // Get count of logs from the last 24 hours
        $recentLogsCount = LoginLog::where('created_at', '>=', now()->subDay())
            ->when(!in_array($user->role, ['admin', 'hr', 'manager']), function($query) use ($user) {
                return $query->where('account_id', $user->id);
            })
            ->count();

        return min($recentLogsCount, 99); // Cap at 99
    }

    // Add this method to your NotificationController.php
    public function index()
    {
        $user = auth()->user();
        
        // Only allow admin, HR, and manager roles
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            abort(403, 'Unauthorized access to login logs.');
        }
        
        $logs = LoginLog::with(['account.employee'])
            ->latest()
            ->paginate(20);
        
        return view('notifications.login-logs', compact('logs'));
    }
}